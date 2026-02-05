document.addEventListener('DOMContentLoaded', function () {

    // Tooltip using title (simple)
    document.querySelectorAll('.status-dot').forEach(dot => {
        const data = JSON.parse(dot.getAttribute('data-tooltip'));
        dot.title = `${data.name}\nTotal: ${data.total}\n` +
            `Disaster: ${data.severity[5] || 0}\n` +
            `High: ${data.severity[4] || 0}\n` +
            `Warning: ${data.severity[3] || 0}\n` +
            `Info: ${data.severity[1] || 0}`;
    });

    // Filter checkbox
    document.getElementById('filterAlerts').addEventListener('change', function () {
        const showOnly = this.checked;
        document.querySelectorAll('.status-dot').forEach(dot => {
            const hasAlert = dot.getAttribute('data-has-alert') === "1";
            dot.style.display = (showOnly && !hasAlert) ? 'none' : 'flex';
        });
    });

    // Size selector
    document.getElementById('dotSize').addEventListener('change', function () {
        const size = this.value;
        document.querySelectorAll('.status-dot').forEach(dot => {
            dot.classList.remove('tiny', 'small', 'medium', 'large');
            dot.classList.add(size);
        });
    });

});
