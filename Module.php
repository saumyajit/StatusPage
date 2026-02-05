<?php declare(strict_types = 1);

namespace Modules\StatusPageCompact;

use APP;
use CMenu;
use CMenuItem;
use Zabbix\Core\CModule;

class Module extends CModule {

    public function init(): void {
        // Initialize module
    }

    public function getMenuItems(): array {
        return [
            (new CMenuItem(_('Status Page')))
                ->setAction('statuspage.view')
                ->setIcon('iconlist')
        ];
    }
}
