class WidgetStatusPageCompact extends CWidget {

    onInitialize() {
        this._refresh_interval = null;
        this._tooltip = null;
    }

    onActivate() {
        this.startRefresh();
    }

    onDeactivate() {
        this.stopRefresh();
        this.hideTooltip();
    }

    promiseUpdate() {
        const curl = new Curl('zabbix.php');
        curl.setArgument('action', 'statuspage.compact.view');

        this._form.getFieldsValues().forEach((value, name) => {
            if (value !== '') {
                curl.setArgument(name, value);
            }
        });

        return fetch(curl.getUrl())
            .then(response => response.json())
            .then(data => {
                this.setContents(this.renderStatusPage(data));
                this.setupTooltips();
            })
            .catch(error => {
                this.setContents({
                    body: `<div class="status-page-error">Error loading data: ${error.message}</div>`
                });
            });
    }

    renderStatusPage(data) {
        const view_style = parseInt(data.view_style || 0);
        const icon_size = parseInt(data.icon_size || 30);
        const show_alert_count = data.show_alert_count === '1' || data.show_alert_count === 1;
        const compact_mode = data.compact_mode === '1' || data.compact_mode === 1;
        
        let html = `<div class="status-page-compact ${compact_mode ? 'ultra-compact' : ''}" data-icon-size="${icon_size}">`;
        
        if (data.status_data && data.status_data.length > 0) {
            switch(view_style) {
                case 0:
                    html += this.renderHoneycomb(data.status_data, icon_size, show_alert_count, compact_mode);
                    break;
                case 1:
                    html += this.renderCircleGrid(data.status_data, icon_size, show_alert_count, compact_mode);
                    break;
                case 2:
                    html += this.renderSquareGrid(data.status_data, icon_size, show_alert_count, compact_mode);
                    break;
            }
        } else {
            html += '<div class="no-data">No host groups found</div>';
        }
        
        html += '</div>';
        
        return { body: html };
    }

    renderHoneycomb(statusData, iconSize, showBadge, compact) {
        const spacing = compact ? 2 : 8;
        const hexWidth = iconSize;
        const hexHeight = iconSize * 1.15;
        
        let html = `<div class="honeycomb-compact" style="gap: ${spacing}px;">`;
        
        statusData.forEach((item, index) => {
            const statusClass = item.has_alerts ? 'alert-active' : 'alert-none';
            const severityClass = this.getSeverityClass(item.max_severity);
            
            html += `
                <div class="hex-compact ${statusClass} ${severityClass}" 
                     data-groupid="${item.groupid}"
                     data-name="${this.escapeHtml(item.name)}"
                     style="width: ${hexWidth}px; height: ${hexHeight}px;">
                    <div class="hex-inner">
                        ${showBadge && item.alert_count > 0 ? `<div class="alert-badge-mini">${item.alert_count > 99 ? '99+' : item.alert_count}</div>` : ''}
                    </div>
                    <div class="tooltip-data" style="display:none;">
                        ${this.renderTooltipContent(item)}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    renderCircleGrid(statusData, iconSize, showBadge, compact) {
        const spacing = compact ? 2 : 8;
        
        let html = `<div class="circle-grid-compact" style="gap: ${spacing}px;">`;
        
        statusData.forEach((item, index) => {
            const statusClass = item.has_alerts ? 'alert-active' : 'alert-none';
            const severityClass = this.getSeverityClass(item.max_severity);
            
            html += `
                <div class="circle-compact ${statusClass} ${severityClass}" 
                     data-groupid="${item.groupid}"
                     data-name="${this.escapeHtml(item.name)}"
                     style="width: ${iconSize}px; height: ${iconSize}px;">
                    ${showBadge && item.alert_count > 0 ? `<div class="alert-badge-mini">${item.alert_count > 99 ? '99+' : item.alert_count}</div>` : ''}
                    <div class="tooltip-data" style="display:none;">
                        ${this.renderTooltipContent(item)}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    renderSquareGrid(statusData, iconSize, showBadge, compact) {
        const spacing = compact ? 2 : 8;
        
        let html = `<div class="square-grid-compact" style="gap: ${spacing}px;">`;
        
        statusData.forEach((item, index) => {
            const statusClass = item.has_alerts ? 'alert-active' : 'alert-none';
            const severityClass = this.getSeverityClass(item.max_severity);
            
            html += `
                <div class="square-compact ${statusClass} ${severityClass}" 
                     data-groupid="${item.groupid}"
                     data-name="${this.escapeHtml(item.name)}"
                     style="width: ${iconSize}px; height: ${iconSize}px;">
                    ${showBadge && item.alert_count > 0 ? `<div class="alert-badge-mini">${item.alert_count > 99 ? '99+' : item.alert_count}</div>` : ''}
                    <div class="tooltip-data" style="display:none;">
                        ${this.renderTooltipContent(item)}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    renderTooltipContent(item) {
        let html = `<div class="tooltip-header"><strong>${this.escapeHtml(item.name)}</strong></div>`;
        html += `<div class="tooltip-subheader">${this.escapeHtml(item.full_name)}</div>`;
        
        if (item.alerts && item.alerts.length > 0) {
            html += '<div class="tooltip-alerts">';
            html += `<div class="tooltip-section">Active Alerts (${item.alert_count}):</div>`;
            
            // Show max 20 alerts
            const displayAlerts = item.alerts.slice(0, 20);
            displayAlerts.forEach(alert => {
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
        
        return html;
    }

    setupTooltips() {
        const items = this._target.querySelectorAll('.hex-compact, .circle-compact, .square-compact');
        
        items.forEach(item => {
            item.addEventListener('mouseenter', (e) => {
                const tooltipData = item.querySelector('.tooltip-data');
                if (tooltipData) {
                    this.showTooltip(e, tooltipData.innerHTML);
                }
            });
            
            item.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });

            item.addEventListener('mousemove', (e) => {
                this.updateTooltipPosition(e);
            });
        });
    }

    showTooltip(event, content) {
        this.hideTooltip();
        
        const tooltip = document.createElement('div');
        tooltip.className = 'status-page-tooltip-compact';
        tooltip.innerHTML = content;
        document.body.appendChild(tooltip);
        
        this._tooltip = tooltip;
        this.updateTooltipPosition(event);
        
        // Show after positioning
        setTimeout(() => {
            tooltip.classList.add('show');
        }, 10);
    }

    updateTooltipPosition(event) {
        if (!this._tooltip) return;
        
        const tooltip = this._tooltip;
        const offset = 15;
        
        // Position tooltip near cursor
        let left = event.pageX + offset;
        let top = event.pageY + offset;
        
        // Adjust if tooltip goes off-screen
        const tooltipRect = tooltip.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        if (left + tooltipRect.width > viewportWidth) {
            left = event.pageX - tooltipRect.width - offset;
        }
        
        if (top + tooltipRect.height > viewportHeight) {
            top = event.pageY - tooltipRect.height - offset;
        }
        
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }

    hideTooltip() {
        if (this._tooltip) {
            this._tooltip.remove();
            this._tooltip = null;
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

    startRefresh() {
        const refresh_rate = this._widgetForm.getFieldsValues().get('rf_rate');
        if (refresh_rate > 0) {
            this._refresh_interval = setInterval(() => this.promiseUpdate(), refresh_rate * 1000);
        }
    }

    stopRefresh() {
        if (this._refresh_interval) {
            clearInterval(this._refresh_interval);
            this._refresh_interval = null;
        }
    }
}
