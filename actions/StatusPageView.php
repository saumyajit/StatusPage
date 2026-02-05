<?php declare(strict_types = 1);

namespace Modules\StatusPageCompact\Actions;

use CController;
use CControllerResponseData;
use API;

class StatusPageView extends CController {

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        $fields = [
            'groupids' => 'array_id',
            'icon_size' => 'int32',
            'view_style' => 'int32',
            'show_alert_count' => 'in 0,1',
            'compact_mode' => 'in 0,1'
        ];

        $ret = $this->validateInput($fields);

        if (!$ret) {
            $this->setResponse(
                new CControllerResponseData(['main_block' => json_encode([
                    'error' => 'Invalid input parameters'
                ])])
            );
        }

        return $ret;
    }

    protected function checkPermissions(): bool {
        return true;
    }

    protected function doAction(): void {
        $groupids = $this->getInput('groupids', []);

        // Get all host groups starting with "CUSTOMER/"
        $groups = API::HostGroup()->get([
            'output' => ['groupid', 'name'],
            'searchByAny' => true,
            'search' => [
                'name' => 'CUSTOMER/'
            ],
            'startSearch' => true,
            'sortfield' => 'name'
        ]);

        // Filter groups if specific groupids provided
        if (!empty($groupids)) {
            $groups = array_filter($groups, function($group) use ($groupids) {
                return in_array($group['groupid'], $groupids);
            });
        }

        $status_data = [];
        
        // Process groups in batches for better performance
        $batch_size = 100;
        $group_chunks = array_chunk($groups, $batch_size);
        
        foreach ($group_chunks as $chunk) {
            $chunk_groupids = array_column($chunk, 'groupid');
            
            // Get all problems for this batch in one call
            $problems = API::Problem()->get([
                'output' => ['eventid', 'name', 'severity', 'clock', 'objectid'],
                'groupids' => $chunk_groupids,
                'recent' => false,
                'sortfield' => ['severity', 'clock'],
                'sortorder' => 'DESC'
            ]);
            
            // Group problems by groupid
            $problems_by_group = [];
            foreach ($problems as $problem) {
                // Get trigger info to find groupid
                $trigger = API::Trigger()->get([
                    'output' => ['triggerid'],
                    'triggerids' => $problem['objectid'],
                    'groupids' => $chunk_groupids,
                    'selectGroups' => ['groupid'],
                    'limit' => 1
                ]);
                
                if (!empty($trigger[0]['groups'])) {
                    foreach ($trigger[0]['groups'] as $group) {
                        if (!isset($problems_by_group[$group['groupid']])) {
                            $problems_by_group[$group['groupid']] = [];
                        }
                        $problems_by_group[$group['groupid']][] = $problem;
                    }
                }
            }
            
            foreach ($chunk as $group) {
                // Extract customer name (everything after "CUSTOMER/")
                $customer_name = preg_replace('/^CUSTOMER\//', '', $group['name']);
                
                $group_problems = $problems_by_group[$group['groupid']] ?? [];
                
                // Limit to top 20 alerts for tooltip
                $group_problems = array_slice($group_problems, 0, 20);
                
                $status_data[] = [
                    'groupid' => $group['groupid'],
                    'name' => $customer_name,
                    'full_name' => $group['name'],
                    'alert_count' => count($group_problems),
                    'has_alerts' => !empty($group_problems),
                    'max_severity' => !empty($group_problems) ? max(array_column($group_problems, 'severity')) : 0,
                    'alerts' => array_map(function($problem) {
                        return [
                            'name' => $problem['name'],
                            'severity' => $problem['severity'],
                            'time' => date('Y-m-d H:i:s', $problem['clock'])
                        ];
                    }, $group_problems)
                ];
            }
        }

        $this->setResponse(new CControllerResponseData([
            'name' => $this->getInput('name', $this->widget->getDefaultName()),
            'status_data' => $status_data,
            'icon_size' => $this->getInput('icon_size', 30),
            'view_style' => $this->getInput('view_style', 0),
            'show_alert_count' => $this->getInput('show_alert_count', 1),
            'compact_mode' => $this->getInput('compact_mode', 0),
            'user' => [
                'debug_mode' => $this->getDebugMode()
            ]
        ]));
    }
}
