class StatusPageApp {
    constructor(containerId, config) {
        this.container = document.getElementById(containerId);
        this.config = config;
        this.data = null;
        this.tooltip = null;
    }

    init() {
        this.container.innerHTML = '<div class="loading">Loading status data...</div>';
    }

    updateConfig(config) {
        this.config = config;
    }

    loadData() {
        const curl = new Curl('zabbix.php');
        curl.setArgument('action', 'statuspage.data');
        curl.setArgument('icon_size', this.config.icon_size);
        curl.setArgument('view_style', this.config.view_style);
        curl.setArgument('show_alert_count', this.config.show_alert_count ? 1 : 0);
        curl.setArgument('compact_mode', this.config.compact_mode ? 1 : 0);

        fetch(curl.getUrl())
            .then(response => response.json())
            .then(result => {
                try {
                    this.data = JSON.parse(result.main_block);
                    this.render();
                } catch(e) {
                    this.showError('Failed to parse data: ' + e.message);
                }
            })
            .catch(error => {
                this.showError('Failed to load data: ' + error.message);
            });
    }

    render() {
        if (!this.data || !this.data.status_data) {
            this.showError('No data available');
            return;
        }

        const compactClass = this.config.compact_mode ? 'ultra-compact' : '';
        let html = `<div class="status-page-compact ${compactClass}" data-icon-size="${this.config.icon_size}">`;

        switch(parseInt(this.config.view_style)) {
            case 0:
                html += this.renderHoneycomb(this.data.status_data);
                break;
            case 1:
                html += this.renderCircleGrid(this.data.status_data);
                break;
            case 2:
                html += this.renderSquareGrid(this.data.status_data);
                break;
        }

        html += '</div>';
        this.container.innerHTML = html;
        this.setupTooltips();
    }

    renderCircleGrid(statusData) {
        const spacing = this.config.compact_mode ? 2 : 8;
        let html = `<div class="circle-grid-compact" style="gap: ${spacing}px;">`;
        
        statusData.forEach(item => {
            const statusClass = item.has_alerts ? 'alert-active' : 'alert-none';
            const severityClass = this.getSeverityClass(item.max_severity);
            
            html += `
                <div class="circle-compact ${statusClass} ${severityClass}" 
                     data-groupid="${item.groupid}"
                     data-tooltip='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                     style="width: ${this.config.icon_size}px; height: ${this.config.icon_size}px;">
                    ${this.config.show_alert_count && item.alert_count > 0 ? 
                        `<div class="alert-badge-mini">${item.alert_count > 99 ? '99+' : item.alert_count}</div>` : ''}
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    renderHoneycomb(statusData) {
        const spacing = this.config.compact_mode ? 2 : 8;
        let html = `<div class="honeycomb-compact" style="gap: ${spacing}px;">`;
        
        statusData.forEach(item => {
            const statusClass = item.has_alerts ? 'alert-active' : 'alert-none';
            const severityClass = this.getSeverityClass(item.max_severity);
            const size = this.config.icon_size;
            
            html += `
                <div class="hex-compact ${statusClass} ${severityClass}" 
                     data-groupid="${item.groupid}"
                     data-tooltip='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                     style="width: ${size}px; height: ${size * 1.15}px;">
                    <div class="hex-inner">
                        ${this.config.show_alert_count && item.alert_count > 0 ? 
                            `<div class="alert-badge-mini">${item.alert_count > 99 ? '99+' : item.alert_count}</div>` : ''}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    renderSquareGrid(statusData) {
        const spacing = this.config.compact_mode ? 2 : 8;
        let html = `<div class="square-grid-compact" style="gap: ${spacing}px;">`;
        
        statusData.forEach(item => {
            const statusClass = item.has_alerts ? 'alert-active' : 'alert-none';
            const severityClass = this.getSeverityClass(item.max_severity);
            
            html += `
                <div class="square-compact ${statusClass} ${severityClass}" 
                     data-groupid="${item.groupid}"
                     data-tooltip='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                     style="width: ${this.config.icon_size}px; height: ${this.config.icon_size}px;">
                    ${this.config.show_alert_count && item.alert_count > 0 ? 
                        `<div class="alert-badge-mini">${item.alert_count > 99 ? '99+' : item.alert_count}</div>` : ''}
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    setupTooltips() {
        const items = this.container.querySelectorAll('.circle-compact, .hex-compact, .square-compact');
        
        items.forEach(item => {
            item.addEventListener('mouseenter', (e) => {
                const data = JSON.parse(item.getAttribute('data-tooltip'));
                this.showTooltip(e, data);
            });
            
            item.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });

            item.addEventListener('mousemove', (e) => {
                this.updateTooltipPosition(e);
            });
        });
    }

    showTooltip(event, item) {
        this.hideTooltip();
        
        let html = `<div class="tooltip-header"><strong>${this.escapeHtml(item.name)}</strong></div>`;
        html += `<div class="tooltip-subheader">${this.escapeHtml(item.full_name)}</div>`;
        
        if (item.alerts && item.alerts.length > 0) {
            html += '<div class="tooltip-alerts">';
            html += `<div class="tooltip-section">Active Alerts (${item.alert_count}):</div>`;
            
            item.alerts.forEach(alert => {
                const severityClass = this.getSeverityClass(alert.severity);
                html += `
                    <div class="alert-item">
                        <span class="severity-badge ${severityClass}">${this.getSeverityName(alert.severity)}</span>
                        <div class="alert-name">${this.escapeHtml(alert.name)}</div>
                        <div class="alert-time">${alert.time}</div>
                    </div>
                `;
            });
            
            if (item.alert_count > 20) {
                html += `<div class="alert-more">... and ${item.alert_count - 20} more alerts</div>`;
            }
            
            html += '</div>';
        } else {
            html += '<div class="tooltip-status-ok">✓ No active alerts</div>';
        }
        
        const tooltip = document.createElement('div');
        tooltip.className = 'status-page-tooltip-compact show';
        tooltip.innerHTML = html;
        document.body.appendChild(tooltip);
        
        this.tooltip = tooltip;
        this.updateTooltipPosition(event);
    }

    updateTooltipPosition(event) {
        if (!this.tooltip) return;
        
        const offset = 15;
        let left = event.pageX + offset;
        let top = event.pageY + offset;
        
        const tooltipRect = this.tooltip.getBoundingClientRect();
        if (left + tooltipRect.width > window.innerWidth) {
            left = event.pageX - tooltipRect.width - offset;
        }
        if (top + tooltipRect.height > window.innerHeight) {
            top = event.pageY - tooltipRect.height - offset;
        }
        
        this.tooltip.style.left = left + 'px';
        this.tooltip.style.top = top + 'px';
    }

    hideTooltip() {
        if (this.tooltip) {
            this.tooltip.remove();
            this.tooltip = null;
        }
    }

    getSeverityClass(severity) {
        const severities = {
            '0': 'not-classified',
            '1': 'information',
            '2': 'warning',
            '3': 'average',
            '4': 'high',
            '5': 'disaster'
        };
        return severities[String(severity)] || 'not-classified';
    }

    getSeverityName(severity) {
        const names = {
            '0': 'Not classified',
            '1': 'Information',
            '2': 'Warning',
            '3': 'Average',
            '4': 'High',
            '5': 'Disaster'
        };
        return names[String(severity)] || 'Unknown';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showError(message) {
        this.container.innerHTML = `<div class="statuspage-error">${message}</div>`;
    }
}
