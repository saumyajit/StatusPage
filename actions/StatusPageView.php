<?php declare(strict_types = 1);

namespace Modules\StatusPage\Actions;

use CController as CAction;
use CControllerResponseData;

class StatusPageView extends CAction {

    protected function init(): void {
        // Nothing needed here for basic setup
    }

    protected function checkPermissions(): bool {
        // Use getUserType() instead of checkAccess()
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }

    protected function checkPermissions(): bool {
        // Allow any logged-in user
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }

    protected function doAction(): void {
        // Simple test data
        $data = [
            'message' => 'Status Page Module is working!',
            'test' => 'Hello from StatusPage'
        ];

        $response = new CControllerResponseData($data);
        $response->setTitle(_('Status Page'));
        $this->setResponse($response);
    }
}
