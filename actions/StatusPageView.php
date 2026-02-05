<?php
namespace Modules\StatusPage\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseFatal;
use CRoleHelper;
use CMessageHelper;
use API;

class StatusPageView extends CController {

    protected function init(): void {
        // Disable CSRF validation for this page
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        // Check if user has any preferences to save
        $fields = [
            'icon_size' => 'in tiny,small,medium,large',
            'spacing' => 'in normal,compact,ultra-compact',
            'refresh' => 'in 0,1',
            'page' => 'ge 1'
        ];

        $ret = $this->validateInput($fields);

        if (!$ret) {
            $this->setResponse(new CControllerResponseFatal());
        }

        return $ret;
    }

    protected function checkPermissions(): bool {
        // Allow all logged-in users for now
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }

    protected function doAction(): void {
        // Get user preferences
        $icon_size = $this->getInput('icon_size', 'small');
        $spacing = $this->getInput('spacing', 'normal');
        $refresh = $this->hasInput('refresh') ? (bool)$this->getInput('refresh') : false;

        try {
            // Fetch data from Zabbix API
            $host_groups = API::HostGroup()->get([
                'output' => ['groupid', 'name'],
                'preservekeys' => true
            ]);

            $hosts = API::Host()->get([
                'output' => ['hostid', 'host', 'name', 'status'],
                'selectHostGroups' => ['groupid'], // Correct parameter name
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
                'warning_alerts' => 0,
                'info_alerts' => 0
            ];

            // Process host status
            $host_status = [];
            $host_triggers = [];

            // Group triggers by host first for better performance
            foreach ($triggers as $trigger) {
                foreach ($trigger['hosts'] as $trigger_host) {
                    $hostid = $trigger_host['hostid'];
                    if (!isset($host_triggers[$hostid])) {
                        $host_triggers[$hostid] = [];
                    }
                    $host_triggers[$hostid][] = $trigger;
                }
            }

            foreach ($hosts as $hostid => $host) {
                $is_healthy = true;
                $has_critical = false;
                $has_warning = false;
                $has_info = false;
                
                // Check triggers for this host
                if (isset($host_triggers[$hostid])) {
                    foreach ($host_triggers[$hostid] as $trigger) {
                        if ($trigger['value'] == TRIGGER_VALUE_TRUE) {
                            $is_healthy = false;
                            
                            switch ($trigger['priority']) {
                                case TRIGGER_SEVERITY_DISASTER:
                                case TRIGGER_SEVERITY_HIGH:
                                    $has_critical = true;
                                    $statistics['critical_alerts']++;
                                    break;
                                case TRIGGER_SEVERITY_AVERAGE:
                                case TRIGGER_SEVERITY_WARNING:
                                    $has_warning = true;
                                    $statistics['warning_alerts']++;
                                    break;
                                default:
                                    $has_info = true;
                                    $statistics['info_alerts']++;
                                    break;
                            }
                        }
                    }
                }

                $host_status[$hostid] = [
                    'host' => $host['host'],
                    'name' => $host['name'],
                    'healthy' => $is_healthy,
                    'has_critical' => $has_critical,
                    'has_warning' => $has_warning,
                    'has_info' => $has_info,
                    'hostgroups' => $host['hostgroups'] ?? [] // Use hostgroups instead of groups
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
                // Check if hostgroups key exists
                if (isset($host['hostgroups']) && is_array($host['hostgroups'])) {
                    foreach ($host['hostgroups'] as $group) {
                        $groupid = $group['groupid'];
                        if (!isset($grouped_hosts[$groupid])) {
                            $grouped_hosts[$groupid] = [
                                'id' => $groupid,
                                'name' => $host_groups[$groupid]['name'] ?? 'Unknown Group',
                                'hosts' => []
                            ];
                        }
                        $grouped_hosts[$groupid]['hosts'][$hostid] = $host_status[$hostid];
                    }
                }
            }

            // Sort groups by name
            usort($grouped_hosts, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            // Calculate overall health percentage
            if ($statistics['total_hosts'] > 0) {
                $statistics['health_percentage'] = round(($statistics['healthy_hosts'] / $statistics['total_hosts']) * 100, 2);
            } else {
                $statistics['health_percentage'] = 0;
            }

            // Prepare response
            $data = [
                'statistics' => $statistics,
                'groups' => $grouped_hosts,
                'total_groups_count' => count($grouped_hosts),
                'icon_size' => $icon_size,
                'spacing' => $spacing,
                'refresh' => $refresh,
                'error' => null
            ];

            // Add success message if refreshed
            if ($refresh) {
                CMessageHelper::setSuccessTitle(_('Status page refreshed successfully'));
            }

        } catch (\Exception $e) {
            // Handle API errors gracefully
            $data = [
                'statistics' => [
                    'total_groups' => 0,
                    'total_hosts' => 0,
                    'healthy_hosts' => 0,
                    'hosts_with_alerts' => 0,
                    'critical_alerts' => 0,
                    'warning_alerts' => 0,
                    'info_alerts' => 0,
                    'health_percentage' => 0
                ],
                'groups' => [],
                'total_groups_count' => 0,
                'icon_size' => $icon_size,
                'spacing' => $spacing,
                'refresh' => $refresh,
                'error' => $e->getMessage()
            ];
            
            CMessageHelper::setErrorTitle(_('Failed to fetch data from Zabbix API'));
        }

        $response = new CControllerResponseData($data);
        $response->setTitle(_('Status Page'));
        $this->setResponse($response);
    }
}
