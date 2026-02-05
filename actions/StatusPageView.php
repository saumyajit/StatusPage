<?php
namespace Modules\StatusPage\Actions;

use CController;
use CControllerResponseData;

/**
 * Main Status Page controller
 */
class StatusPageView extends CController {
    
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
     * Check user permissions
     */
    protected function checkPermissions(): bool {
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }
    
    /**
     * Main action logic
     */
    protected function doAction(): void {
        // Get filter parameters or defaults
        $filter_groups = $this->hasInput('filter_groups') 
            ? $this->getInput('filter_groups') 
            : '';
        $filter_alerts_only = $this->hasInput('filter_alerts_only') 
            ? $this->getInput('filter_alerts_only') 
            : 0;
        $icon_size = $this->hasInput('icon_size') 
            ? $this->getInput('icon_size') 
            : 30;
        $spacing = $this->hasInput('spacing') 
            ? $this->getInput('spacing') 
            : 'normal';
        $limit = $this->hasInput('limit') 
            ? $this->getInput('limit') 
            : 500;
        
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
            // Available options for filters
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
