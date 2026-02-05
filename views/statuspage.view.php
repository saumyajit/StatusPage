<?php
/** @var CControllerResponseData $data */

$page_title = _('Status Page');
$icon_size = $data['icon_size'] ?? 'small';
$spacing = $data['spacing'] ?? 'normal';

// Calculate icon sizes
$icon_sizes = [
    'tiny' => '20px',
    'small' => '30px',
    'medium' => '40px',
    'large' => '50px'
];

$spacing_classes = [
    'normal' => 'spacing-normal',
    'compact' => 'spacing-compact',
    'ultra-compact' => 'spacing-ultra-compact'
];

$current_icon_size = $icon_sizes[$icon_size] ?? $icon_sizes['small'];
$current_spacing_class = $spacing_classes[$spacing] ?? $spacing_classes['normal'];

// Use absolute path for includes
$web_layout_mode = ZBX_LAYOUT_NORMAL;

// Include page header with correct path
(new CView('layout.html', [
    'page' => [
        'title' => $page_title,
        'file' => 'modules/StatusPage/views/statuspage.view.php'
    ],
    'data' => $data
]))->show();
?>

<!-- Status Page Content -->
<div class="status-page-container" data-module="StatusPage">
    <!-- Header Section -->
    <div class="status-page-header">
        <h1><?= _('Status Page') ?></h1>
        <div class="header-subtitle"><?= _('Real-time monitoring overview of your infrastructure') ?></div>
        
        <!-- Controls -->
        <div class="status-controls">
            <div class="control-group">
                <label for="icon-size"><?= _('Icon Size:') ?></label>
                <select id="icon-size" class="form-control">
                    <option value="tiny" <?= $icon_size === 'tiny' ? 'selected' : '' ?>>Tiny (20px)</option>
                    <option value="small" <?= $icon_size === 'small' ? 'selected' : '' ?>>Small (30px)</option>
                    <option value="medium" <?= $icon_size === 'medium' ? 'selected' : '' ?>>Medium (40px)</option>
                    <option value="large" <?= $icon_size === 'large' ? 'selected' : '' ?>>Large (50px)</option>
                </select>
            </div>
            
            <div class="control-group">
                <label for="spacing"><?= _('Spacing:') ?></label>
                <select id="spacing" class="form-control">
                    <option value="normal" <?= $spacing === 'normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="compact" <?= $spacing === 'compact' ? 'selected' : '' ?>>Compact</option>
                    <option value="ultra-compact" <?= $spacing === 'ultra-compact' ? 'selected' : '' ?>>Ultra Compact</option>
                </select>
            </div>
            
            <button id="refresh-btn" class="btn btn-primary">
                <i class="icon-refresh"></i> <?= _('Refresh') ?>
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="statistics-container">
        <div class="stat-card total-groups">
            <div class="stat-icon">
                <i class="icon-server"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $data['statistics']['total_groups'] ?? 0 ?></div>
                <div class="stat-label"><?= _('Host Groups') ?></div>
            </div>
        </div>
        
        <div class="stat-card healthy">
            <div class="stat-icon">
                <i class="icon-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $data['statistics']['healthy_hosts'] ?? 0 ?></div>
                <div class="stat-label"><?= _('Healthy Hosts') ?></div>
            </div>
        </div>
        
        <div class="stat-card with-alerts">
            <div class="stat-icon">
                <i class="icon-warning"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $data['statistics']['hosts_with_alerts'] ?? 0 ?></div>
                <div class="stat-label"><?= _('Hosts with Alerts') ?></div>
            </div>
        </div>
        
        <div class="stat-card critical-alerts">
            <div class="stat-icon">
                <i class="icon-alert"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $data['statistics']['critical_alerts'] ?? 0 ?></div>
                <div class="stat-label"><?= _('Critical Alerts') ?></div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend-container">
        <div class="legend-title"><?= _('Legend:') ?></div>
        <div class="legend-items">
            <div class="legend-item">
                <span class="legend-icon healthy-icon"></span>
                <span class="legend-text"><?= _('Healthy Host') ?></span>
            </div>
            <div class="legend-item">
                <span class="legend-icon warning-icon"></span>
                <span class="legend-text"><?= _('Warning Alert') ?></span>
            </div>
            <div class="legend-item">
                <span class="legend-icon critical-icon"></span>
                <span class="legend-text"><?= _('Critical Alert') ?></span>
            </div>
        </div>
    </div>

    <?php if (!empty($data['error'])): ?>
    <!-- Error Message -->
    <div class="alert alert-danger">
        <strong><?= _('Error:') ?></strong> <?= htmlspecialchars($data['error']) ?>
    </div>
    <?php endif; ?>

    <!-- Host Groups Grid -->
    <?php if (!empty($data['groups'])): ?>
    <div class="host-groups-container <?= $current_spacing_class ?>">
        <?php foreach ($data['groups'] as $group): ?>
        <div class="host-group" data-group-id="<?= $group['id'] ?>">
            <div class="group-header">
                <h3 class="group-name"><?= htmlspecialchars($group['name']) ?></h3>
                <span class="group-count">(<?= count($group['hosts']) ?> <?= _('hosts') ?>)</span>
            </div>
            
            <div class="hosts-grid" style="--icon-size: <?= $current_icon_size ?>;">
                <?php foreach ($group['hosts'] as $host_id => $host_status): ?>
                <div class="host-icon 
                    <?= $host_status['healthy'] ? 'status-healthy' : '' ?>
                    <?= $host_status['has_critical'] ? 'status-critical' : '' ?>
                    <?= $host_status['has_warning'] ? 'status-warning' : '' ?>"
                    data-host-id="<?= $host_id ?>"
                    title="<?= htmlspecialchars($host_status['host']) ?>: <?= $host_status['healthy'] ? _('Healthy') : ($host_status['has_critical'] ? _('Critical Alert') : _('Warning Alert')) ?>">
                    <div class="icon-inner">
                        <i class="icon-server"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- No Data Message -->
    <div class="no-data-message">
        <i class="icon-info"></i>
        <p><?= _('No host groups found or you don\'t have permission to view them.') ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" style="display: none;">
        <div class="loading-spinner"></div>
        <div class="loading-text"><?= _('Loading...') ?></div>
    </div>
</div>

<script type="text/javascript">
    // Pass data to JavaScript
    const StatusPageData = {
        module_name: 'StatusPage',
        statistics: <?= json_encode($data['statistics'] ?? []) ?>,
        groups_count: <?= $data['total_groups_count'] ?? 0 ?>,
        current_settings: {
            icon_size: '<?= $icon_size ?>',
            spacing: '<?= $spacing ?>'
        }
    };
</script>
