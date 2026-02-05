(function($) {
    'use strict';
    
    const StatusPage = {
        // Initialize the module
        init: function() {
            this.bindEvents();
            this.initializeTooltips();
            this.applySettings();
        },
        
        // Bind event listeners
        bindEvents: function() {
            // Icon size change
            $('#icon-size').on('change', (e) => {
                this.changeIconSize($(e.target).val());
            });
            
            // Spacing change
            $('#spacing').on('change', (e) => {
                this.changeSpacing($(e.target).val());
            });
            
            // Refresh button
            $('#refresh-btn').on('click', () => {
                this.refreshData();
            });
            
            // Host icon click
            $(document).on('click', '.host-icon', (e) => {
                this.onHostIconClick($(e.currentTarget));
            });
            
            // Host icon hover
            $(document).on('mouseenter', '.host-icon', (e) => {
                this.onHostIconHover($(e.currentTarget));
            });
            
            // Keyboard shortcuts
            $(document).on('keydown', (e) => {
                this.handleKeyboardShortcuts(e);
            });
        },
        
        // Initialize tooltips
        initializeTooltips: function() {
            // Initialize jQuery UI tooltips if available
            if ($.fn.tooltip) {
                $('.host-icon').tooltip({
                    track: true,
                    show: { effect: 'fade', duration: 200 },
                    hide: { effect: 'fade', duration: 200 }
                });
            }
        },
        
        // Change icon size
        changeIconSize: function(size) {
            const sizes = {
                'tiny': '20px',
                'small': '30px',
                'medium': '40px',
                'large': '50px'
            };
            
            const newSize = sizes[size] || sizes.small;
            
            // Update CSS variable
            $('.hosts-grid').css('--icon-size', newSize);
            
            // Update icon font size
            const fontSize = parseInt(newSize) * 0.5;
            $('.icon-inner').css('font-size', fontSize + 'px');
            
            // Save setting
            this.saveSetting('icon_size', size);
            
            // Update tooltips
            this.updateTooltips();
        },
        
        // Change spacing
        changeSpacing: function(spacing) {
            const spacingClasses = {
                'normal': 'spacing-normal',
                'compact': 'spacing-compact',
                'ultra-compact': 'spacing-ultra-compact'
            };
            
            const container = $('.host-groups-container');
            
            // Remove existing spacing classes
            container.removeClass('spacing-normal spacing-compact spacing-ultra-compact');
            
            // Add new spacing class
            container.addClass(spacingClasses[spacing] || spacingClasses.normal);
            
            // Save setting
            this.saveSetting('spacing', spacing);
        },
        
        // Refresh data
        refreshData: function() {
            this.showLoading();
            
            // Send AJAX request to refresh data
            $.ajax({
                url: window.location.href,
                type: 'GET',
                data: {
                    refresh: 1,
                    icon_size: $('#icon-size').val(),
                    spacing: $('#spacing').val()
                },
                success: (response) => {
                    this.hideLoading();
                    
                    // Parse response and update page
                    this.updatePageData(response);
                    
                    // Show success notification
                    this.showNotification('Data refreshed successfully', 'success');
                },
                error: (xhr, status, error) => {
                    this.hideLoading();
                    
                    // Show error notification
                    this.showNotification('Failed to refresh data: ' + error, 'error');
                    
                    console.error('Refresh error:', error);
                }
            });
        },
        
        // Show loading overlay
        showLoading: function() {
            $('.loading-overlay').fadeIn(200);
        },
        
        // Hide loading overlay
        hideLoading: function() {
            $('.loading-overlay').fadeOut(200);
        },
        
        // Host icon click handler
        onHostIconClick: function($icon) {
            const hostId = $icon.data('host-id');
            const groupId = $icon.closest('.host-group').data('group-id');
            
            // Navigate to host details page
            const url = new URL('/zabbix.php', window.location.origin);
            url.searchParams.set('action', 'host.edit');
            url.searchParams.set('hostid', hostId);
            
            window.open(url, '_blank');
        },
        
        // Host icon hover handler
        onHostIconHover: function($icon) {
            const hostId = $icon.data('host-id');
            const status = $icon.hasClass('status-healthy') ? 'healthy' : 
                          $icon.hasClass('status-critical') ? 'critical' : 'warning';
            
            // You can add additional hover effects here
            $icon.addClass('hover-active');
        },
        
        // Handle keyboard shortcuts
        handleKeyboardShortcuts: function(e) {
            // Ctrl/Cmd + R to refresh
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                this.refreshData();
            }
            
            // Escape to clear selections
            if (e.key === 'Escape') {
                $('.host-icon').removeClass('selected');
            }
        },
        
        // Apply saved settings
        applySettings: function() {
            // Get saved settings from localStorage
            const savedIconSize = localStorage.getItem('statuspage_icon_size') || 'small';
            const savedSpacing = localStorage.getItem('statuspage_spacing') || 'normal';
            
            // Apply settings to controls
            $('#icon-size').val(savedIconSize);
            $('#spacing').val(savedSpacing);
            
            // Apply settings to page
            this.changeIconSize(savedIconSize);
            this.changeSpacing(savedSpacing);
        },
        
        // Save setting to localStorage
        saveSetting: function(key, value) {
            localStorage.setItem('statuspage_' + key, value);
        },
        
        // Update page data after refresh
        updatePageData: function(data) {
            // This would update the statistics and host groups
            // Implementation depends on your API response structure
            
            // For now, just reload the page
            window.location.reload();
        },
        
        // Update tooltips after icon size change
        updateTooltips: function() {
            if ($.fn.tooltip) {
                $('.host-icon').tooltip('destroy');
                this.initializeTooltips();
            }
        },
        
        // Show notification
        showNotification: function(message, type) {
            // Use Zabbix's notification system if available
            if (typeof addMessage === 'function') {
                const messageType = type === 'error' ? 'error' : 'good';
                addMessage(messageType, message, 'statuspage-notification');
            } else {
                // Fallback to browser notification
                const notification = $('<div>', {
                    class: `status-notification status-notification-${type}`,
                    text: message
                });
                
                $('body').append(notification);
                
                notification.fadeIn(200);
                setTimeout(() => {
                    notification.fadeOut(200, () => notification.remove());
                }, 3000);
            }
        },
        
        // Export data to CSV
        exportToCSV: function() {
            const csvData = [];
            const headers = ['Host Group', 'Host Name', 'Status', 'Alerts'];
            
            csvData.push(headers.join(','));
            
            $('.host-group').each(function() {
                const groupName = $(this).find('.group-name').text().trim();
                
                $(this).find('.host-icon').each(function() {
                    const status = $(this).hasClass('status-healthy') ? 'Healthy' :
                                  $(this).hasClass('status-critical') ? 'Critical' : 'Warning';
                    const alerts = status === 'Healthy' ? 'None' : 'Active';
                    
                    csvData.push([
                        `"${groupName}"`,
                        `"Host ${$(this).data('host-id')}"`,
                        status,
                        alerts
                    ].join(','));
                });
            });
            
            const csvContent = csvData.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            
            a.href = url;
            a.download = `status-page-${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        StatusPage.init();
    });
    
    // Make StatusPage available globally for debugging
    window.StatusPage = StatusPage;
    
})(jQuery);
