<?php declare(strict_types = 1);

namespace Modules\StatusPage;

use Zabbix\Core\CModule;
use APP;
use CMenuItem;

class Module extends CModule {
    
    public function init(): void {
        // Add menu item
        APP::Component()->get('menu.main')
            ->findOrAdd(_('Monitoring'))
            ->getSubmenu()
            ->insertAfter(_('Hosts'),
                (new CMenuItem(_('Status Page')))->setAction('status.page')
            );
    }
}
