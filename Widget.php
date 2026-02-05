<?php declare(strict_types = 1);

namespace Modules\StatusPageCompact;

use Zabbix\Core\CWidget;

class Widget extends CWidget {

    public const DEFAULT_NAME = 'Status Page';
    
    public function getDefaultName(): string {
        return self::DEFAULT_NAME;
    }
}
