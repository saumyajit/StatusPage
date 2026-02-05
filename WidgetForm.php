<?php declare(strict_types = 1);

namespace Modules\StatusPageCompact;

use Zabbix\Widgets\CWidgetForm;
use Zabbix\Widgets\Fields\CWidgetFieldCheckBox;
use Zabbix\Widgets\Fields\CWidgetFieldMultiSelectGroup;
use Zabbix\Widgets\Fields\CWidgetFieldRadioButtonList;

class WidgetForm extends CWidgetForm {

    public function addFields(): self {
        return $this
            ->addField(
                new CWidgetFieldMultiSelectGroup('groupids', _('Host groups'))
            )
            ->addField(
                (new CWidgetFieldRadioButtonList('icon_size', _('Icon Size'), 30))
                    ->setValues([
                        20 => _('Tiny (20px)'),
                        30 => _('Small (30px)'),
                        40 => _('Medium (40px)'),
                        50 => _('Large (50px)')
                    ])
            )
            ->addField(
                (new CWidgetFieldRadioButtonList('view_style', _('View Style'), 0))
                    ->setValues([
                        0 => _('Honeycomb'),
                        1 => _('Circle Grid'),
                        2 => _('Square Grid')
                    ])
            )
            ->addField(
                new CWidgetFieldCheckBox('show_alert_count', _('Show alert count badge'), 1)
            )
            ->addField(
                new CWidgetFieldCheckBox('compact_mode', _('Ultra compact (no spacing)'), 0)
            );
    }
}
