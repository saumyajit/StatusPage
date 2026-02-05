<?php declare(strict_types = 1);

namespace Modules\StatusPageCompact;

use Zabbix\Core\CWidget;

class Widget extends CWidget {

    public const DEFAULT_REFRESH = 30;
    public const ICON_SIZE_TINY = 20;
    public const ICON_SIZE_SMALL = 30;
    public const ICON_SIZE_MEDIUM = 40;
    public const ICON_SIZE_LARGE = 50;

    public function getDefaultName(): string {
        return _('Status Page');
    }

    public function getTranslationStrings(): array {
        return [
            'class.widget.js' => [
                'No data' => _('No data'),
                'Active alerts' => _('Active alerts'),
                'No active alerts' => _('No active alerts'),
                'Host Group' => _('Host Group')
            ]
        ];
    }
}
