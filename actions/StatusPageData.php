<?php
namespace Modules\StatusPage\Actions;

use CController;
use CControllerResponseData;
use API;

/**
 * AJAX endpoint for fetching status data
 */
class StatusPageData extends CController {
    
    protected function checkInput(): bool {
        $fields = [
            'filter_groups' => 'string',
            'filter_alerts_only' => 'in 0,1',
            'limit' => 'int32',
            'offset' => 'int32'
        ];
        
        $ret = $this->validateInput($fields);
        
        if (!$ret) {
            $this->setResponse(new CControllerResponseFatal());
        }
        
        return $ret;
    }
    
	protected function checkPermissions(): bool {
		// Allow any authenticated user
		return true;
	}
    
    protected function doAction(): void {
        $filter_groups = $this->getInput('filter_groups', '');
        $filter_alerts_only = $this->getInput('filter_alerts_only', 0);
        $limit = $this->getInput('limit', 500);
        $offset = $this->getInput('offset', 0);
        
        // Step 1: Fetch hostgroups starting with "CUSTOMER/"
        $hostgroups_params = [
            'output' => ['groupid', 'name'],
            'search' => [
                'name' => 'CUSTOMER/'
            ],
            'searchByAny' => true,
            'startSearch' => true,
            'sortfield' => 'name',
            'sortorder' => 'ASC',
            'limit' => $limit
        ];
        
        // Add text filter if provided
        if (!empty($filter_groups)) {
            $hostgroups_params['search']['name'] = 'CUSTOMER/' . $filter_groups;
        }
        
        $hostgroups = API::HostGroup()->get($hostgroups_params);
        
        if (empty($hostgroups)) {
            $this->setResponse(new CControllerResponseData([
                'data' => [],
                'total' => 0
            ]));
            return;
        }
        
        $groupids = array_column($hostgroups, 'groupid');
        
        // Step 2: Fetch active problems for these hostgroups
        $problems = API::Problem()->get([
            'output' => ['eventid', 'objectid', 'name', 'severity'],
            'groupids' => $groupids,
            'selectHosts' => ['hostid', 'name'],
            'recent' => false,
            'suppressed' => false,
            'sortfield' => 'severity',
            'sortorder' => 'DESC'
        ]);
        
        // Step 3: Aggregate problems by hostgroup
        $hostgroup_problems = [];
        
        foreach ($problems as $problem) {
            // Get hostgroups for this problem's hosts
            foreach ($problem['hosts'] as $host) {
                $host_groups = API::HostGroup()->get([
                    'output' => ['groupid', 'name'],
                    'hostids' => $host['hostid']
                ]);
                
                foreach ($host_groups as $group) {
                    if (in_array($group['groupid'], $groupids)) {
                        if (!isset($hostgroup_problems[$group['groupid']])) {
                            $hostgroup_problems[$group['groupid']] = [
                                'problems' => [],
                                'severity_counts' => [
                                    TRIGGER_SEVERITY_NOT_CLASSIFIED => 0,
                                    TRIGGER_SEVERITY_INFORMATION => 0,
                                    TRIGGER_SEVERITY_WARNING => 0,
                                    TRIGGER_SEVERITY_AVERAGE => 0,
                                    TRIGGER_SEVERITY_HIGH => 0,
                                    TRIGGER_SEVERITY_DISASTER => 0
                                ]
                            ];
                        }
                        
                        $hostgroup_problems[$group['groupid']]['problems'][] = [
                            'name' => $problem['name'],
                            'severity' => $problem['severity'],
                            'host' => $host['name']
                        ];
                        
                        $hostgroup_problems[$group['groupid']]['severity_counts'][$problem['severity']]++;
                    }
                }
            }
        }
        
        // Step 4: Build final data structure
        $status_data = [];
        
        foreach ($hostgroups as $group) {
            $groupid = $group['groupid'];
            $display_name = str_replace('CUSTOMER/', '', $group['name']);
            
            $has_problems = isset($hostgroup_problems[$groupid]);
            $problem_count = $has_problems 
                ? count($hostgroup_problems[$groupid]['problems']) 
                : 0;
            
            // Apply alerts filter
            if ($filter_alerts_only && !$has_problems) {
                continue;
            }
            
            $status_data[] = [
                'groupid' => $groupid,
                'name' => $display_name,
                'full_name' => $group['name'],
                'has_problems' => $has_problems,
                'problem_count' => $problem_count,
                'severity_counts' => $has_problems 
                    ? $hostgroup_problems[$groupid]['severity_counts'] 
                    : null,
                'problems' => $has_problems 
                    ? array_slice($hostgroup_problems[$groupid]['problems'], 0, 10) // Limit to 10 for tooltip
                    : []
            ];
        }
        
        // Return JSON response
        $response = new CControllerResponseData([
            'data' => $status_data,
            'total' => count($hostgroups)
        ]);
        
        $this->setResponse($response);
    }
}
