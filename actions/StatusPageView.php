<?php declare(strict_types = 1);

namespace Modules\StatusPage\Actions;

use CController as CAction;
use CControllerResponseData;

class StatusPageView extends CAction {

    protected function checkInput(): bool {
        return true;
    }

    protected function checkPermissions(): bool {
        return true;
    }

    protected function doAction(): void {
        $data = [
            'message' => 'Status Page is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response = new CControllerResponseData($data);
        $response->setTitle(_('Status Page'));
        $this->setResponse($response);
    }
}
