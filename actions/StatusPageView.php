<?php declare(strict_types = 1);

namespace Modules\StatusPageCompact\Actions;

use CControllerResponseData;
use CController;

class StatusPageView extends CController {

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return true;
    }

    protected function checkPermissions(): bool {
        return $this->checkAccess(CRoleHelper::UI_MONITORING_HOSTS);
    }

    protected function doAction(): void {
        $data = [
            'title' => _('Status Page'),
            'active_tab' => CMenuHelper::encode('statuspage.view')
        ];

        $response = new CControllerResponseData($data);
        $response->setTitle(_('Status Page'));
        $this->setResponse($response);
    }
}
