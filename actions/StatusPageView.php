<?php

namespace Modules\StatusPage\Actions;

use CController;
use CControllerResponseData;
use API;
use CArrayHelper;

class StatusPageView extends CController {

    protected function init(): void {
        $this->disableSIDValidation();
    }

    protected function checkInput(): bool {
        $fields = [
            'filter_name' => 'string',
            'filter_with_problems' => 'in 0,1',
            'icon_size' => 'in 20,30,40,50',
            'spacing' => 'in normal,compact'
        ];

        $ret = $this->validateInput($fields);

        if (!$ret) {
            $this->setResponse(new CControllerResponseFatal());
        }

        return $ret;
    }

    protected function checkPermissions(): bool {
        return $this->checkAccess(CRoleHelper::UI_MONITORING_HOSTS);
    }

    protected function doAction(): void {
        $filter_name = $this->getInput('filter_name', '');
        $filter_with_problems = $this->getInput('filter_with_problems', 0);
        $icon_size = $this->getInput('icon_size', 30);
        $spacing = $this->getInput('spacing', 'normal');

        // Fetch all hostgroups starting with "CUSTOMER/"
        $hostgroups = API::HostGroup()->get([
            'output' => ['groupid', 'name'],
            'search' => [
                'name' => 'CUSTOMER/'
            ],
            'searchWildcardsEnabled' => true,
            'startSearch' => true,
            'sortfield' => 'name',
            'preservekeys' => true
        ]);

        if (!$hostgroups) {
            $hostgroups = [];
        }

        $groupids = array_keys($hostgroups);

        // Fetch problems for all hostgroups
        $problems_data = [];
        $total_problems = 0;
        $groups_with_problems = 0;

        if (!empty($groupids)) {
            // Get all hosts in these groups
            $hosts = API::Host()->get([
                'output' => ['hostid', 'name'],
                'groupids' => $groupids,
                'selectHostGroups' => ['groupid']
            ]);

            // Create mapping of groupid => hostids
            $group_hosts = [];
            foreach ($hosts as $host) {
                foreach ($host['hostgroups'] as $group) {
                    if (isset($hostgroups[$group['groupid']])) {
                        $group_hosts[$group['groupid']][] = $host['hostid'];
                    }
                }
            }

            // Fetch all active problems
            if (!empty($hosts)) {
                $all_problems = API::Problem()->get([
                    'output' => ['eventid', 'objectid', 'name', 'severity'],
                    'source' => EVENT_SOURCE_TRIGGERS,
                    'object' => EVENT_OBJECT_TRIGGER,
                    'suppressed' => false,
                    'selectTriggers' => ['triggerid', 'description', 'priority'],
                    'selectHosts' => ['hostid', 'name'],
                    'recent' => false
                ]);

                // Map problems to hostgroups
                foreach ($all_problems as $problem) {
                    if (!empty($problem['hosts'])) {
                        $hostid = $problem['hosts'][0]['hostid'];
                        
                        foreach ($group_hosts as $groupid => $host_list) {
                            if (in_array($hostid, $host_list)) {
                                if (!isset($problems_data[$groupid])) {
                                    $problems_data[$groupid] = [
                                        'total' => 0,
                                        'severity' => [
                                            TRIGGER_SEVERITY_NOT_CLASSIFIED => 0,
                                            TRIGGER_SEVERITY_INFORMATION => 0,
                                            TRIGGER_SEVERITY_WARNING => 0,
                                            TRIGGER_SEVERITY_AVERAGE => 0,
                                            TRIGGER_SEVERITY_HIGH => 0,
                                            TRIGGER_SEVERITY_DISASTER => 0
                                        ],
                                        'problems' => []
                                    ];
                                }
                                
                                $severity = $problem['severity'];
                                $problems_data[$groupid]['total']++;
                                $problems_data[$groupid]['severity'][$severity]++;
                                $problems_data[$groupid]['problems'][] = [
                                    'name' => $problem['name'],
                                    'severity' => $severity,
                                    'hostname' => $problem['hosts'][0]['name']
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Prepare data for view
        $groups_data = [];
        foreach ($hostgroups as $groupid => $group) {
            $has_problems = isset($problems_data[$groupid]) && $problems_data[$groupid]['total'] > 0;
            
            // Apply filter for groups with problems only
            if ($filter_with_problems && !$has_problems) {
                continue;
            }

            $problem_count = $has_problems ? $problems_data[$groupid]['total'] : 0;
            
            if ($has_problems) {
                $groups_with_problems++;
                $total_problems += $problem_count;
            }

            $groups_data[] = [
                'groupid' => $groupid,
                'name' => str_replace('CUSTOMER/', '', $group['name']),
                'full_name' => $group['name'],
                'has_problems' => $has_problems,
                'problem_count' => $problem_count,
                'severity_counts' => $has_problems ? $problems_data[$groupid]['severity'] : [],
                'problems' => $has_problems ? $problems_data[$groupid]['problems'] : []
            ];
        }

        // Sort by name
        CArrayHelper::sort($groups_data, ['name']);

        $data = [
            'groups' => $groups_data,
            'total_groups' => count($groups_data),
            'total_healthy' => count($groups_data) - $groups_with_problems,
            'total_with_alerts' => $groups_with_problems,
            'total_problems' => $total_problems,
            'filter_name' => $filter_name,
            'filter_with_problems' => $filter_with_problems,
            'icon_size' => $icon_size,
            'spacing' => $spacing
        ];

        $response = new CControllerResponseData($data);
        $response->setTitle(_('Status Page'));
        $this->setResponse($response);
    }
}
