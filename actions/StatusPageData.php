<?php declare(strict_types = 1);

namespace Modules\StatusPage\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseFatal;
use API;

/**
 * AJAX Data Controller
 */
class StatusPageData extends CController {
    
    /**
     * Initialize action
     */
    public function init(): void {
        $this->disableCsrfValidation();
    }
    
    /**
     * Check user permissions
     */
    protected function checkPermissions(): bool {
        // Allow access for any authenticated Zabbix user
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }
    
    /**
     * Validate input
     */
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
    
    /**
     * Main action
     */
    protected function doAction(): void {
        $filter_groups = $this->getInput('filter_groups', '');
        $filter_alerts_only = (int) $this->getInput('filter_alerts_only', 0);
        $limit = (int) $this->getInput('limit', 500);
        $offset = (int) $this->getInput('offset', 0);
        
        try {
            // Fetch hostgroups starting with "CUSTOMER/"
            $search_pattern = 'CUSTOMER/';
            if (!empty($filter_groups)) {
                $search_pattern .= $filter_groups;
            }
            
            $hostgroups = API::HostGroup()->get([
                'output' => ['groupid', 'name'],
                'search' => ['name' => $search_pattern],
                'startSearch' => true,
                'sortfield' => 'name',
                'sortorder' => 'ASC',
                'limit' => $limit
            ]);
            
            if (empty($hostgroups)) {
                $this->setResponse(new CControllerResponseData([
                    'data' => [],
                    'total' => 0
                ]));
                return;
            }
            
            $groupids = array_column($hostgroups, 'groupid');
            
            // Fetch active problems
            $problems = API::Problem()->get([
                'output' => ['eventid', 'objectid', 'name', 'severity', 'clock'],
                'groupids' => $groupids,
                'selectHosts' => ['hostid', 'name'],
                'recent' => false,
                'suppressed' => false,
                'sortfield' => ['severity', 'clock'],
                'sortorder' => 'DESC',
                'preservekeys' => true
            ]);
            
            // Build hostgroup-to-problems mapping
            $hostgroup_problems = [];
            
            if (!empty($problems)) {
                foreach ($problems as $problem) {
                    if (empty($problem['hosts'])) {
                        continue;
                    }
                    
                    foreach ($problem['hosts'] as $host) {
                        // Get host's groups
                        $host_groups = API::HostGroup()->get([
                            'output' => ['groupid'],
                            'hostids' => $host['hostid']
                        ]);
                        
                        foreach ($host_groups as $group) {
                            if (in_array($group['groupid'], $groupids)) {
                                $gid = $group['groupid'];
                                
                                if (!isset($hostgroup_problems[$gid])) {
                                    $hostgroup_problems[$gid] = [
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
                                
                                $hostgroup_problems[$gid]['problems'][] = [
                                    'name' => $problem['name'],
                                    'severity' => (int) $problem['severity'],
                                    'host' => $host['name'],
                                    'clock' => $problem['clock']
                                ];
                                
                                $hostgroup_problems[$gid]['severity_counts'][(int) $problem['severity']]++;
                            }
                        }
                    }
                }
            }
            
            // Build final response
            $status_data = [];
            
            foreach ($hostgroups as $group) {
                $groupid = $group['groupid'];
                $display_name = preg_replace('/^CUSTOMER\//', '', $group['name']);
                
                $has_problems = isset($hostgroup_problems[$groupid]);
                $problem_count = $has_problems ? count($hostgroup_problems[$groupid]['problems']) : 0;
                
                // Apply filter
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
                        ? array_slice($hostgroup_problems[$groupid]['problems'], 0, 10) 
                        : []
                ];
            }
            
            $this->setResponse(new CControllerResponseData([
                'data' => $status_data,
                'total' => count($status_data)
            ]));
            
        } catch (\Exception $e) {
            $this->setResponse(new CControllerResponseData([
                'error' => $e->getMessage(),
                'data' => [],
                'total' => 0
            ]));
        }
    }
}
