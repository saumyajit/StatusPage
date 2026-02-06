<?php
// Get data from controller
$statistics = $data['statistics'] ?? [];
$groups = $data['groups'] ?? [];
$all_tags = $data['all_tags'] ?? [];
$icon_size = $data['icon_size'] ?? '30';
$spacing = $data['spacing'] ?? 'normal';
$filter_alerts = $data['filter_alerts'] ?? false;
$search = $data['search'] ?? '';
$filter_severities = $data['filter_severities'] ?? [];
$filter_tags = $data['filter_tags'] ?? [];
$filter_alert_name = $data['filter_alert_name'] ?? '';
$filter_logic = $data['filter_logic'] ?? 'AND';
$error = $data['error'] ?? null;

// Spacing values
$spacing_map = [
    'normal' => '8px',
    'compact' => '4px',
    'ultra-compact' => '2px'
];
$gap = $spacing_map[$spacing] ?? '8px';

// Check if filters are active
$has_active_filters = !empty($filter_severities) || !empty($filter_tags) || !empty($filter_alert_name);
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

        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
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

        /* Advanced Filters Section */
        .advanced-filters {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .filter-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }

        .filter-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }

        .filter-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-title {
            font-size: 15px;
            font-weight: 700;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-badge {
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }

        .filter-toggle {
            font-size: 18px;
            transition: transform 0.3s;
        }

        .filter-toggle.collapsed {
            transform: rotate(-90deg);
        }

        .filter-body {
            padding: 20px;
            display: none;
        }

        .filter-body.expanded {
            display: block;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-weight: 600;
            font-size: 13px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filter-input {
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background: white;
            font-size: 13px;
            transition: border-color 0.2s;
        }

        .filter-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .multi-select {
            min-height: 120px;
            max-height: 200px;
            overflow-y: auto;
            padding: 8px;
        }

        .multi-select-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .multi-select-option:hover {
            background: #f0f0f0;
        }

        .multi-select-option input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .multi-select-option label {
            cursor: pointer;
            flex: 1;
            font-size: 13px;
            color: #495057;
        }

        .severity-label {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .severity-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .severity-disaster { background: #d32f2f; }
        .severity-high { background: #f44336; }
        .severity-average { background: #f57c00; }
        .severity-warning { background: #fbc02d; }
        .severity-info { background: #2196f3; }
        .severity-not-classified { background: #9e9e9e; }

        .filter-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .logic-selector {
            display: flex;
            gap: 10px;
            align-items: center;
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ced4da;
        }

        .logic-option {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .logic-option input[type="radio"] {
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

        .severity-disaster-text { color: #d32f2f; font-weight: 600; }
        .severity-high-text { color: #f44336; font-weight: 600; }
        .severity-average-text { color: #f57c00; font-weight: 600; }
        .severity-warning-text { color: #fbc02d; font-weight: 600; }
        .severity-info-text { color: #2196f3; font-weight: 600; }

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

            .filter-grid {
                grid-template-columns: 1fr;
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

        <!-- Main Controls -->
        <div class="controls-section">
            <form method="GET" action="zabbix.php" id="mainForm">
                <input type="hidden" name="action" value="status.page">
                
                <div class="controls-row">
                    <div class="control-group">
                        <label for="search"><?= _('Search Groups:') ?></label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               class="control-input search-input" 
                               placeholder="<?= _('Search host groups...') ?>"
                               value="<?= htmlspecialchars($search) ?>">
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

        <!-- Advanced Filters -->
        <div class="advanced-filters">
            <div class="filter-header" id="filterHeader">
                <div class="filter-header-left">
                    <span class="filter-toggle <?= $has_active_filters ? '' : 'collapsed' ?>" id="filterToggle">▼</span>
                    <div class="filter-title">
                        <span>🔍 <?= _('Advanced Filters') ?></span>
                        <?php if ($has_active_filters): ?>
                            <span class="filter-badge"><?= _('Active') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <span style="font-size: 12px; color: #666;">
                    <?= $has_active_filters ? _('Click to collapse') : _('Click to expand') ?>
                </span>
            </div>
            
            <div class="filter-body <?= $has_active_filters ? 'expanded' : '' ?>" id="filterBody">
                <form method="GET" action="zabbix.php" id="filterForm">
                    <input type="hidden" name="action" value="status.page">
                    <input type="hidden" name="icon_size" value="<?= htmlspecialchars($icon_size) ?>">
                    <input type="hidden" name="spacing" value="<?= htmlspecialchars($spacing) ?>">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    
                    <div class="filter-grid">
                        <!-- Alert Severity Filter -->
                        <div class="filter-field">
                            <label class="filter-label">
                                ⚠️ <?= _('Alert Severity') ?>
                            </label>
                            <div class="filter-input multi-select">
                                <?php
                                $severities = [
                                    TRIGGER_SEVERITY_DISASTER => ['name' => _('Disaster'), 'class' => 'disaster'],
                                    TRIGGER_SEVERITY_HIGH => ['name' => _('High'), 'class' => 'high'],
                                    TRIGGER_SEVERITY_AVERAGE => ['name' => _('Average'), 'class' => 'average'],
                                    TRIGGER_SEVERITY_WARNING => ['name' => _('Warning'), 'class' => 'warning'],
                                    TRIGGER_SEVERITY_INFORMATION => ['name' => _('Information'), 'class' => 'info'],
                                    TRIGGER_SEVERITY_NOT_CLASSIFIED => ['name' => _('Not classified'), 'class' => 'not-classified']
                                ];
                                foreach ($severities as $sev_value => $sev_info):
                                    $checked = in_array((string)$sev_value, $filter_severities);
                                ?>
                                <div class="multi-select-option">
                                    <input type="checkbox" 
                                           name="filter_severities[]" 
                                           value="<?= $sev_value ?>" 
                                           id="sev_<?= $sev_value ?>"
                                           <?= $checked ? 'checked' : '' ?>>
                                    <label for="sev_<?= $sev_value ?>" class="severity-label">
                                        <span class="severity-dot severity-<?= $sev_info['class'] ?>"></span>
                                        <?= $sev_info['name'] ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Alert Tags Filter -->
                        <div class="filter-field">
                            <label class="filter-label">
                                🏷️ <?= _('Alert Tags') ?>
                            </label>
                            <div class="filter-input multi-select">
                                <?php if (empty($all_tags)): ?>
                                    <div style="padding: 20px; text-align: center; color: #999; font-size: 12px;">
                                        <?= _('No tags available') ?>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($all_tags as $tag): 
                                        $checked = in_array($tag['display'], $filter_tags);
                                    ?>
                                    <div class="multi-select-option">
                                        <input type="checkbox" 
                                               name="filter_tags[]" 
                                               value="<?= htmlspecialchars($tag['display']) ?>" 
                                               id="tag_<?= md5($tag['display']) ?>"
                                               <?= $checked ? 'checked' : '' ?>>
                                        <label for="tag_<?= md5($tag['display']) ?>">
                                            <?= htmlspecialchars($tag['display']) ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Alert Name Filter -->
                        <div class="filter-field">
                            <label class="filter-label" for="filter_alert_name">
                                📝 <?= _('Alert Name (contains)') ?>
                            </label>
                            <input type="text" 
                                   id="filter_alert_name" 
                                   name="filter_alert_name" 
                                   class="filter-input" 
                                   placeholder="<?= _('Search in alert descriptions...') ?>"
                                   value="<?= htmlspecialchars($filter_alert_name) ?>">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <div class="logic-selector">
                            <span style="font-weight: 600; font-size: 13px; color: #495057;"><?= _('Filter Logic:') ?></span>
                            <div class="logic-option">
                                <input type="radio" 
                                       name="filter_logic" 
                                       value="AND" 
                                       id="logic_and"
                                       <?= $filter_logic === 'AND' ? 'checked' : '' ?>>
                                <label for="logic_and" style="cursor: pointer; font-size: 13px;">AND (all match)</label>
                            </div>
                            <div class="logic-option">
                                <input type="radio" 
                                       name="filter_logic" 
                                       value="OR" 
                                       id="logic_or"
                                       <?= $filter_logic === 'OR' ? 'checked' : '' ?>>
                                <label for="logic_or" style="cursor: pointer; font-size: 13px;">OR (any match)</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <span>✓</span>
                            <?= _('Apply Filters') ?>
                        </button>

                        <button type="button" class="btn btn-outline" id="clearFilters">
                            <span>✕</span>
                            <?= _('Clear All') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics -->
        <div class="statistics-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $statistics['total_groups'] ?></div>
                    <div class="stat-label"><?= _('TOTAL GROUPS') ?></div>
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
                    <div class="stat-value"><?= $statistics['filtered_groups'] ?></div>
                    <div class="stat-label"><?= _('SHOWING') ?></div>
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
                        <span class="legend-text"><?= _('Healthy') ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot status-warning"></span>
                        <span class="legend-text"><?= _('Warning') ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot status-average"></span>
                        <span class="legend-text"><?= _('Average') ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot status-high"></span>
                        <span class="legend-text"><?= _('High') ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot status-disaster"></span>
                        <span class="legend-text"><?= _('Disaster') ?></span>
                    </div>
                    <div class="legend-item">
                        <span style="display: inline-block; width: 20px; height: 14px; border-radius: 8px; background: rgba(255,255,255,0.95); border: 1px solid #333; text-align: center; font-size: 9px; line-height: 13px; font-weight: bold;">2</span>
                        <span class="legend-text"><?= _('Alert count') ?></span>
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
                        <div class="empty-state-subtext">
                            <?= $has_active_filters ? _('Try adjusting your filters') : _('No CUSTOMER/ groups available') ?>
                        </div>
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

        // Filter panel toggle
        const filterHeader = document.getElementById('filterHeader');
        const filterBody = document.getElementById('filterBody');
        const filterToggle = document.getElementById('filterToggle');

        filterHeader.addEventListener('click', function() {
            filterBody.classList.toggle('expanded');
            filterToggle.classList.toggle('collapsed');
        });

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
                    html += '<div class="severity-item"><span class="severity-disaster-text">⚠ Disaster:</span> <span>' + data.severityCounts[5] + '</span></div>';
                }
                if (data.severityCounts[4] > 0) {
                    html += '<div class="severity-item"><span class="severity-high-text">⚠ High:</span> <span>' + data.severityCounts[4] + '</span></div>';
                }
                if (data.severityCounts[3] > 0) {
                    html += '<div class="severity-item"><span class="severity-average-text">⚠ Average:</span> <span>' + data.severityCounts[3] + '</span></div>';
                }
                if (data.severityCounts[2] > 0) {
                    html += '<div class="severity-item"><span class="severity-warning-text">⚠ Warning:</span> <span>' + data.severityCounts[2] + '</span></div>';
                }
                if (data.severityCounts[1] > 0 || data.severityCounts[0] > 0) {
                    const infoTotal = (data.severityCounts[1] || 0) + (data.severityCounts[0] || 0);
                    html += '<div class="severity-item"><span class="severity-info-text">ℹ Info:</span> <span>' + infoTotal + '</span></div>';
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

        // Refresh button - FIXED
        document.getElementById('refreshBtn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loadingOverlay').classList.add('active');
            
            // Get current URL parameters
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('refresh', '1');
            
            window.location.href = currentUrl.toString();
        });

        // Clear filters button
        document.getElementById('clearFilters').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'zabbix.php?action=status.page';
        });

        // Auto-submit on select change
        document.getElementById('icon_size').addEventListener('change', function() {
            document.getElementById('mainForm').submit();
        });

        document.getElementById('spacing').addEventListener('change', function() {
            document.getElementById('mainForm').submit();
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
