/**
 * Status Page Canvas Renderer
 */
var StatusPage = (function() {
    'use strict';
    
    var config = {
        canvasId: 'status-canvas',
        tooltipId: 'status-tooltip',
        loadingId: 'status-loading',
        iconSize: 30,
        spacing: 'normal',
        limit: 500,
        filter: {
            groups: '',
            alerts_only: 0
        },
        refreshInterval: 60000 // 60 seconds
    };
    
    var canvas, ctx, tooltip;
    var statusData = [];
    var circles = [];
    var refreshTimer = null;
    var hoveredCircle = null;
    
    /**
     * Initialize the Status Page
     */
    function init(filterConfig) {
        canvas = document.getElementById(config.canvasId);
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }
        
        ctx = canvas.getContext('2d');
        tooltip = document.getElementById(config.tooltipId);
        
        // Merge filter config
        if (filterConfig) {
            config.iconSize = filterConfig.icon_size || 30;
            config.spacing = filterConfig.spacing || 'normal';
            config.limit = filterConfig.limit || 500;
            config.filter.groups = filterConfig.groups || '';
            config.filter.alerts_only = filterConfig.alerts_only || 0;
        }
        
        // Set canvas size
        resizeCanvas();
        
        // Event listeners
        window.addEventListener('resize', resizeCanvas);
        canvas.addEventListener('mousemove', handleMouseMove);
        canvas.addEventListener('mouseout', handleMouseOut);
        canvas.addEventListener('click', handleClick);
        
        // Load data
        loadData();
        
        // Auto-refresh
        startAutoRefresh();
    }
    
    /**
     * Resize canvas to fill container
     */
    function resizeCanvas() {
        var container = canvas.parentElement;
        canvas.width = container.clientWidth;
        canvas.height = Math.max(600, container.clientHeight);
        
        if (statusData.length > 0) {
            renderCanvas();
        }
    }
    
    /**
     * Load status data from server
     */
    function loadData() {
        showLoading(true);
        
        var params = {
            filter_groups: config.filter.groups,
            filter_alerts_only: config.filter.alerts_only,
            limit: config.limit,
            offset: 0
        };
        
        jQuery.ajax({
            url: 'zabbix.php?action=status.page.data',
            method: 'POST',
            data: params,
            dataType: 'json',
            success: function(response) {
                statusData = response.data || [];
                updateSummary();
                renderCanvas();
                showLoading(false);
            },
            error: function(xhr, status, error) {
                console.error('Failed to load status data:', error);
                showLoading(false);
            }
        });
    }
    
    /**
     * Update summary statistics
     */
    function updateSummary() {
        var total = statusData.length;
        var withAlerts = statusData.filter(function(item) {
            return item.has_problems;
        }).length;
        var healthy = total - withAlerts;
        
        jQuery('#total-groups-count').text(total);
        jQuery('#healthy-groups-count').text(healthy);
        jQuery('#alert-groups-count').text(withAlerts);
    }
    
    /**
     * Render circles on canvas
     */
    function renderCanvas() {
        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        circles = [];
        
        if (statusData.length === 0) {
            renderEmptyState();
            return;
        }
        
        var radius = config.iconSize / 2;
        var padding = config.spacing === 'compact' ? 5 : 10;
        var diameter = config.iconSize + padding;
        
        var cols = Math.floor(canvas.width / diameter);
        var startX = (canvas.width - (cols * diameter)) / 2 + radius + padding/2;
        var startY = radius + padding;
        
        var x = startX;
        var y = startY;
        var col = 0;
        
        statusData.forEach(function(item, index) {
            // Draw circle
            var color = item.has_problems ? '#d32f2f' : '#4caf50';
            
            // Store circle data for hit detection
            circles.push({
                x: x,
                y: y,
                radius: radius,
                data: item
            });
            
            // Draw circle
            ctx.beginPath();
            ctx.arc(x, y, radius, 0, 2 * Math.PI);
            ctx.fillStyle = color;
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();
            
            // Draw problem count if has problems
            if (item.has_problems && item.problem_count > 0) {
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold ' + Math.max(10, config.iconSize * 0.4) + 'px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(item.problem_count, x, y);
            }
            
            // Update position for next circle
            col++;
            if (col >= cols) {
                col = 0;
                x = startX;
                y += diameter;
            } else {
                x += diameter;
            }
        });
    }
    
    /**
     * Render empty state message
     */
    function renderEmptyState() {
        ctx.fillStyle = '#666';
        ctx.font = '18px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('No hostgroups found', canvas.width / 2, canvas.height / 2);
    }
    
    /**
     * Handle mouse move over canvas
     */
    function handleMouseMove(e) {
        var rect = canvas.getBoundingClientRect();
        var mouseX = e.clientX - rect.left;
        var mouseY = e.clientY - rect.top;
        
        var found = false;
        
        for (var i = 0; i < circles.length; i++) {
            var circle = circles[i];
            var dist = Math.sqrt(
                Math.pow(mouseX - circle.x, 2) + 
                Math.pow(mouseY - circle.y, 2)
            );
            
            if (dist < circle.radius) {
                found = true;
                hoveredCircle = circle;
                canvas.style.cursor = 'pointer';
                showTooltip(e.clientX, e.clientY, circle.data);
                break;
            }
        }
        
        if (!found) {
            hoveredCircle = null;
            canvas.style.cursor = 'default';
            hideTooltip();
        }
    }
    
    /**
     * Handle mouse out of canvas
     */
    function handleMouseOut() {
        hoveredCircle = null;
        canvas.style.cursor = 'default';
        hideTooltip();
    }
    
    /**
     * Handle click on circle
     */
    function handleClick(e) {
        if (hoveredCircle) {
            // Navigate to hostgroup problems page
            var groupid = hoveredCircle.data.groupid;
            window.location.href = 'zabbix.php?action=problem.view&groupids[]=' + groupid;
        }
    }
    
    /**
     * Show tooltip
     */
    function showTooltip(x, y, data) {
        var html = '<div class="tooltip-header">' + escapeHtml(data.name) + '</div>';
        
        if (data.has_problems) {
            html += '<div class="tooltip-body">';
            html += '<div class="severity-counts">';
            
            var severities = [
                {level: 5, name: 'Disaster', count: data.severity_counts[5]},
                {level: 4, name: 'High', count: data.severity_counts[4]},
                {level: 3, name: 'Average', count: data.severity_counts[3]},
                {level: 2, name: 'Warning', count: data.severity_counts[2]},
                {level: 1, name: 'Information', count: data.severity_counts[1]}
            ];
            
            severities.forEach(function(sev) {
                if (sev.count > 0) {
                    html += '<div class="severity-item severity-' + sev.level + '">';
                    html += '<span class="severity-name">' + sev.name + ':</span> ';
                    html += '<span class="severity-count">' + sev.count + '</span>';
                    html += '</div>';
                }
            });
            
            html += '</div>';
            
            // Show recent problems (max 5)
            if (data.problems.length > 0) {
                html += '<div class="tooltip-problems">';
                html += '<strong>Recent problems:</strong><br>';
                data.problems.slice(0, 5).forEach(function(problem) {
                    html += '• ' + escapeHtml(problem.name) + '<br>';
                });
                if (data.problems.length > 5) {
                    html += '<em>... and ' + (data.problems.length - 5) + ' more</em>';
                }
                html += '</div>';
            }
            
            html += '</div>';
        } else {
            html += '<div class="tooltip-body">';
            html += '<div class="status-ok">All systems operational</div>';
            html += '</div>';
        }
        
        tooltip.innerHTML = html;
        tooltip.style.display = 'block';
        tooltip.style.left = (x + 15) + 'px';
        tooltip.style.top = (y + 15) + 'px';
    }
    
    /**
     * Hide tooltip
     */
    function hideTooltip() {
        tooltip.style.display = 'none';
    }
    
    /**
     * Show/hide loading overlay
     */
    function showLoading(show) {
        var loading = document.getElementById(config.loadingId);
        loading.style.display = show ? 'block' : 'none';
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Apply filter
     */
    function applyFilter() {
        config.filter.groups = jQuery('#filter_groups').val();
        config.filter.alerts_only = jQuery('#filter_alerts_only').is(':checked') ? 1 : 0;
        config.iconSize = parseInt(jQuery('#icon_size').val());
        config.spacing = jQuery('#spacing').val();
        config.limit = parseInt(jQuery('#limit').val());
        
        loadData();
    }
    
    /**
     * Refresh data
     */
    function refresh() {
        loadData();
    }
    
    /**
     * Start auto-refresh timer
     */
    function startAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
        }
        refreshTimer = setInterval(function() {
            loadData();
        }, config.refreshInterval);
    }
    
    /**
     * Stop auto-refresh
     */
    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }
    
    // Public API
    return {
        init: init,
        refresh: refresh,
        applyFilter: applyFilter,
        startAutoRefresh: startAutoRefresh,
        stopAutoRefresh: stopAutoRefresh
    };
})();
