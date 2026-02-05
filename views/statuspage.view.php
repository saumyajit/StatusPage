<?php
// Get data from controller
$statistics = $data['statistics'] ?? [];
$groups = $data['groups'] ?? [];
$icon_size = $data['icon_size'] ?? '30';
$spacing = $data['spacing'] ?? 'normal';
$filter_alerts = $data['filter_alerts'] ?? false;
$search = $data['search'] ?? '';
$error = $data['error'] ?? null;

// Spacing values
$spacing_map = [
    'normal' => '8px',
    'compact' => '4px',
    'ultra-compact' => '2px'
];
$gap = $spacing_map[$spacing] ?? '8px';
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .status-page-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding: 20px;
            background: #f5f7fa;
            min-height: 100vh;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 600;
        }

        .page-subtitle {
            font-size: 14px;
            opacity: 0.95;
            margin: 0;
        }

        /* Controls Section */
        .controls-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .controls-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .control-group label {
            font-weight: 600;
            font-size: 13px;
            color: #495057;
            white-space: nowrap;
        }

        .control-input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background: white;
            font-size: 13px;
            min-width: 130px;
            transition: border-color 0.2s;
        }

        .control-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-input {
            min-width: 250px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            font-size: 13px;
            font-weight: 600;
            color: #495057;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Statistics Section */
        .statistics-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }

        .stat-card {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef3 100%);
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Status Page Section */
        .status-page-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .status-grid-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef3 100%);
            padding: 20px;
            border-radius: 8px;
            min-height: 400px;
            max-height: 800px;
            overflow-y: auto;
        }

        .status-grid {
            display: flex;
            flex-wrap: wrap;
            gap: <?= $gap ?>;
            justify-content: center;
            align-items: flex-start;
        }

        .status-circle {
            width: <?= $icon_size ?>px;
            height: <?= $icon_size ?>px;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .status-circle:hover {
            transform: scale(1.3);
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Status Colors */
        .status-healthy {
            background: #4caf50;
        }

        .status-warning {
            background: #fbc02d;
        }

        .status-average {
            background: #f57c00;
        }

        .status-high {
            background: #f44336;
        }

        .status-disaster {
            background: #d32f2f;
        }

        /* Alert Badge */
        .alert-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            border-radius: 8px;
            padding: 1px 5px;
            font-size: 10px;
            font-weight: bold;
            min-width: 16px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            z-index: 2;
            line-height: 1.4;
        }

        /* Tooltip */
        .status-tooltip {
            position: fixed;
            background: white;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            z-index: 10000;
            max-width: 400px;
            border: 2px solid #e0e0e0;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.15s ease;
            pointer-events: none;
            font-size: 13px;
        }

        .status-tooltip.visible {
            opacity: 1;
            visibility: visible;
        }

        .tooltip-header {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #333;
        }

        .tooltip-subheader {
            font-size: 11px;
            color: #666;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tooltip-status-ok {
            color: #4caf50;
            font-weight: 600;
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }

        .tooltip-alerts {
            margin-top: 8px;
        }

        .tooltip-alert-summary {
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }

        .tooltip-severity-list {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .severity-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .severity-disaster { color: #d32f2f; font-weight: 600; }
        .severity-high { color: #f44336; font-weight: 600; }
        .severity-average { color: #f57c00; font-weight: 600; }
        .severity-warning { color: #fbc02d; font-weight: 600; }
        .severity-info { color: #2196f3; font-weight: 600; }

        /* Legend */
        .legend {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
        }

        .legend-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .legend-items {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
        }

        .legend-text {
            font-size: 13px;
            color: #495057;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .empty-state-text {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .empty-state-subtext {
            font-size: 13px;
            color: #adb5bd;
        }

        /* Alert Message */
        .alert-message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid;
        }

        .alert-error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            flex-direction: column;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: white;
            margin-top: 20px;
            font-size: 16px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }

            .control-group {
                flex-direction: column;
                align-items: stretch;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .search-input {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="status-page-wrapper">
        <!-- Header -->
        <div class="page-header">
            <h1><?= _('Status Dashboard') ?></h1>
            <p class="page-subtitle"><?= _('Visual overview of customer host groups') ?></p>
        </div>

        <!-- Controls -->
        <div class="controls-section">
            <form method="GET" action="zabbix.php" id="filterForm">
                <input type="hidden" name="action" value="status.page">
                
                <div class="controls-row">
                    <div class="control-group">
                        <label for="search"><?= _('Filter Groups:') ?></label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               class="control-input search-input" 
                               placeholder="<?= _('Search host groups...') ?>"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <div class="control-group">
                        <label class="checkbox-label">
                            <input type="checkbox" 
                                   name="filter_alerts" 
                                   value="1" 
                                   id="filter_alerts"
                                   <?= $filter_alerts ? 'checked' : '' ?>>
                            <?= _('Show: Customer Groups Only') ?>
                        </label>
                    </div>

                    <div class="control-group">
                        <label for="icon_size"><?= _('Icon Size:') ?></label>
                        <select name="icon_size" id="icon_size" class="control-input">
                            <option value="20" <?= $icon_size === '20' ? 'selected' : '' ?>>Tiny (20px)</option>
                            <option value="25" <?= $icon_size === '25' ? 'selected' : '' ?>>Small (25px)</option>
                            <option value="30" <?= $icon_size === '30' ? 'selected' : '' ?>>Medium (30px)</option>
                            <option value="35" <?= $icon_size === '35' ? 'selected' : '' ?>>Large (35px)</option>
                            <option value="40" <?= $icon_size === '40' ? 'selected' : '' ?>>X-Large (40px)</option>
                        </select>
                    </div>

                    <div class="control-group">
                        <label for="spacing"><?= _('Spacing:') ?></label>
                        <select name="spacing" id="spacing" class="control-input">
                            <option value="normal" <?= $spacing === 'normal' ? 'selected' : '' ?>>Normal</option>
                            <option value="compact" <?= $spacing === 'compact' ? 'selected' : '' ?>>Compact</option>
                            <option value="ultra-compact" <?= $spacing === 'ultra-compact' ? 'selected' : '' ?>>Ultra Compact</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <span>⚡</span>
                        <?= _('Apply') ?>
                    </button>

                    <button type="button" class="btn btn-secondary" id="refreshBtn">
                        <span>🔄</span>
                        <?= _('Refresh') ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics -->
        <div class="statistics-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $statistics['total_groups'] ?></div>
                    <div class="stat-label"><?= _('HOST GROUPS') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $statistics['healthy_groups'] ?></div>
                    <div class="stat-label"><?= _('HEALTHY') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $statistics['groups_with_alerts'] ?></div>
                    <div class="stat-label"><?= _('WITH ALERTS') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $statistics['health_percentage'] ?>%</div>
                    <div class="stat-label"><?= _('HEALTH') ?></div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
        <div class="alert-message alert-error">
            <strong><?= _('Error:') ?></strong> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Status Page -->
        <div class="status-page-section">
            <!-- Legend -->
            <div class="legend">
                <div class="legend-title"><?= _('Legend:') ?></div>
                <div class="legend-items">
                    <div class="legend-item">
                        <span class="legend-dot status-healthy"></span>
                        <span class="legend-text"><?= _('Healthy (No alerts)') ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot status-warning"></span>
                        <span class="legend-text"><?= _('Warning alerts') ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot status-average"></span>
                        <span class="legend-text"><?= _('Average alerts') ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot status-high"></span>
                        <span class="legend-text"><?= _('High alerts') ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot status-disaster"></span>
                        <span class="legend-text"><?= _('Critical alerts') ?></span>
                    </div>
                    <div class="legend-item">
                        <span style="display: inline-block; width: 20px; height: 14px; border-radius: 8px; background: rgba(255,255,255,0.95); border: 1px solid #333; text-align: center; font-size: 9px; line-height: 13px; font-weight: bold;">2</span>
                        <span class="legend-text"><?= _('Alert count badge') ?></span>
                    </div>
                </div>
            </div>

            <div class="status-grid-container">
                <?php if (!empty($groups)): ?>
                    <div class="status-grid">
                        <?php foreach ($groups as $group): ?>
                            <?php
                            $status_class = 'status-healthy';
                            if (!$group['is_healthy']) {
                                switch ($group['highest_severity']) {
                                    case TRIGGER_SEVERITY_DISASTER:
                                        $status_class = 'status-disaster';
                                        break;
                                    case TRIGGER_SEVERITY_HIGH:
                                        $status_class = 'status-high';
                                        break;
                                    case TRIGGER_SEVERITY_AVERAGE:
                                        $status_class = 'status-average';
                                        break;
                                    default:
                                        $status_class = 'status-warning';
                                        break;
                                }
                            }
                            
                            $tooltip_data = [
                                'name' => $group['short_name'],
                                'fullName' => $group['name'],
                                'alertCount' => $group['alert_count'],
                                'isHealthy' => $group['is_healthy'],
                                'severityCounts' => $group['severity_counts']
                            ];
                            ?>
                            <div class="status-circle <?= $status_class ?>"
                                 data-groupid="<?= $group['groupid'] ?>"
                                 data-tooltip='<?= htmlspecialchars(json_encode($tooltip_data), ENT_QUOTES, 'UTF-8') ?>'>
                                <?php if ($group['alert_count'] > 0 && $icon_size >= 25): ?>
                                    <div class="alert-badge">
                                        <?= $group['alert_count'] > 99 ? '99+' : $group['alert_count'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <div class="empty-state-text"><?= _('No host groups found') ?></div>
                        <div class="empty-state-subtext"><?= _('Showing 3 groups') ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tooltip -->
        <div class="status-tooltip" id="tooltip"></div>

        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner"></div>
            <div class="loading-text"><?= _('Loading...') ?></div>
        </div>
    </div>

    <script>
    (function() {
        'use strict';

        const tooltip = document.getElementById('tooltip');
        let currentCircle = null;

        // Tooltip handling
        document.querySelectorAll('.status-circle').forEach(circle => {
            circle.addEventListener('mouseenter', function(e) {
                showTooltip(e, this);
            });

            circle.addEventListener('mouseleave', function() {
                hideTooltip();
            });

            circle.addEventListener('mousemove', function(e) {
                updateTooltipPosition(e);
            });

            // Click to open group page
            circle.addEventListener('click', function() {
                const groupid = this.dataset.groupid;
                if (groupid) {
                    window.open('zabbix.php?action=hostgroup.edit&groupid=' + groupid, '_blank');
                }
            });
        });

        function showTooltip(event, circle) {
            currentCircle = circle;
            const data = JSON.parse(circle.dataset.tooltip);
            
            let html = '<div class="tooltip-header">' + escapeHtml(data.name) + '</div>';
            html += '<div class="tooltip-subheader">' + escapeHtml(data.fullName) + '</div>';
            
            if (data.isHealthy) {
                html += '<div class="tooltip-status-ok">✓ No active alerts</div>';
            } else {
                html += '<div class="tooltip-alerts">';
                html += '<div class="tooltip-alert-summary">Active Alerts: ' + data.alertCount + '</div>';
                html += '<div class="tooltip-severity-list">';
                
                if (data.severityCounts[5] > 0) {
                    html += '<div class="severity-item"><span class="severity-disaster">⚠ Disaster:</span> <span>' + data.severityCounts[5] + '</span></div>';
                }
                if (data.severityCounts[4] > 0) {
                    html += '<div class="severity-item"><span class="severity-high">⚠ High:</span> <span>' + data.severityCounts[4] + '</span></div>';
                }
                if (data.severityCounts[3] > 0) {
                    html += '<div class="severity-item"><span class="severity-average">⚠ Average:</span> <span>' + data.severityCounts[3] + '</span></div>';
                }
                if (data.severityCounts[2] > 0) {
                    html += '<div class="severity-item"><span class="severity-warning">⚠ Warning:</span> <span>' + data.severityCounts[2] + '</span></div>';
                }
                if (data.severityCounts[1] > 0 || data.severityCounts[0] > 0) {
                    const infoTotal = (data.severityCounts[1] || 0) + (data.severityCounts[0] || 0);
                    html += '<div class="severity-item"><span class="severity-info">ℹ Info:</span> <span>' + infoTotal + '</span></div>';
                }
                
                html += '</div></div>';
            }
            
            tooltip.innerHTML = html;
            tooltip.classList.add('visible');
            updateTooltipPosition(event);
        }

        function hideTooltip() {
            tooltip.classList.remove('visible');
            currentCircle = null;
        }

        function updateTooltipPosition(event) {
            if (!currentCircle) return;
            
            const offset = 15;
            let left = event.pageX + offset;
            let top = event.pageY + offset;
            
            // Adjust if tooltip goes off screen
            const tooltipRect = tooltip.getBoundingClientRect();
            if (left + tooltipRect.width > window.innerWidth) {
                left = event.pageX - tooltipRect.width - offset;
            }
            if (top + tooltipRect.height > window.innerHeight + window.scrollY) {
                top = event.pageY - tooltipRect.height - offset;
            }
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            document.getElementById('loadingOverlay').classList.add('active');
            const form = document.getElementById('filterForm');
            const url = new URL(form.action, window.location.origin);
            const formData = new FormData(form);
            formData.append('refresh', '1');
            
            const params = new URLSearchParams(formData);
            window.location.href = url.pathname + '?' + params.toString();
        });

        // Auto-submit on select change
        document.getElementById('icon_size').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        document.getElementById('spacing').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        document.getElementById('filter_alerts').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                document.getElementById('refreshBtn').click();
            }
        });
    })();
    </script>
</body>
</html>
