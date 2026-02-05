var StatusPage = {
    tooltip: null,
    
    init: function() {
        this.tooltip = jQuery('#status-tooltip');
        this.bindEvents();
        this.applyClientFilter();
    },
    
    bindEvents: function() {
        var self = this;
        
        // Hover events for tooltips
        jQuery('.status-icon').on('mouseenter', function(e) {
            self.showTooltip(jQuery(this), e);
        }).on('mousemove', function(e) {
            self.updateTooltipPosition(e);
        }).on('mouseleave', function() {
            self.hideTooltip();
        });
        
        // Client-side filter
        jQuery('input[name="filter_name"]').on('keyup', function() {
            self.applyClientFilter();
        });
    },
    
    showTooltip: function($icon, event) {
        var groupName = $icon.attr('data-groupname');
        var problems = JSON.parse($icon.attr('data-problems') || '[]');
        var severityCounts = JSON.parse($icon.attr('data-severity') || '{}');
        
        var html = '<div class="tooltip-header">' + this.escapeHtml(groupName) + '</div>';
        
        if (problems.length === 0) {
            html += '<div class="tooltip-status tooltip-healthy">✓ No Active Problems</div>';
        } else {
            html += '<div class="tooltip-status tooltip-problem">⚠ ' + problems.length + ' Active Problem' + (problems.length > 1 ? 's' : '') + '</div>';
            
            // Severity breakdown
            html += '<div class="tooltip-severity">';
            var severityNames = {
                '0': 'Not classified',
                '1': 'Information',
                '2': 'Warning',
                '3': 'Average',
                '4': 'High',
                '5': 'Disaster'
            };
            
            var severityClasses = {
                '0': 'severity-na',
                '1': 'severity-info',
                '2': 'severity-warn',
                '3': 'severity-avg',
                '4': 'severity-high',
                '5': 'severity-disaster'
            };
            
            for (var sev = 5; sev >= 0; sev--) {
                if (severityCounts[sev] && severityCounts[sev] > 0) {
                    html += '<div class="severity-row ' + severityClasses[sev] + '">';
                    html += '<span class="severity-label">' + severityNames[sev] + ':</span> ';
                    html += '<span class="severity-count">' + severityCounts[sev] + '</span>';
                    html += '</div>';
                }
            }
            html += '</div>';
            
            // Show first 5 problems
            if (problems.length > 0) {
                html += '<div class="tooltip-problems-list">';
                var showCount = Math.min(problems.length, 5);
                for (var i = 0; i < showCount; i++) {
                    var problem = problems[i];
                    html += '<div class="problem-item">';
                    html += '<div class="problem-host">' + this.escapeHtml(problem.hostname) + '</div>';
                    html += '<div class="problem-name">' + this.escapeHtml(problem.name) + '</div>';
                    html += '</div>';
                }
                if (problems.length > 5) {
                    html += '<div class="problem-more">... and ' + (problems.length - 5) + ' more</div>';
                }
                html += '</div>';
            }
        }
        
        this.tooltip.html(html).show();
        this.updateTooltipPosition(event);
    },
    
    updateTooltipPosition: function(event) {
        var tooltipWidth = this.tooltip.outerWidth();
        var tooltipHeight = this.tooltip.outerHeight();
        var windowWidth = jQuery(window).width();
        var windowHeight = jQuery(window).height();
        
        var left = event.pageX + 15;
        var top = event.pageY + 15;
        
        // Adjust if tooltip goes off screen
        if (left + tooltipWidth > windowWidth) {
            left = event.pageX - tooltipWidth - 15;
        }
        
        if (top + tooltipHeight > windowHeight + jQuery(window).scrollTop()) {
            top = event.pageY - tooltipHeight - 15;
        }
        
        this.tooltip.css({
            left: left + 'px',
            top: top + 'px'
        });
    },
    
    hideTooltip: function() {
        this.tooltip.hide();
    },
    
    applyClientFilter: function() {
        var filterText = jQuery('input[name="filter_name"]').val().toLowerCase();
        
        jQuery('.status-icon').each(function() {
            var groupName = jQuery(this).attr('data-groupname').toLowerCase();
            if (filterText === '' || groupName.indexOf(filterText) !== -1) {
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }
        });
    },
    
    escapeHtml: function(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
};
