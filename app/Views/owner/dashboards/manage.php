<?= $this->extend('layouts/owner') ?>
<?= $this->section('css') ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    .statistics-list { max-height: 400px; overflow-y: auto; }
    .statistic-item { display: flex; align-items: center; padding: 10px; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 8px; cursor: grab; transition: all 0.2s ease; background: white; }
    .statistic-item:hover { border-color: #198754; background: #f8f9fa; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
    .statistic-item.dragging { opacity: 0.5; cursor: grabbing; border-color: #198754; background: #e8f5e9; }
    .statistic-item:active { cursor: grabbing; }
    .statistic-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #198754 0%, #157347 100%); border-radius: 8px; color: white; font-size: 1.2rem; margin-right: 12px; flex-shrink: 0; }
    .statistic-info { flex: 1; min-width: 0; }
    .statistic-info h6 { font-size: 0.9rem; font-weight: 600; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }
    .statistic-info small { font-size: 0.75rem; }
    .dashboard-canvas { min-height: 300px; border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; background: #f8f9fa; transition: all 0.3s ease; }
    .dashboard-canvas.drag-over { border-color: #198754; background: #e8f5e9; }
    .dashboard-widget { background: white; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 15px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); }
    .dashboard-widget .widget-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f8f9fa; border-bottom: 1px solid #e9ecef; }
    .dashboard-widget .widget-header h6 { font-size: 0.9rem; font-weight: 600; margin: 0; }
    .widget-actions { display: flex; gap: 5px; }
    .widget-content { padding: 15px; }
    .statistic-widget { min-height: 100px; }
    .manual-widget { font-size: 0.9rem; line-height: 1.6; }
    .chart-container { position: relative; height: 250px; }
    .dashboard-canvas:empty::before { content: "Seret statistik dari panel kiri atau tambahkan widget manual untuk memulai"; display: block; text-align: center; color: #6c757d; padding: 40px 20px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title">
    <h1><i class="bi bi-gear me-2"></i>Kelola Dashboard: <?= esc($dashboard['dashboard_name']) ?></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('owner/dashboards'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item active"><?= esc($dashboard['dashboard_name']); ?></li>
        </ol>
    </nav>
    <div class="mt-2">
        <a href="<?= base_url('owner/dashboards/preview/' . $dashboard['id']); ?>" target="_blank" class="btn btn-sm btn-outline-success me-1">
            <i class="bi bi-eye me-1"></i>Preview
        </a>
        <a href="<?= base_url('owner/dashboards/settings/' . $dashboard['id']); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-gear me-1"></i>Pengaturan
        </a>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistik Tersedia</h5></div>
            <div class="card-body">
                <div class="mb-3"><input type="text" class="form-control" id="search-statistic" placeholder="Cari statistik..."></div>
                <div id="statistics-list" class="statistics-list">
                    <?php if (!empty($available_statistics)): ?>
                        <?php foreach ($available_statistics as $statistic): ?>
                            <div class="statistic-item draggable" data-statistic-id="<?= $statistic['id'] ?>" data-statistic-name="<?= esc($statistic['stat_name']) ?>" data-visualization-type="<?= $statistic['visualization_type'] ?>" data-metric-type="<?= $statistic['metric_type'] ?>" draggable="true">
                                <div class="statistic-icon">
                                    <?php $vizType = $statistic['visualization_type'] ?? 'table'; ?>
                                    <?php if ($vizType == 'bar_chart' || $vizType == 'chart'): ?><i class="bi bi-bar-chart"></i>
                                    <?php elseif ($vizType == 'table'): ?><i class="bi bi-table"></i>
                                    <?php elseif ($vizType == 'kpi_card' || $vizType == 'number'): ?><i class="bi bi-hash"></i>
                                    <?php elseif (in_array($vizType, ['pie_chart', 'donut_chart'])): ?><i class="bi bi-pie-chart"></i>
                                    <?php elseif (in_array($vizType, ['line_chart', 'area_chart'])): ?><i class="bi bi-graph-up"></i>
                                    <?php elseif ($vizType == 'progress_bar'): ?><i class="bi bi-bar-chart-steps"></i>
                                    <?php elseif ($vizType == 'scatter_chart'): ?><i class="bi bi-bounding-box"></i>
                                    <?php else: ?><i class="bi bi-text-left"></i><?php endif; ?>
                                </div>
                                <div class="statistic-info">
                                    <h6><?= esc($statistic['stat_name']) ?></h6>
                                    <small class="text-muted"><?= ucfirst($statistic['metric_type'] ?? 'count') ?> - <?= ucfirst(str_replace('_', ' ', $vizType)) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="bi bi-info-circle text-muted" style="font-size: 2rem;"></i><p class="text-muted mt-2">Belum ada statistik</p><a href="<?= base_url('owner/statistics/create') ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i>Buat Statistik</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-plus-square me-2"></i>Tambah Widget Manual</h6></div>
            <div class="card-body">
                <form id="add-widget-form">
                    <input type="hidden" name="widget_type" value="manual">
                    <div class="mb-3"><label for="widget_title" class="form-label">Judul Widget</label><input type="text" class="form-control" id="widget_title" name="widget_title" required></div>
                    <div class="mb-3"><label for="widget_content" class="form-label">Konten Widget</label><textarea class="form-control" id="widget_content" name="widget_content" rows="3" required></textarea></div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Tambah Widget</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-layout-text-window-reverse me-2"></i>Dashboard Canvas</h5>
                <div>
                    <a href="<?= base_url('owner/dashboards/preview/' . $dashboard['id']); ?>" target="_blank" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-eye me-1"></i>Preview
                    </a>
                    <button id="save-layout-btn" class="btn btn-success btn-sm"><i class="bi bi-check-circle me-1"></i>Simpan Layout</button>
                </div>
            </div>
            <div class="card-body">
                <div id="dashboard-canvas" class="dashboard-canvas">
                    <?php if (!empty($dashboard_widgets)): ?>
                        <?php foreach ($dashboard_widgets as $widget): ?>
                            <div class="dashboard-widget" data-widget-id="<?= $widget['id'] ?>">
                                <div class="widget-header"><h6 class="mb-0"><?= esc($widget['widget_title']) ?></h6><div class="widget-actions"><button class="btn btn-sm btn-outline-warning edit-widget" data-widget-id="<?= $widget['id'] ?>"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger remove-widget" data-widget-id="<?= $widget['id'] ?>"><i class="bi bi-trash"></i></button></div></div>
                                <div class="widget-content">
                                    <?php if (!empty($widget['statistic_config_id'])): ?>
                                        <div class="statistic-widget" data-statistic-id="<?= $widget['statistic_config_id'] ?>">
                                            <?php 
                                            $vizType = $widget['visualization_type'] ?? 'table';
                                            $statData = $widget['statistic_data'] ?? [];
                                            $statError = $widget['statistic_error'] ?? null;
                                            
                                            // Debug: Uncomment to see raw data
                                            // log_message('debug', 'Widget ' . $widget['id'] . ' stat_data: ' . json_encode($statData));
                                            ?>
                                            
                                            <?php if (!empty($statError)): ?>
                                                <div class="error-widget text-center py-4">
                                                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                                                    <p class="mt-2 mb-0 text-danger"><?= esc($statError) ?></p>
                                                </div>
                                            <?php elseif ($vizType == 'kpi_card' || $vizType == 'number'): ?>
                                                <div class="kpi-card text-center py-4">
                                                    <?php 
                                                    $value = 0;
                                                    if (is_array($statData) && !empty($statData)) {
                                                        $firstItem = is_array($statData[0] ?? null) ? $statData[0] : $statData;
                                                        $value = is_numeric($firstItem['value'] ?? null) ? (float)$firstItem['value'] : 0;
                                                    }
                                                    ?>
                                                    <h2 class="mb-0 text-success"><?= number_format($value, 0, ',', '.') ?></h2>
                                                    <small class="text-muted"><?= esc($widget['stat_name'] ?? 'Statistik') ?></small>
                                                </div>
                                            <?php elseif (in_array($vizType, ['bar_chart', 'pie_chart', 'line_chart', 'area_chart', 'donut_chart', 'scatter_chart'])): ?>
                                                <div class="chart-container" style="position: relative; height: 250px;">
                                                    <canvas data-statistic-id="<?= $widget['statistic_config_id'] ?>" data-chart-labels="<?= esc(json_encode(array_column($statData, 'label') ?? [])) ?>" data-chart-values="<?= esc(json_encode(array_map(function($v) { return is_numeric($v['value'] ?? null) ? (float)$v['value'] : 0; }, $statData ?? []))) ?>"></canvas>
                                                </div>
                                            <?php elseif ($vizType == 'table'): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead><tr><th>Label</th><th class="text-end">Nilai</th></tr></thead>
                                                        <tbody>
                                                            <?php if (!empty($statData) && is_array($statData)): ?>
                                                                <?php foreach ($statData as $row): ?>
                                                                    <tr>
                                                                        <td><?= esc($row['label'] ?? '-') ?></td>
                                                                        <td class="text-end"><?= number_format($row['value'] ?? 0, 0, ',', '.') ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <tr><td colspan="2" class="text-center py-3 text-muted">Tidak ada data</td></tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php elseif ($vizType == 'progress_bar'): ?>
                                                <?php 
                                                $value = 0;
                                                $max = 100;
                                                if (is_array($statData) && !empty($statData)) {
                                                    $firstItem = is_array($statData[0] ?? null) ? $statData[0] : $statData;
                                                    $value = is_numeric($firstItem['value'] ?? null) ? (float)$firstItem['value'] : 0;
                                                }
                                                $percentage = min(100, max(0, ($value / $max) * 100));
                                                ?>
                                                <div class="progress-label mb-2">
                                                    <span><?= esc($widget['stat_name'] ?? 'Progress') ?></span>
                                                    <span><?= number_format($value, 0, ',', '.') ?> / <?= number_format($max, 0, ',', '.') ?></span>
                                                </div>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentage ?>%;">
                                                        <?= number_format($percentage, 1) ?>%
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-3 text-muted">Tipe visualisasi tidak didukung</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="manual-widget"><?= $widget['widget_content'] ?? 'Konten widget' ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5"><i class="bi bi-layout-text-window-reverse text-muted" style="font-size: 3rem;"></i><h5 class="mt-3 text-muted">Dashboard Kosong</h5><p class="text-muted">Seret statistik dari panel kiri atau tambahkan widget manual</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editWidgetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Widget</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="edit-widget-form"><input type="hidden" id="edit-widget-id" name="widget_id"><div class="mb-3"><label for="edit-widget-title" class="form-label">Judul Widget</label><input type="text" class="form-control" id="edit-widget-title" name="widget_title" required></div><div class="mb-3"><label for="edit-widget-content" class="form-label">Konten Widget</label><textarea class="form-control" id="edit-widget-content" name="widget_content" rows="3" required></textarea></div></form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="save-widget-changes">Simpan</button></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadWidgetData();
    
    // Preview button now uses direct link (see HTML above)
    
    $('.statistic-item').on('dragstart', function(e) {
        $(this).addClass('dragging');
        e.originalEvent.dataTransfer.setData('text/plain', $(this).data('statistic-id'));
    });
    
    $('.statistic-item').on('dragend', function() {
        $(this).removeClass('dragging');
    });
    
    var canvasEl = document.getElementById('dashboard-canvas');
    if (canvasEl) {
        canvasEl.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        canvasEl.addEventListener('drop', function(e) {
            e.preventDefault();
            var statisticId = e.originalEvent.dataTransfer.getData('text/plain');
            if (statisticId) {
                addStatisticWidget(statisticId);
            }
        });
    }
    
    $('.statistic-item').on('click', function() {
        addStatisticWidget($(this).data('statistic-id'));
    });
    
    $('#search-statistic').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.statistic-item').each(function() {
            $(this).toggle($(this).data('statistic-name').toLowerCase().indexOf(searchTerm) !== -1);
        });
    });
    
    function loadWidgetData() {
        // Load charts using pre-calculated data from server (faster, no AJAX needed)
        $('.chart-container canvas').each(function() {
            var canvas = $(this);
            var labels = canvas.data('chart-labels');
            var values = canvas.data('chart-values');
            var statisticId = canvas.data('statistic-id');
            
            // Check if pre-calculated data is available
            if (labels && values && labels.length > 0 && values.length > 0) {
                console.log('[Manage] Using pre-calculated data for statistic:', statisticId);
                renderChartFromData(canvas, labels, values, 'bar');
            } else {
                // Fallback to AJAX if no pre-calculated data
                console.log('[Manage] No pre-calculated data, fetching via AJAX for:', statisticId);
                loadStatisticChart(canvas, statisticId);
            }
        });
        
        // Load tables - they should already have data rendered server-side
        $('.table-responsive').each(function() {
            // Tables are now rendered server-side, no AJAX needed
            console.log('[Manage] Table data rendered server-side');
        });
        
        // Load KPI cards - they should already have data rendered server-side
        $('.statistic-widget').each(function() {
            var widget = $(this);
            var kpiCard = widget.find('.kpi-card');
            if (kpiCard.length) {
                console.log('[Manage] KPI card rendered server-side');
            }
        });
    }
    
    // Render chart from pre-calculated data
    function renderChartFromData(canvas, labels, values, defaultType) {
        try {
            var ctx = canvas[0].getContext('2d');
            var chartType = defaultType || 'bar';
            
            console.log('[renderChartFromData] Rendering chart with', labels.length, 'labels');
            
            // Default colors
            var bgColors = [
                'rgba(25, 135, 84, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)',
                'rgba(201, 203, 207, 0.7)'
            ];
            var borderColors = bgColors.map(function(c) { return c.replace('0.7', '1'); });
            
            var backgroundColor = (chartType === 'pie' || chartType === 'doughnut') ? bgColors : (bgColors[0] || 'rgba(25, 135, 84, 0.7)');
            var borderColor = (chartType === 'pie' || chartType === 'doughnut') ? borderColors : (borderColors[0] || 'rgba(25, 135, 84, 1)');
            
            new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Data',
                        data: values,
                        backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: (chartType === 'pie' || chartType === 'doughnut'),
                            position: 'bottom'
                        }
                    },
                    scales: (chartType === 'pie' || chartType === 'doughnut') ? {} : {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            console.log('[renderChartFromData] Chart rendered successfully');
            
        } catch (e) {
            console.error('[renderChartFromData] Error:', e.message);
            showChartError(canvas.closest('.chart-container'), 'Error rendering chart');
        }
    }
    
    function loadStatisticKPI(widget, statisticId) {
        $.ajax({
            url: '<?= base_url('api/statistics/data/') ?>' + statisticId,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    var data = response.data;
                    var kpiValue = widget.find('.kpi-value');
                    
                    if (data.value !== undefined && data.value !== null) {
                        kpiValue.html('<span class="text-success">' + numberFormat(data.value) + '</span>');
                    } else if (data.values && data.values.length > 0) {
                        // Handle array format
                        kpiValue.html('<span class="text-success">' + numberFormat(data.values[0]) + '</span>');
                    } else {
                        widget.find('.kpi-placeholder').html(
                            '<div class="text-center py-4 text-muted">' +
                            '<i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>' +
                            '<p class="mt-2 mb-0">Tidak ada data</p>' +
                            '</div>'
                        );
                    }
                } else {
                    widget.find('.kpi-placeholder').html(
                        '<div class="text-center py-4 text-muted">' +
                        '<i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>' +
                        '<p class="mt-2 mb-0">' + (response.message || 'Tidak ada data') + '</p>' +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading KPI statistic:', error);
                widget.find('.kpi-placeholder').html(
                    '<div class="text-center py-4 text-danger">' +
                    '<i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>' +
                    '<p class="mt-2 mb-0">Gagal memuat data</p>' +
                    '</div>'
                );
            }
        });
    }
    
    function loadStatisticChart(canvas, statisticId) {
        $.ajax({
            url: '<?= base_url('api/statistics/data/') ?>' + statisticId,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    var data = response.data;
                    var container = canvas.closest('.chart-container');
                    var widgetContent = container.closest('.widget-content');
                    
                    // Check for KPI format first (single value)
                    if (data.value !== undefined) {
                        widgetContent.find('h2').html('<span class="text-success">' + (data.value !== null ? numberFormat(data.value) : '0') + '</span>');
                        // Also update the subtitle if exists
                        widgetContent.find('small').text(data.label || '');
                    } 
                    // Check for chart format
                    else if (data.labels && data.values && data.labels.length > 0) {
                        renderChart(canvas, data);
                    } 
                    // Fallback: try values array
                    else if (data.values && data.values.length > 0) {
                        renderChart(canvas, data);
                    } else {
                        showChartError(container, 'Tidak ada data');
                    }
                } else {
                    showChartError(canvas.closest('.chart-container'), response.message || 'Tidak ada data');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading statistic:', error);
                showChartError(canvas.closest('.chart-container'), 'Gagal memuat data');
            }
        });
    }
    
    function loadStatisticTable(container, statisticId) {
        $.ajax({
            url: '<?= base_url('api/statistics/data/') ?>' + statisticId,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    var data = response.data;
                    var tbody = container.find('tbody');
                    if (data.rows && data.rows.length > 0) {
                        var html = '';
                        data.rows.forEach(function(row) {
                            html += '<tr><td>' + (row.label || '-') + '</td><td>' + numberFormat(row.value) + '</td></tr>';
                        });
                        tbody.html(html);
                    } else if (data.labels && data.values) {
                        var html = '';
                        for (var i = 0; i < data.labels.length; i++) {
                            html += '<tr><td>' + data.labels[i] + '</td><td>' + numberFormat(data.values[i]) + '</td></tr>';
                        }
                        tbody.html(html || '<tr><td colspan="2" class="text-center">Tidak ada data</td></tr>');
                    } else {
                        tbody.html('<tr><td colspan="2" class="text-center">Tidak ada data</td></tr>');
                    }
                } else {
                    container.find('tbody').html('<tr><td colspan="2" class="text-center text-danger">' + (response.message || 'Error') + '</td></tr>');
                }
            },
            error: function() {
                container.find('tbody').html('<tr><td colspan="2" class="text-center text-danger">Gagal memuat data</td></tr>');
            }
        });
    }
    
    function renderChart(canvas, data) {
        var ctx = canvas[0].getContext('2d');
        var chartType = data.chart_type || 'bar';
        if (chartType === 'donut') chartType = 'doughnut';
        var fill = (chartType === 'line' || chartType === 'area');
        
        var bgColors = [];
        var borderColors = [];
        
        // Use colors from API response (API returns 'colors' not 'backgroundColor')
        if (data.colors && data.colors.length > 0) {
            bgColors = data.colors;
            borderColors = data.colors;
        } else if (data.backgroundColor && data.backgroundColor.length > 0) {
            bgColors = data.backgroundColor;
            borderColors = data.borderColor || data.backgroundColor;
        } else {
            // Default colors
            bgColors = [
                'rgba(25, 135, 84, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)',
                'rgba(201, 203, 207, 0.7)'
            ];
            borderColors = [
                'rgba(25, 135, 84, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(201, 203, 207, 1)'
            ];
        }
        
        // For pie/doughnut, use all colors. For others, use first color
        var backgroundColor = (chartType === 'pie' || chartType === 'doughnut') ? bgColors : (bgColors[0] || 'rgba(25, 135, 84, 0.7)');
        var borderColor = (chartType === 'pie' || chartType === 'doughnut') ? borderColors : (borderColors[0] || 'rgba(25, 135, 84, 1)');
        
        new Chart(ctx, {
            type: chartType,
            data: {
                labels: data.labels || [],
                datasets: [{
                    label: data.label || 'Data',
                    data: data.values || [],
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
                        display: (chartType === 'pie' || chartType === 'doughnut'),
                        position: 'bottom'
                    },
                    title: {
                        display: !!data.title,
                        text: data.title
                    }
                },
                scales: (chartType === 'pie' || chartType === 'doughnut') ? {} : {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    function showChartError(container, message) {
        container.html(
            '<div class="text-center py-4 text-muted">' +
            '<i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>' +
            '<p class="mt-2 mb-0">' + (message || 'Gagal memuat grafik') + '</p>' +
            '</div>'
        );
    }
    
    function numberFormat(num) {
        if (num === null || num === undefined) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    function addStatisticWidget(statisticId) {
        showLoading();
        $.ajax({
            url: '<?= base_url('owner/widgets/add') ?>',
            method: 'POST',
            data: {
                dashboard_id: <?= $dashboard['id'] ?>,
                statistic_id: statisticId,
                widget_type: 'statistic',
                sort_order: $('.dashboard-widget').length
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Statistik berhasil ditambahkan',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Terjadi kesalahan',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function() {
                hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
    
    $('#save-layout-btn').on('click', function() {
        var widgets = [];
        $('.dashboard-widget').each(function(index) {
            widgets.push({
                id: $(this).data('widget-id'),
                sort_order: index
            });
        });
        showLoading();
        $.ajax({
            url: '<?= base_url('owner/widgets/update-position') ?>',
            method: 'POST',
            data: JSON.stringify(widgets),
            contentType: 'application/json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Layout berhasil disimpan',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Terjadi kesalahan',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function() {
                hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
    
    $('#add-widget-form').on('submit', function(e) {
        e.preventDefault();
        var widgetData = {
            dashboard_id: <?= $dashboard['id'] ?>,
            widget_type: 'manual',
            widget_title: $('#widget_title').val(),
            widget_content: $('#widget_content').val(),
            sort_order: $('.dashboard-widget').length
        };
        showLoading();
        $.ajax({
            url: '<?= base_url('owner/widgets/add') ?>',
            method: 'POST',
            data: widgetData,
            success: function(response) {
                hideLoading();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Terjadi kesalahan',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function() {
                hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
    
    $(document).on('click', '.remove-widget', function() {
        var widgetId = $(this).data('widget-id');
        var widgetElement = $(this).closest('.dashboard-widget');
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Hapus widget ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                showLoading();
                $.ajax({
                    url: '<?= base_url('owner/widgets/delete/') ?>' + widgetId,
                    method: 'POST',
                    success: function(response) {
                        hideLoading();
                        if (response.success) {
                            widgetElement.fadeOut('slow', function() {
                                $(this).remove();
                                if ($('.dashboard-widget').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        hideLoading();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
    
    $(document).on('click', '.edit-widget', function() {
        var widgetId = $(this).data('widget-id');
        var widgetElement = $(this).closest('.dashboard-widget');
        $('#edit-widget-id').val(widgetId);
        $('#edit-widget-title').val(widgetElement.find('.widget-header h6').text());
        $('#editWidgetModal').modal('show');
    });
    
    $('#save-widget-changes').on('click', function() {
        var widgetId = $('#edit-widget-id').val();
        var widgetTitle = $('#edit-widget-title').val();
        var widgetElement = $('.dashboard-widget[data-widget-id="' + widgetId + '"]');
        showLoading();
        $.ajax({
            url: '<?= base_url('owner/widgets/update/') ?>' + widgetId,
            method: 'POST',
            data: {
                widget_title: widgetTitle,
                widget_content: $('#edit-widget-content').val()
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    widgetElement.find('.widget-header h6').text(widgetTitle);
                    $('#editWidgetModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Widget berhasil diupdate',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function() {
                hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>

    