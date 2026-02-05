<?php declare(strict_types = 1);

/**
 * Status Page widget view.
 *
 * @var CView $this
 * @var array $data
 */

use Zabbix\Widgets\CWidgetHelper;

// Include widget CSS
$this->includeJsFile('widget.edit.js.php');
$this->addJsFile('class.widget.js');

?>

<div class="<?= ZBX_STYLE_DASHBOARD_WIDGET_ITEM ?>" data-widget-type="statuspage">
    <div class="status-page-widget-container" id="statuspage_<?= $data['uniqueid'] ?>"></div>
</div>

<script type="text/javascript">
    jQuery(function($) {
        // Widget will be initialized by class.widget.js
        var widget_data = <?= json_encode($data) ?>;
    });
</script>
