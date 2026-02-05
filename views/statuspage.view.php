<?php
/**
 * @var CView $this
 */

$widget = (new CWidget())
    ->setTitle(_('Status Page'))
    ->addItem(new CDiv('Module loaded successfully!'));

$widget->addItem(
    new CDiv([
        new CTag('h2', true, 'Test Message:'),
        new CDiv($data['message']),
        new CBr(),
        new CDiv($data['test'])
    ])
);

$widget->show();
