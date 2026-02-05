<?php
// Get data from controller
$statistics = $data['statistics'] ?? [];
$groups = $data['groups'] ?? [];
$icon_size = $data['icon_size'] ?? 'small';
$spacing = $data['spacing'] ?? 'normal';
$error = $data['error'] ?? null;
$test_mode = $data['test_mode'] ?? false;
?>

<div class="status-page-container">
    <!-- Header -->
    <div class="status-header">
        <h1><?= _('Status Page') ?></h1>
        <div class="header-subtitle"><?= _('Real-time infrastructure monitoring overview') ?></div>
        
        <!-- Controls -->
        <div class="status-controls">
            <div class="control-item">
                <label for="icon-size"><?= _('Icon Size:') ?></label>
                <select id="icon-size" class="form-control">
                    <option value="tiny" <?= $icon_size === 'tiny' ? 'selected' : '' ?>>Tiny (20px)</option>
                    <option value="small" <?= $icon_size === 'small' ? 'selected' : '' ?>>Small (30px)</option>
                    <option value="medium" <?= $icon_size === 'medium' ? 'selected' : '' ?>>Medium (40px)</option>
                    <option value="large" <?= $icon_size === 'large' ? 'selected' : '' ?>>Large (50px)</option>
                </select>
            </div>
            
            <div class="control-item">
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
    <div class="statistics-grid">
        <div class="stat-card">
            <div class="stat-icon total-groups">
                <i class="icon-server"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?= $statistics['total_groups'] ?></div>
                <div class="stat-label"><?= _('Host Groups') ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon total-hosts">
                <i class="icon-monitoring"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?= $statistics['total_hosts'] ?></div>
                <div class="stat-label"><?= _('Total Hosts') ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon healthy">
                <i class="icon-check"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?= $statistics['healthy_hosts'] ?></div>
                <div class="stat-label"><?= _('Healthy') ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon with-alerts">
                <i class="icon-warning"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?= $statistics['hosts_with_alerts'] ?></div>
                <div class="stat-label"><?= _('With Alerts') ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon health-percentage">
                <i class="icon-percentage"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?= $statistics['health_percentage'] ?>%</div>
                <div class="stat-label"><?= _('Health') ?></div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend">
        <div class="legend-title"><?= _('Legend:') ?></div>
        <div class="legend-items">
            <div class="legend-item">
                <span class="legend-dot healthy-dot"></span>
                <span class="legend-text"><?= _('Healthy') ?></span>
            </div>
            <div class="legend-item">
                <span class="legend-dot warning-dot"></span>
                <span class="legend-text"><?= _('Warning') ?></span>
            </div>
            <div class="legend-item">
                <span class="legend-dot critical-dot"></span>
                <span class="legend-text"><?= _('Critical') ?></span>
            </div>
            <div class="legend-item">
                <span class="legend-dot info-dot"></span>
                <span class="legend-text"><?= _('Info') ?></span>
            </div>
        </div>
    </div>

    <!-- Error Message -->
    <?php if ($error): ?>
    <div class="alert alert-error">
        <strong><?= _('Error:') ?></strong> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Test Mode Notice -->
    <?php if ($test_mode): ?>
    <div class="alert alert-info">
        <strong><?= _('Note:') ?></strong> <?= _('Running with sample data. Real API integration coming soon.') ?>
    </div>
    <?php endif; ?>

    <!-- Host Groups -->
    <?php if (!empty($groups)): ?>
    <div class="host-groups-container spacing-<?= $spacing ?>">
        <?php foreach ($groups as $group): ?>
        <div class="host-group">
            <div class="group-header">
                <h3 class="group-name"><?= htmlspecialchars($group['name']) ?></h3>
                <span class="group-count"><?= count($group['hosts']) ?> <?= _('hosts') ?></span>
            </div>
            
            <div class="hosts-grid icon-size-<?= $icon_size ?>">
                <?php foreach ($group['hosts'] as $host_id => $host): ?>
                <div class="host-icon 
                    <?= $host['healthy'] ? 'status-healthy' : '' ?>
                    <?= $host['has_critical'] ? 'status-critical' : '' ?>
                    <?= $host['has_warning'] ? 'status-warning' : '' ?>
                    <?= $host['has_info'] ? 'status-info' : '' ?>"
                    data-host-id="<?= $host_id ?>"
                    title="<?= htmlspecialchars($host['host']) ?> - <?= htmlspecialchars($host['name']) ?>">
                    <div class="icon-inner">
                        <i class="icon-host"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="no-data">
        <i class="icon-info"></i>
        <p><?= _('No host groups found.') ?></p>
    </div>
    <?php endif; ?>

    <!-- Loading Overlay -->
    <div class="loading-overlay" style="display: none;">
        <div class="spinner"></div>
        <div class="loading-text"><?= _('Loading...') ?></div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Icon size change
    document.getElementById('icon-size').addEventListener('change', function() {
        const size = this.value;
        window.location.href = '?action=statuspage.view&icon_size=' + size;
    });
    
    // Spacing change
    document.getElementById('spacing').addEventListener('change', function() {
        const spacing = this.value;
        window.location.href = '?action=statuspage.view&spacing=' + spacing;
    });
    
    // Refresh button
    document.getElementById('refresh-btn').addEventListener('click', function() {
        window.location.href = '?action=statuspage.view&refresh=1';
    });
    
    // Host icon click
    document.querySelectorAll('.host-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const hostId = this.dataset.hostId;
            if (hostId) {
                window.open('zabbix.php?action=host.edit&hostid=' + hostId, '_blank');
            }
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            window.location.href = '?action=statuspage.view&refresh=1';
        }
    });
});
</script>

