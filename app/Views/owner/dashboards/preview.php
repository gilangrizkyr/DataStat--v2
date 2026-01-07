<?php echo $this->extend('layouts/preview'); ?>
<?php echo $this->section('css'); ?>
<style>
    /* Digital Clock Styles */
    .preview-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }
    
    .preview-toolbar > .refresh-timer {
        flex-shrink: 0;
    }
    
    .preview-toolbar > .digital-clock {
        flex: 1;
        justify-content: center;
    }
    
    .preview-toolbar > div:last-child {
        flex-shrink: 0;
    }
    
    .digital-clock {
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
        font-family: 'Courier New', monospace;
    }
    
    .digital-clock .clock-icon {
        font-size: 1.3rem;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    
    .digital-clock .time-display {
        display: flex;
        flex-direction: column;
    }
    
    .digital-clock .time-main {
        font-size: 1.5rem;
        font-weight: bold;
        letter-spacing: 2px;
        text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
    }
    
    .digital-clock .date-display {
        font-size: 0.7rem;
        opacity: 0.8;
    }
    
    /* Refresh Timer */
    .refresh-timer {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: white;
        font-size: 0.8rem;
    }
    
    .timer-circle {
        width: 28px;
        height: 28px;
        position: relative;
    }
    
    .timer-circle svg {
        transform: rotate(-90deg);
    }
    
    .timer-circle circle {
        fill: none;
        stroke-width: 3;
    }
    
    .timer-bg {
        stroke: rgba(255, 255, 255, 0.2);
    }
    
    .timer-progress {
        stroke: #00ff88;
        stroke-dasharray: 81;
        stroke-dashoffset: 0;
        transition: stroke-dashoffset 1s linear;
    }
    
    .refresh-text {
        min-width: 32px;
        text-align: center;
    }
    
    /* Toolbar Buttons */
    .toolbar-btn {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .toolbar-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.05);
    }
    
    .toolbar-btn.refresh {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    }
    
    .toolbar-btn.fullscreen {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
    }
    
    .toolbar-btn.close {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    /* Loading Overlay */
    .refresh-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .refresh-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .refresh-overlay .spinner-content {
        background: white;
        padding: 25px 40px;
        border-radius: 12px;
        text-align: center;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .spin {
        animation: spin 1s linear infinite;
    }
    
    /* Original Styles */
    .dashboard-preview { padding: 20px; }
    .dashboard-header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef; }
    .dashboard-header h2 { font-size: 1.5rem; font-weight: 600; color: #198754; margin-bottom: 5px; }
    .dashboard-header .dashboard-description { color: #6c757d; font-size: 0.9rem; }
    .dashboard-header .dashboard-meta { font-size: 0.8rem; color: #adb5bd; margin-top: 8px; }
    .preview-widget { background: white; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 15px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); }
    .preview-widget .widget-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f8f9fa; border-bottom: 1px solid #e9ecef; }
    .preview-widget .widget-header h6 { font-size: 0.9rem; font-weight: 600; margin: 0; }
    .preview-widget .widget-content { padding: 15px; min-height: 100px; }
    .statistic-widget { min-height: 100px; }
    .kpi-card { text-align: center; padding: 20px; }
    .kpi-card .kpi-value { font-size: 2.5rem; font-weight: 700; color: #198754; line-height: 1.2; }
    .kpi-card .kpi-label { font-size: 0.9rem; color: #6c757d; margin-top: 5px; }
    .kpi-card .kpi-change { font-size: 0.8rem; margin-top: 8px; }
    .kpi-card .kpi-change.positive { color: #198754; }
    .kpi-card .kpi-change.negative { color: #dc3545; }
    .table-responsive { max-height: 300px; overflow-y: auto; }
    .table-widget { font-size: 0.9rem; }
    .table-widget th { background: #f8f9fa; font-weight: 600; position: sticky; top: 0; }
    .chart-container { position: relative; height: 300px; }
    .progress-widget { padding: 10px 0; }
    .progress-label { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9rem; }
    .empty-widget { text-align: center; padding: 40px 20px; color: #6c757d; }
    .empty-widget i { font-size: 2rem; margin-bottom: 10px; }
    .error-widget { text-align: center; padding: 30px 20px; color: #dc3545; }
    .error-widget i { font-size: 2rem; margin-bottom: 10px; }
    .widget-actions { display: flex; gap: 5px; }
    .widget-actions .btn { padding: 4px 8px; font-size: 0.8rem; }
    @media (max-width: 768px) {
        .dashboard-preview { padding: 10px; }
        .kpi-card .kpi-value { font-size: 2rem; }
        .chart-container { height: 250px; }
    }
</style>
<?php echo $this->endSection(); ?>

<?php echo $this->section('content'); ?>

<!-- Toolbar dengan Jam Digital, Timer, dan Tombol -->
<div class="preview-toolbar">
    <!-- Timer Refresh (kiri) -->
    <div class="refresh-timer">
        <div class="timer-circle">
            <svg width="28" height="28">
                <circle class="timer-bg" cx="14" cy="14" r="12"></circle>
                <circle class="timer-progress" id="timerProgress" cx="14" cy="14" r="12"></circle>
            </svg>
        </div>
        <span class="refresh-text" id="refreshCountdown">60s</span>
    </div>
    
    <!-- Digital Clock (center) -->
    <div class="digital-clock">
        <div class="clock-icon">
            <i class="bi bi-clock-fill"></i>
        </div>
        <div class="time-display">
            <div class="time-main" id="timeDisplay">00:00:00</div>
            <div class="date-display" id="dateDisplay">Sen, 1 Jan 2024</div>
        </div>
    </div>
    
    <!-- Tombol (kanan) -->
    <div style="display: flex; gap: 10px;">
        <!-- Tombol Refresh Manual -->
        <button class="toolbar-btn refresh" onclick="manualRefresh()" title="Refresh Sekarang">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
        
        <!-- Tombol Fullscreen -->
        <button class="toolbar-btn fullscreen" id="fullscreenBtn" onclick="toggleFullscreen()" title="Fullscreen (Tekan F)">
            <i class="bi bi-fullscreen"></i>
        </button>
    </div>
</div>

<!-- Loading Overlay -->
<div class="refresh-overlay" id="refreshOverlay">
    <div class="spinner-content">
        <i class="bi bi-arrow-clockwise spin" style="font-size: 2rem; color: #198754;"></i>
        <p class="mb-0 mt-2 fw-bold">Memuat data...</p>
    </div>
</div>

<div class="dashboard-preview">
    <div class="dashboard-header">
        <h2><i class="bi bi-layout-text-window-reverse me-2"></i><?php echo esc($dashboard['dashboard_name']); ?></h2>
        <?php if (!empty($dashboard['dashboard_description'])): ?>
            <p class="dashboard-description"><?php echo esc($dashboard['dashboard_description']); ?></p>
        <?php endif; ?>
        <div class="dashboard-meta">
            <i class="bi bi-calendar me-1"></i>
            Dibuat: <?php echo date('d M Y H:i', strtotime($dashboard['created_at'])); ?>
            <?php if (!empty($dashboard['updated_at'])): ?>
                · Diupdate: <?php echo date('d M Y H:i', strtotime($dashboard['updated_at'])); ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($widgets)): ?>
        <div class="row">
            <?php foreach ($widgets as $widget): ?>
                <div class="col-md-<?php echo $widget['width'] ?? 12; ?>">
                    <div class="preview-widget">
                        <div class="widget-header">
                            <h6><?php echo esc($widget['widget_title']); ?></h6>
                        </div>
                        <div class="widget-content">
                            <?php if (!empty($widget['statistic_config_id'])): ?>
                                <div class="statistic-widget">
                                    <?php
                                    $vizType = $widget['visualization_type'] ?? 'table';
                                    $statData = $widget['statistic_data'] ?? [];
                                    $statError = $widget['statistic_error'] ?? null;
                                    
                                    // Prepare chart data for embedded rendering
                                    $chartLabels = [];
                                    $chartValues = [];
                                    if (!empty($statData) && is_array($statData)) {
                                        $chartLabels = array_column($statData, 'label');
                                        $chartValues = array_map(function($v) { 
                                            return is_numeric($v['value'] ?? null) ? (float)$v['value'] : 0; 
                                        }, $statData);
                                    }
                                    
                                    // Encode for JavaScript
                                    $encodedLabels = json_encode($chartLabels);
                                    $encodedValues = json_encode($chartValues);
                                    
                                    if (!empty($statError)): ?>
                                        <div class="error-widget">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <p><?php echo esc($statError); ?></p>
                                        </div>
                                    <?php elseif ($vizType == 'kpi_card' || $vizType == 'number'): ?>
                                        <div class="kpi-card">
                                            <?php 
                                            $value = 0;
                                            if (is_array($statData) && !empty($statData)) {
                                                $firstItem = is_array($statData[0] ?? null) ? $statData[0] : $statData;
                                                $value = is_numeric($firstItem['value'] ?? null) ? (float)$firstItem['value'] : 0;
                                            }
                                            $label = $statData['label'] ?? 'Total';
                                            $change = $statData['change'] ?? null;
                                            ?>
                                            <div class="kpi-value"><?php echo number_format($value, 0, ',', '.'); ?></div>
                                            <div class="kpi-label"><?php echo esc($label); ?></div>
                                            <?php if ($change !== null): ?>
                                                <div class="kpi-change <?php echo $change >= 0 ? 'positive' : 'negative'; ?>">
                                                    <i class="bi <?php echo $change >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'; ?>"></i>
                                                    <?php echo abs(number_format($change, 1)); ?>% dari sebelumnya
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif (in_array($vizType, ['bar_chart', 'pie_chart', 'line_chart', 'area_chart', 'donut_chart', 'scatter_chart'])): ?>
                                        <div class="chart-container">
                                            <canvas 
                                                class="statistic-chart-canvas"
                                                data-statistic-id="<?php echo $widget['statistic_config_id']; ?>"
                                                data-chart-labels='<?php echo $encodedLabels; ?>'
                                                data-chart-values='<?php echo $encodedValues; ?>'
                                                data-viz-type="<?php echo $vizType; ?>"
                                            ></canvas>
                                        </div>
                                    <?php elseif ($vizType == 'table'): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-widget">
                                                <thead>
                                                    <tr>
                                                        <th>Label</th>
                                                        <th class="text-end">Nilai</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($statData) && is_array($statData)): ?>
                                                        <?php foreach ($statData as $row): ?>
                                                            <tr>
                                                                <td><?php echo esc($row['label'] ?? '-'); ?></td>
                                                                <td class="text-end"><?php echo number_format($row['value'] ?? 0, 0, ',', '.'); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center py-3">
                                                                <i class="bi bi-hourglass-split fa-spin"></i> Memuat data...
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php elseif ($vizType == 'progress_bar'): ?>
                                        <div class="progress-widget">
                                            <?php 
                                            $value = 0;
                                            $max = 100;
                                            if (is_array($statData) && !empty($statData)) {
                                                $firstItem = is_array($statData[0] ?? null) ? $statData[0] : $statData;
                                                $value = is_numeric($firstItem['value'] ?? null) ? (float)$firstItem['value'] : 0;
                                            }
                                            $label = $statData['label'] ?? 'Progress';
                                            $percentage = min(100, max(0, ($value / $max) * 100));
                                            ?>
                                            <div class="progress-label">
                                                <span><?php echo esc($label); ?></span>
                                                <span><?php echo number_format($value, 0, ',', '.'); ?> / <?php echo number_format($max, 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage; ?>%;">
                                                    <?php echo number_format($percentage, 1); ?>%
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-widget">
                                            <i class="bi bi-info-circle"></i>
                                            <p>Tipe visualisasi "<?php echo esc($vizType); ?>" tidak didukung untuk preview</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="manual-widget">
                                    <?php echo $widget['widget_content'] ?? 'Konten widget'; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-layout-text-window-reverse text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">Dashboard Kosong</h4>
            <p class="text-muted">Belum ada widget di dashboard ini</p>
            <a href="<?php echo base_url('owner/dashboards/manage/' . $dashboard['id']); ?>" class="btn btn-primary">
                <i class="bi bi-gear me-2"></i>Kelola Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<?php echo $this->section('js'); ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    var apiBaseUrl = '<?php echo base_url('api/statistics/data/'); ?>';
    
    // Get CSRF token
    function getCsrfToken() {
        var token = $('meta[name="csrf-token"]').attr('content');
        return token || '';
    }
    
    // Process each chart canvas
    $('.statistic-chart-canvas').each(function() {
        var canvas = $(this);
        var statisticId = canvas.data('statistic-id');
        
        if (!statisticId) {
            showChartError(canvas, 'Konfigurasi widget tidak lengkap');
            return;
        }
        
        // Check for embedded data first (faster, no AJAX needed)
        var embeddedLabels = canvas.data('chart-labels');
        var embeddedValues = canvas.data('chart-values');
        
        if (embeddedLabels && embeddedValues && embeddedLabels.length > 0 && embeddedValues.length > 0) {
            // Use embedded data directly
            var embeddedData = {
                labels: embeddedLabels,
                values: embeddedValues,
                chart_type: canvas.data('viz-type') || 'bar'
            };
            renderChart(canvas, embeddedData);
        } else {
            // Fallback to AJAX if no embedded data
            var apiUrl = apiBaseUrl + statisticId;
            
            $.ajax({
                url: apiUrl,
                method: 'GET',
                dataType: 'json',
                timeout: 30000,
                beforeSend: function(xhr) {
                    var csrfToken = getCsrfToken();
                    if (csrfToken) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                    }
                },
                success: function(response) {
                    if (response.success && response.data) {
                        renderChart(canvas, response.data);
                    } else {
                        showChartError(canvas, response.message || 'Gagal memuat data');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'Error: ' + error;
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'API endpoint tidak ditemukan';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error: ' + (xhr.responseJSON?.message || 'Unknown error');
                    }
                    showChartError(canvas, errorMessage);
                }
            });
        }
    });
});

function renderChart(canvas, data) {
    try {
        var ctx = canvas[0].getContext('2d');
        var chartType = data.chart_type || 'bar';
        
        // Convert donut to doughnut untuk Chart.js
        if (chartType === 'donut') chartType = 'doughnut';
        
        // Tentukan fill untuk line/area chart
        var fill = (chartType === 'line' || chartType === 'area');
        
        // Validasi data
        if (!data.labels || !Array.isArray(data.labels) || data.labels.length === 0) {
            showChartError(canvas, 'Data chart kosong');
            return;
        }
        
        if (!data.values || !Array.isArray(data.values) || data.values.length === 0) {
            showChartError(canvas, 'Nilai chart kosong');
            return;
        }
        
        // Generate colors
        var bgColors = [];
        var borderColors = [];
        
        // Use colors from API response
        if (data.colors && data.colors.length > 0 && Array.isArray(data.colors)) {
            bgColors = data.colors;
            borderColors = data.colors;
        } else if (data.backgroundColor && data.backgroundColor.length > 0) {
            bgColors = data.backgroundColor;
            borderColors = data.borderColor || data.backgroundColor;
        } else {
            // Default colors
            bgColors = [
                'rgba(25, 135, 84, 0.7)',  // Green
                'rgba(54, 162, 235, 0.7)', // Blue
                'rgba(255, 206, 86, 0.7)', // Yellow
                'rgba(255, 99, 132, 0.7)', // Red
                'rgba(75, 192, 192, 0.7)', // Cyan
                'rgba(153, 102, 255, 0.7)', // Purple
                'rgba(255, 159, 64, 0.7)',  // Orange
                'rgba(201, 203, 207, 0.7)'  // Gray
            ];
            borderColors = bgColors.map(function(c) { return c.replace('0.7', '1'); });
        }
        
        // Untuk pie/doughnut, gunakan semua warna. Untuk yang lain, warna pertama
        var backgroundColor = (chartType === 'pie' || chartType === 'doughnut') ? bgColors : (bgColors[0] || 'rgba(25, 135, 84, 0.7)');
        var borderColor = (chartType === 'pie' || chartType === 'doughnut') ? borderColors : (borderColors[0] || 'rgba(25, 135, 84, 1)');
        
        // Buat chart
        var chartConfig = {
            type: chartType,
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.label || 'Data',
                    data: data.values,
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    borderWidth: 2,
                    fill: fill,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: chartType === 'pie' || chartType === 'doughnut',
                        position: 'bottom'
                    },
                    title: {
                        display: !!data.title,
                        text: data.title
                    }
                },
                scales: chartType === 'pie' || chartType === 'doughnut' ? {} : {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };
        
        var chart = new Chart(ctx, chartConfig);
        
    } catch (e) {
        showChartError(canvas, 'Error rendering chart: ' + e.message);
    }
}

function showChartError(canvas, message) {
    var container = canvas.closest('.chart-container');
    message = message || 'Gagal memuat grafik';
    container.html('<div class="error-widget"><i class="bi bi-exclamation-triangle"></i><p class="mb-0">' + message + '</p></div>');
}

// ==================== Digital Clock & Timer ====================
const REFRESH_INTERVAL = 60; // seconds
let refreshCountdown = REFRESH_INTERVAL;
const CIRCUMFERENCE = 2 * Math.PI * 12; // radius = 12

$(document).ready(function() {
    initDigitalClock();
    initRefreshTimer();
    initKeyboardShortcuts();
});

function initDigitalClock() {
    updateDigitalClock();
    setInterval(updateDigitalClock, 1000);
}

function updateDigitalClock() {
    const now = new Date();
    
    // Time
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('timeDisplay').textContent = hours + ':' + minutes + ':' + seconds;
    
    // Date dalam bahasa Indonesia
    const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
    const dateStr = now.toLocaleDateString('id-ID', options);
    document.getElementById('dateDisplay').textContent = dateStr;
}

function initRefreshTimer() {
    updateTimerDisplay();
    
    // Countdown setiap detik
    setInterval(function() {
        refreshCountdown--;
        updateTimerDisplay();
        
        if (refreshCountdown <= 0) {
            autoRefresh();
        }
    }, 1000);
}

function updateTimerDisplay() {
    document.getElementById('refreshCountdown').textContent = refreshCountdown + 's';
    
    // Update circle progress
    const timerProgress = document.getElementById('timerProgress');
    const offset = CIRCUMFERENCE - (refreshCountdown / REFRESH_INTERVAL) * CIRCUMFERENCE;
    timerProgress.style.strokeDashoffset = offset;
}

function autoRefresh() {
    showLoading();
    window.location.reload();
}

function manualRefresh() {
    refreshCountdown = REFRESH_INTERVAL;
    autoRefresh();
}

function showLoading() {
    document.getElementById('refreshOverlay').classList.add('active');
}

function hideLoading() {
    document.getElementById('refreshOverlay').classList.remove('active');
}

function initKeyboardShortcuts() {
    // Keyboard shortcut for fullscreen (F key)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'f' || e.key === 'F') {
            if (!$(e.target).is('input, textarea')) {
                toggleFullscreen();
            }
        }
        // ESC untuk keluar fullscreen
        if (e.key === 'Escape') {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
        }
    });
}

window.toggleFullscreen = function() {
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    
    if (!document.fullscreenElement) {
        // Enter fullscreen
        document.documentElement.requestFullscreen().then(function() {
            fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
            fullscreenBtn.title = 'Exit Fullscreen (ESC)';
        }).catch(function(err) {
            console.log('Fullscreen error:', err);
        });
    } else {
        // Exit fullscreen
        document.exitFullscreen().then(function() {
            fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen"></i>';
            fullscreenBtn.title = 'Fullscreen (Tekan F)';
        });
    }
};

// Handle fullscreen change event
document.addEventListener('fullscreenchange', function() {
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    if (!document.fullscreenElement && fullscreenBtn) {
        fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen"></i>';
        fullscreenBtn.title = 'Fullscreen (Tekan F)';
    }
});
</script>
<?php echo $this->endSection(); ?>

<?= $this->endSection() ?>