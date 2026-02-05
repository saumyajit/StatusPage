<?php
/**
 * Status Page View
 * @var CView $this
 * @var array $data
 */

// Build absolute path to module assets
$module_url = 'modules/StatusPage/assets';

// Include CSS
$this->addCssFile($module_url . '/css/statuspage.css');

// Create page widget
$widget = (new CWidget())
    ->setTitle($data['title'])
    ->setControls(
        (new CTag('nav', true, 
            (new CButton('refresh', _('Refresh')))
                ->onClick('StatusPage.refresh();')
        ))->setAttribute('aria-label', _('Content controls'))
    );

// Filter form
$filter_form = (new CForm('get'))
    ->setId('status-page-filter')
    ->setName('status_page_filter')
    ->addVar('action', 'status.page');

$filter_table = (new CFormGrid())
    ->addItem([
        new CLabel(_('Items'), 'limit'),
        new CFormField(
            (new CSelect('limit'))
                ->setId('limit')
                ->setValue($data['filter']['limit'])
                ->addOptions(CSelect::createOptionsFromArray($data['limit_options']))
        )
    ])
    ->addItem([
        new CLabel(_('Icon Size'), 'icon_size'),
        new CFormField(
            (new CSelect('icon_size'))
                ->setId('icon_size')
                ->setValue($data['filter']['icon_size'])
                ->addOptions(CSelect::createOptionsFromArray($data['icon_sizes']))
        )
    ])
    ->addItem([
        new CLabel(_('Spacing'), 'spacing'),
        new CFormField(
            (new CSelect('spacing'))
                ->setId('spacing')
                ->setValue($data['filter']['spacing'])
                ->addOptions(CSelect::createOptionsFromArray($data['spacing_options']))
        )
    ])
    ->addItem([
        new CLabel(_('Filter Groups'), 'filter_groups'),
        new CFormField(
            (new CTextBox('filter_groups', $data['filter']['groups']))
                ->setId('filter_groups')
                ->setWidth(ZBX_TEXTAREA_FILTER_STANDARD_WIDTH)
                ->setAttribute('placeholder', _('Search hostgroup name...'))
        )
    ])
    ->addItem([
        new CLabel(_('Show Alerts Only'), 'filter_alerts_only'),
        new CFormField(
            (new CCheckBox('filter_alerts_only', 1))
                ->setId('filter_alerts_only')
                ->setChecked($data['filter']['alerts_only'] == 1)
        )
    ])
    ->addItem([
        new CFormField(
            (new CSimpleButton(_('Apply')))
                ->onClick('StatusPage.applyFilter();')
                ->addClass(ZBX_STYLE_BTN_ALT)
        )
    ]);

$filter_form->addItem($filter_table);
$widget->addItem($filter_form);

// Status summary section
$summary_div = (new CDiv())
    ->setId('status-summary')
    ->addClass('status-summary')
    ->addItem([
        (new CDiv())
            ->setId('total-groups')
            ->addClass('summary-item')
            ->addItem([
                (new CSpan())->setId('total-groups-count')->addClass('summary-count'),
                (new CSpan(_('HOST GROUPS')))->addClass('summary-label')
            ]),
        (new CDiv())
            ->setId('healthy-groups')
            ->addClass('summary-item')
            ->addItem([
                (new CSpan())->setId('healthy-groups-count')->addClass('summary-count'),
                (new CSpan(_('HEALTHY')))->addClass('summary-label')
            ]),
        (new CDiv())
            ->setId('alert-groups')
            ->addClass('summary-item')
            ->addItem([
                (new CSpan())->setId('alert-groups-count')->addClass('summary-count'),
                (new CSpan(_('WITH ALERTS')))->addClass('summary-label')
            ])
    ]);

$widget->addItem($summary_div);

// Canvas container
$canvas_container = (new CDiv())
    ->setId('status-canvas-container')
    ->addClass('status-canvas-container')
    ->addItem(
        (new CTag('canvas', false))
            ->setId('status-canvas')
            ->setAttribute('width', '100%')
            ->setAttribute('height', '600')
    );

$widget->addItem($canvas_container);

// Tooltip container
$tooltip = (new CDiv())
    ->setId('status-tooltip')
    ->addClass('status-tooltip')
    ->addStyle('display: none;');

$widget->addItem($tooltip);

// Loading overlay
$loading = (new CDiv(_('Loading...')))
    ->setId('status-loading')
    ->addClass('status-loading')
    ->addStyle('display: none;');

$widget->addItem($loading);

// Add inline JavaScript to load external JS file and initialize
$js_init = '
(function() {
    // Dynamically load the JavaScript file
    var script = document.createElement("script");
    script.src = "' . $module_url . '/js/statuspage.js";
    script.onload = function() {
        // Initialize after script loads
        jQuery(document).ready(function() {
            StatusPage.init(' . json_encode($data['filter']) . ');
        });
    };
    document.head.appendChild(script);
})();
';

$this->addJavaScript($js_init);

$widget->show();
