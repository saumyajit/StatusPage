<?php declare(strict_types = 1);

namespace Modules\StatusPage\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseFatal;
use CRoleHelper;
use CWidget;
use CForm;
use CFormGrid;
use CLabel;
use CFormField;
use CSelect;
use CTextBox;
use CCheckBox;
use CSimpleButton;
use CDiv;
use CSpan;
use CTag;
use CView;
use ZBX_STYLE_BTN_ALT;
use ZBX_TEXTAREA_FILTER_STANDARD_WIDTH;

/**
 * Status Page View Controller
 */
class StatusPageView extends CController {
    
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
     * Validate input parameters
     */
    protected function checkInput(): bool {
        $fields = [
            'filter_groups' => 'string',
            'filter_alerts_only' => 'in 0,1',
            'icon_size' => 'in 20,30,40,50',
            'spacing' => 'in normal,compact',
            'limit' => 'in 100,500,1000,2000'
        ];
        
        $ret = $this->validateInput($fields);
        
        if (!$ret) {
            $this->setResponse(new CControllerResponseFatal());
        }
        
        return $ret;
    }
    
    /**
     * Main action logic
     */
    protected function doAction(): void {
        // Get filter parameters or defaults
        $filter_groups = $this->getInput('filter_groups', '');
        $filter_alerts_only = (int) $this->getInput('filter_alerts_only', 0);
        $icon_size = (int) $this->getInput('icon_size', 30);
        $spacing = $this->getInput('spacing', 'normal');
        $limit = (int) $this->getInput('limit', 500);
        
        // Prepare data for view
        $data = [
            'title' => _('Status Page'),
            'filter' => [
                'groups' => $filter_groups,
                'alerts_only' => $filter_alerts_only,
                'icon_size' => $icon_size,
                'spacing' => $spacing,
                'limit' => $limit
            ],
            'icon_sizes' => [
                20 => _('Tiny (20px)'),
                30 => _('Small (30px)'),
                40 => _('Medium (40px)'),
                50 => _('Large (50px)')
            ],
            'spacing_options' => [
                'normal' => _('Normal'),
                'compact' => _('Ultra Compact')
            ],
            'limit_options' => [
                100 => _('100 groups'),
                500 => _('500 groups'),
                1000 => _('1000 groups'),
                2000 => _('2000 groups')
            ]
        ];
        
        $response = new CControllerResponseData($data);
        $response->setTitle(_('Status Page'));
        $this->setResponse($response);
    }
}
