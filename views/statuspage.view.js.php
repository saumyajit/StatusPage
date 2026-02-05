<?php declare(strict_types = 1); ?>

<script type="text/javascript">
    const STATUS_PAGE_CONFIG = {
        icon_size: 30,
        view_style: 1, // Circle Grid
        show_alert_count: true,
        compact_mode: false,
        auto_refresh: true,
        refresh_interval: 30000 // 30 seconds
    };

    let statusPageApp = null;
    let refreshTimer = null;

    jQuery(document).ready(function($) {
        // Initialize status page
        statusPageApp = new StatusPageApp('statuspage-container', STATUS_PAGE_CONFIG);
        statusPageApp.init();
        statusPageApp.loadData();

        // Setup controls
        setupControls();

        // Start auto-refresh
        if (STATUS_PAGE_CONFIG.auto_refresh) {
            startAutoRefresh();
        }
    });

    function setupControls() {
        const controls = jQuery('#statuspage-controls');
        
        controls.html(`
            <div class="statuspage-toolbar">
                <div class="toolbar-group">
                    <label>Icon Size:</label>
                    <select id="icon-size-select" class="toolbar-select">
                        <option value="20">Tiny (20px)</option>
                        <option value="30" selected>Small (30px)</option>
                        <option value="40">Medium (40px)</option>
                        <option value="50">Large (50px)</option>
                    </select>
                </div>
                <div class="toolbar-group">
                    <label>View:</label>
                    <select id="view-style-select" class="toolbar-select">
                        <option value="0">Honeycomb</option>
                        <option value="1" selected>Circle Grid</option>
                        <option value="2">Square Grid</option>
                    </select>
                </div>
                <div class="toolbar-group">
                    <label>
                        <input type="checkbox" id="compact-mode" ${STATUS_PAGE_CONFIG.compact_mode ? 'checked' : ''}>
                        Ultra Compact
                    </label>
                </div>
                <div class="toolbar-group">
                    <label>
                        <input type="checkbox" id="show-badges" ${STATUS_PAGE_CONFIG.show_alert_count ? 'checked' : ''}>
                        Show Badges
                    </label>
                </div>
                <div class="toolbar-group">
                    <button id="refresh-btn" class="toolbar-button">🔄 Refresh</button>
                </div>
            </div>
        `);

        // Event handlers
        jQuery('#icon-size-select').on('change', function() {
            STATUS_PAGE_CONFIG.icon_size = parseInt(jQuery(this).val());
            statusPageApp.updateConfig(STATUS_PAGE_CONFIG);
            statusPageApp.render();
        });

        jQuery('#view-style-select').on('change', function() {
            STATUS_PAGE_CONFIG.view_style = parseInt(jQuery(this).val());
            statusPageApp.updateConfig(STATUS_PAGE_CONFIG);
            statusPageApp.render();
        });

        jQuery('#compact-mode').on('change', function() {
            STATUS_PAGE_CONFIG.compact_mode = jQuery(this).is(':checked');
            statusPageApp.updateConfig(STATUS_PAGE_CONFIG);
            statusPageApp.render();
        });

        jQuery('#show-badges').on('change', function() {
            STATUS_PAGE_CONFIG.show_alert_count = jQuery(this).is(':checked');
            statusPageApp.updateConfig(STATUS_PAGE_CONFIG);
            statusPageApp.render();
        });

        jQuery('#refresh-btn').on('click', function() {
            statusPageApp.loadData();
        });
    }

    function startAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
        }
        refreshTimer = setInterval(function() {
            statusPageApp.loadData();
        }, STATUS_PAGE_CONFIG.refresh_interval);
    }

    function statusPageSettings() {
        // Open settings dialog
        overlayDialogue({
            'title': <?= json_encode(_('Status Page Settings')) ?>,
            'content': jQuery('<div>').html('Settings dialog content'),
            'buttons': [
                {
                    'title': <?= json_encode(_('Save')) ?>,
                    'class': 'dialogue-widget-save',
                    'action': function() {
                        // Save settings
                    }
                },
                {
                    'title': <?= json_encode(_('Cancel')) ?>,
                    'class': 'btn-alt',
                    'action': function() {}
                }
            ]
        }, this);
    }
</script>
