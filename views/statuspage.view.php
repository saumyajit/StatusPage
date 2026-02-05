<?php declare(strict_types = 1);

/**
 * @var CView $this
 * @var array $data
 */

$this->addJsFile('class.statuspage.js');
$this->includeJsFile('statuspage.view.js.php');

$this->enableLayoutModes();
$web_layout_mode = $this->getLayoutMode();

$html_page = (new CHtmlPage())
    ->setTitle(_('Status Page'))
    ->setWebLayoutMode($web_layout_mode)
    ->setControls(
        (new CTag('nav', true,
            (new CList())
                ->addItem(
                    (new CSimpleButton(_('Settings')))
                        ->onClick('statusPageSettings()')
                        ->addClass(ZBX_STYLE_BTN_ALT)
                )
        ))->setAttribute('aria-label', _('Content controls'))
    );

// Main content widget
$html_page->addItem(
    (new CDiv([
        (new CDiv())
            ->setId('statuspage-controls')
            ->addClass('statuspage-controls'),
        (new CDiv())
            ->setId('statuspage-container')
            ->addClass('statuspage-container')
    ]))
        ->addClass('statuspage-wrapper')
);

$html_page->show();
