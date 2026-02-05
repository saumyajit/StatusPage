<?php

namespace Modules\StatusPage;

use Zabbix\Core\CModule;
use APP;
use CMenuItem;

class Module extends CModule {
    
    public function init(): void {
        // Register CSS file
        APP::Component()->get('stylesheet')->addExternalStyle('modules/StatusPage/assets/css/statuspage.css');
        
        // Add menu item
        $menu = APP::Component()->get('menu.main')
            ->findOrAdd(_('Monitoring'))
            ->getSubmenu();
            
        $menu->insertAfter(_('Hosts'),
            (new CMenuItem(_('Status Page')))->setAction('status.page')
        );
    }
}
