<?php declare(strict_types = 1);

namespace Modules\StatusPage\Actions;

use CController;
use CControllerResponseData;

class StatusPageView extends CController {

    protected function checkPermissions(): bool {
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }

    protected function doAction(): void {

        // 1) Get HostGroups
        $groups = API::HostGroup()->get([
            'output' => ['groupid', 'name'],
            'search' => ['name' => 'CUSTOMER/'],
            'startSearch' => true
        ]);

        $groupIds = array_column($groups, 'groupid');

        // 2) Get active problems (alerts)
        $problems = API::Problem()->get([
            'output' => ['eventid', 'severity'],
            'groupids' => $groupIds,
            'recent' => true,
            'sortfield' => ['eventid'],
            'sortorder' => 'DESC'
        ]);

        // 3) Aggregate alerts by group
        $alerts = [];
        foreach ($problems as $p) {
            foreach ($p['groups'] as $g) {
                $gid = $g['groupid'];
                if (!isset($alerts[$gid])) {
                    $alerts[$gid] = [
                        'total' => 0,
                        'severity' => array_fill(0, 6, 0)
                    ];
                }
                $alerts[$gid]['total']++;
                $alerts[$gid]['severity'][$p['severity']]++;
            }
        }

        // 4) Build view data
        $data = [];
        foreach ($groups as $g) {
            $gid = $g['groupid'];
            $total = $alerts[$gid]['total'] ?? 0;
            $severity = $alerts[$gid]['severity'] ?? array_fill(0, 6, 0);

            $status = 'green';
            if ($total > 0) {
                $status = ($severity[4] > 0 || $severity[5] > 0) ? 'red' : 'yellow';
            }

            $data[] = [
                'groupid' => $gid,
                'name' => substr($g['name'], strlen('CUSTOMER/')),
                'total' => $total,
                'severity' => $severity,
                'status' => $status
            ];
        }

        $this->setResponse(new CControllerResponseData([
            'groups' => $data
        ]));
    }
}
