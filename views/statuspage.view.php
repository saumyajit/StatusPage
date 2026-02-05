<?php
$this->addJsFile('modules/StatusPage/assets/js/statuspage.js');

$widget = (new CWidget())
    ->setTitle(_('Status Page Widget'))
    ->setControls(
        (new CTag('nav', true,
            (new CList())
                ->addItem(
                    (new CButton('refresh', _('Refresh')))
                        ->onClick('location.reload();')
                )
        ))->setAttribute('aria-label', _('Content controls'))
    );

// Filter form
$filter_form = (new CForm('get'))
    ->setName('statuspage_filter')
    ->addVar('action', 'status.page');

$filter_form_list = (new CFormList())
    ->addRow(_('Host group name'),
        (new CTextBox('filter_name', $data['filter_name']))
            ->setWidth(ZBX_TEXTAREA_FILTER_SMALL_WIDTH)
            ->setAttribute('placeholder', _('type to filter'))
            ->setAttribute('autofocus', 'autofocus')
    )
    ->addRow(_('Show only groups with problems'),
        (new CCheckBox('filter_with_problems', 1))
            ->setChecked($data['filter_with_problems'] == 1)
    )
    ->addRow(_('Icon size'),
        (new CSelect('icon_size'))
            ->setValue($data['icon_size'])
            ->addOptions(CSelect::createOptionsFromArray([
                20 => _('Tiny (20px)'),
                30 => _('Small (30px)'),
                40 => _('Medium (40px)'),
                50 => _('Large (50px)')
            ]))
    )
    ->addRow(_('Spacing'),
        (new CSelect('spacing'))
            ->setValue($data['spacing'])
            ->addOptions(CSelect::createOptionsFromArray([
                'normal' => _('Normal'),
                'compact' => _('Ultra Compact')
            ]))
    );

$filter_form->addItem($filter_form_list);

$filter_form->addItem(
    (new CSubmitButton(_('Apply'), 'filter_apply'))->addClass('js-filter-submit')
);

$widget->addItem($filter_form);

// Summary statistics
$stats_div = (new CDiv())
    ->addClass('status-summary')
    ->addItem([
        (new CDiv([
            (new CDiv($data['total_groups']))->addClass('stat-number'),
            (new CDiv(_('HOST GROUPS')))->addClass('stat-label')
        ]))->addClass('stat-item'),
        (new CDiv([
            (new CDiv($data['total_healthy']))->addClass('stat-number healthy-color'),
            (new CDiv(_('HEALTHY')))->addClass('stat-label')
        ]))->addClass('stat-item'),
        (new CDiv([
            (new CDiv($data['total_with_alerts']))->addClass('stat-number alert-color'),
            (new CDiv(_('WITH ALERTS')))->addClass('stat-label')
        ]))->addClass('stat-item')
    ]);

$widget->addItem($stats_div);

// Status icons container
$icons_container = (new CDiv())
    ->addClass('status-icons-container')
    ->addClass('spacing-' . $data['spacing'])
    ->addClass('size-' . $data['icon_size']);

foreach ($data['groups'] as $group) {
    $icon = (new CDiv())
        ->addClass('status-icon')
        ->addClass($group['has_problems'] ? 'status-problem' : 'status-ok')
        ->setAttribute('data-groupid', $group['groupid'])
        ->setAttribute('data-groupname', $group['full_name'])
        ->setAttribute('data-problems', json_encode($group['problems']))
        ->setAttribute('data-severity', json_encode($group['severity_counts']));
    
    if ($group['problem_count'] > 0) {
        $icon->addItem(
            (new CDiv($group['problem_count']))->addClass('problem-count')
        );
    }
    
    $icons_container->addItem($icon);
}

$widget->addItem($icons_container);

// Tooltip container
$widget->addItem(
    (new CDiv())->setId('status-tooltip')->addClass('status-tooltip')
);

$widget->show();
?>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        StatusPage.init();
    });
</script>
