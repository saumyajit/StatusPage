<?php
// $groups is provided by controller
?>

<div class="status-page">

  <div class="status-toolbar">
    <label>
      <input type="checkbox" id="filterAlerts" />
      Show only groups with alerts
    </label>

    <label>
      Size:
      <select id="dotSize">
        <option value="tiny">20px</option>
        <option value="small" selected>30px</option>
        <option value="medium">40px</option>
        <option value="large">50px</option>
      </select>
    </label>
  </div>

  <div class="status-grid" id="statusGrid">
    <?php foreach ($groups as $g): ?>
      <div class="status-dot <?= $g['status'] ?>"
           data-total="<?= $g['total'] ?>"
           data-has-alert="<?= $g['total'] > 0 ? 1 : 0 ?>"
           data-tooltip='<?= json_encode($g) ?>'>
        <?= $g['total'] > 0 ? $g['total'] : '' ?>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<script src="assets/js/statuspage.js"></script>
<link rel="stylesheet" href="assets/css/statuspage.css">
