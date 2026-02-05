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
        
        // Get problems for each group
        foreach ($groups as $group) {
            $customer_name = preg_replace('/^CUSTOMER\//', '', $group['name']);
            
            // Get active problems for this host group
            $problems = API::Problem()->get([
                'output' => ['eventid', 'name', 'severity', 'clock'],
                'groupids' => $group['groupid'],
                'recent' => false,
                'sortfield' => ['severity', 'clock'],
                'sortorder' => 'DESC',
                'limit' => 20
            ]);
            
            $max_severity = 0;
            if (!empty($problems)) {
                $max_severity = max(array_column($problems, 'severity'));
            }
            
            $status_data[] = [
                'groupid' => $group['groupid'],
                'name' => $customer_name,
                'full_name' => $group['name'],
                'alert_count' => count($problems),
                'has_alerts' => !empty($problems),
                'max_severity' => $max_severity,
                'alerts' => array_map(function($problem) {
                    return [
                        'name' => $problem['name'],
                        'severity' => $problem['severity'],
                        'time' => date('Y-m-d H:i:s', $problem['clock'])
                    ];
                }, $problems)
            ];
        }

        $this->setResponse(new CControllerResponseData([
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
