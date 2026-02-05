<?php
namespace Modules\StatusPage\Actions;

use CController;
use CRoleHelper;

class StatusPageView extends CController {

    protected function init(): void {
        $this->disableSIDvalidation();
    }

    protected function checkPermissions(): bool {
        return $this->checkAccess(CRoleHelper::UI_REPORTS_SYSTEM_INFO);
    }

    protected function doAction(): void {
        // Fetch data from Zabbix API
        $host_groups = API::HostGroup()->get([
            'output' => ['groupid', 'name'],
            'preservekeys' => true
        ]);

        $hosts = API::Host()->get([
            'output' => ['hostid', 'host', 'status'],
            'selectGroups' => ['groupid'],
            'monitored_hosts' => true,
            'preservekeys' => true
        ]);

        $triggers = API::Trigger()->get([
            'output' => ['triggerid', 'description', 'priority', 'value'],
            'selectHosts' => ['hostid'],
            'filter' => ['value' => TRIGGER_VALUE_TRUE],
            'preservekeys' => true
        ]);

        // Prepare statistics
        $statistics = [
            'total_groups' => count($host_groups),
            'total_hosts' => count($hosts),
            'healthy_hosts' => 0,
            'hosts_with_alerts' => 0,
            'critical_alerts' => 0,
            'warning_alerts' => 0
        ];

        // Process host status
        $host_status = [];
        foreach ($hosts as $hostid => $host) {
            $is_healthy = true;
            $has_critical = false;
            $has_warning = false;

            // Check triggers for this host
            foreach ($triggers as $trigger) {
                foreach ($trigger['hosts'] as $trigger_host) {
                    if ($trigger_host['hostid'] == $hostid) {
                        if ($trigger['value'] == TRIGGER_VALUE_TRUE) {
                            $is_healthy = false;
                            if ($trigger['priority'] >= TRIGGER_SEVERITY_HIGH) {
                                $has_critical = true;
                                $statistics['critical_alerts']++;
                            } else {
                                $has_warning = true;
                                $statistics['warning_alerts']++;
                            }
                        }
                    }
                }
            }

            $host_status[$hostid] = [
                'healthy' => $is_healthy,
                'has_critical' => $has_critical,
                'has_warning' => $has_warning
            ];

            if ($is_healthy) {
                $statistics['healthy_hosts']++;
            } else {
                $statistics['hosts_with_alerts']++;
            }
        }

        // Group hosts by host group
        $grouped_hosts = [];
        foreach ($hosts as $hostid => $host) {
            foreach ($host['groups'] as $group) {
                $groupid = $group['groupid'];
                if (!isset($grouped_hosts[$groupid])) {
                    $grouped_hosts[$groupid] = [
                        'name' => $host_groups[$groupid]['name'],
                        'hosts' => []
                    ];
                }
                $grouped_hosts[$groupid]['hosts'][$hostid] = $host_status[$hostid];
            }
        }

        // Prepare response
        $data = [
            'statistics' => $statistics,
            'groups' => $grouped_hosts,
            'total_groups_count' => count($grouped_hosts),
            'icon_size' => 'small', // Default icon size
            'spacing' => 'normal' // Default spacing
        ];

        $response = new CControllerResponseData($data);
        $response->setTitle(_('Status Page'));
        $this->setResponse($response);
    }
}