<!-- CSS Styles -->
<style>
.status-page-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.status-header {
    margin-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 20px;
}

.status-header h1 {
    color: #333;
    margin: 0 0 5px 0;
    font-size: 28px;
}

.header-subtitle {
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
}

.status-controls {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.control-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.control-item label {
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
}

.form-control {
    padding: 6px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: white;
    min-width: 150px;
}

.btn-primary {
    background: #0068b5;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover {
    background: #005a9e;
}

/* Statistics Grid */
.statistics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-icon.total-groups { background: #3498db; }
.stat-icon.total-hosts { background: #9b59b6; }
.stat-icon.healthy { background: #2ecc71; }
.stat-icon.with-alerts { background: #f39c12; }
.stat-icon.health-percentage { background: #1abc9c; }

.stat-info {
    flex: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

/* Legend */
.legend {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 30px;
    border: 1px solid #dee2e6;
}

.legend-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
    font-size: 16px;
}

.legend-items {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: inline-block;
}

.healthy-dot { background: #2ecc71; }
.warning-dot { background: #f39c12; }
.critical-dot { background: #e74c3c; }
.info-dot { background: #3498db; }

.legend-text {
    color: #495057;
    font-size: 14px;
}

/* Host Groups */
.host-groups-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.host-group {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.group-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.group-name {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.group-count {
    font-size: 14px;
    color: #666;
    background: #f8f9fa;
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 500;
}

/* Hosts Grid */
.hosts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(30px, 1fr));
    gap: 10px;
}

.icon-size-tiny .hosts-grid {
    grid-template-columns: repeat(auto-fill, minmax(20px, 1fr));
    gap: 5px;
}

.icon-size-small .hosts-grid {
    grid-template-columns: repeat(auto-fill, minmax(30px, 1fr));
    gap: 10px;
}

.icon-size-medium .hosts-grid {
    grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
    gap: 12px;
}

.icon-size-large .hosts-grid {
    grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
    gap: 15px;
}

/* Spacing */
.spacing-normal .hosts-grid {
    gap: 10px;
}

.spacing-compact .hosts-grid {
    gap: 5px;
}

.spacing-ultra-compact .hosts-grid {
    gap: 2px;
}

/* Host Icon */
.host-icon {
    width: 100%;
    aspect-ratio: 1/1;
    border-radius: 50%;
    cursor: pointer;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}

.host-icon:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    z-index: 10;
}

.icon-inner {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    color: #666;
    font-size: 70%;
}

/* Status Colors */
.status-healthy .icon-inner {
    background: #2ecc71;
    color: white;
}

.status-warning .icon-inner {
    background: #f39c12;
    color: white;
}

.status-critical .icon-inner {
    background: #e74c3c;
    color: white;
}

.status-info .icon-inner {
    background: #3498db;
    color: white;
}

/* Alerts */
.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid transparent;
}

.alert-error {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.alert-info {
    background: #d1ecf1;
    border-color: #0c5460;
    color: #0c5460;
}

/* No Data */
.no-data {
    text-align: center;
    padding: 40px;
    color: #666;
}

.no-data i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
    color: #adb5bd;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #0068b5;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-text {
    color: white;
    margin-top: 20px;
    font-size: 18px;
}

/* Responsive */
@media (max-width: 768px) {
    .status-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .control-item {
        flex-direction: column;
        align-items: stretch;
    }
    
    .statistics-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .legend-items {
        flex-direction: column;
        gap: 15px;
    }
}
</style>
