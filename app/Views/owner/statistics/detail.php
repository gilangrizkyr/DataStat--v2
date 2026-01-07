<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        <?= esc($title) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/statistics') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="<?= base_url('owner/statistics/builder/' . $statistic['id']) ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Konfigurasi
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-ban"></i> <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Info Statistik -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle"></i> Informasi Statistik
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Nama Statistik</strong></td>
                                            <td><?= esc($statistic['stat_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dataset</strong></td>
                                            <td><?= esc($statistic['dataset_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipe Metrik</strong></td>
                                            <td><?= esc(ucfirst(str_replace('_', ' ', $statistic['metric_type']))) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipe Visualisasi</strong></td>
                                            <td><?= esc(ucfirst(str_replace('_', ' ', $statistic['visualization_type']))) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Target Field</strong></td>
                                            <td><?= esc($statistic['target_field'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td>
                                                <?php if ($statistic['is_active'] == 1): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dibuat</strong></td>
                                            <td><?= esc(date('d M Y H:i', strtotime($statistic['created_at']))) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-sliders-h"></i> Konfigurasi
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($statistic['description'])): ?>
                                        <div class="mb-3">
                                            <strong>Deskripsi:</strong><br>
                                            <?= esc($statistic['description']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($statistic['group_by_fields'])): ?>
                                        <?php $groupBy = is_string($statistic['group_by_fields']) ? json_decode($statistic['group_by_fields'], true) : $statistic['group_by_fields']; ?>
                                        <?php if (!empty($groupBy)): ?>
                                            <div class="mb-2">
                                                <strong>Group By:</strong> <?= esc(implode(', ', $groupBy)) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($statistic['filters'])): ?>
                                        <?php $filters = is_string($statistic['filters']) ? json_decode($statistic['filters'], true) : $statistic['filters']; ?>
                                        <?php if (!empty($filters)): ?>
                                            <div class="mb-2">
                                                <strong>Filter:</strong>
                                                <pre class="bg-light p-2 rounded mt-1"><?= esc(json_encode($filters, JSON_PRETTY_PRINT)) ?></pre>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($statistic['calculation_config'])): ?>
                                        <?php $calcConfig = is_string($statistic['calculation_config']) ? json_decode($statistic['calculation_config'], true) : $statistic['calculation_config']; ?>
                                        <?php if (!empty($calcConfig)): ?>
                                            <div class="mb-2">
                                                <strong>Konfigurasi Perhitungan:</strong>
                                                <pre class="bg-light p-2 rounded mt-1"><?= esc(json_encode($calcConfig, JSON_PRETTY_PRINT)) ?></pre>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($statistic['visualization_config'])): ?>
                                        <?php $vizConfig = is_string($statistic['visualization_config']) ? json_decode($statistic['visualization_config'], true) : $statistic['visualization_config']; ?>
                                        <?php if (!empty($vizConfig)): ?>
                                            <div class="mb-2">
                                                <strong>Visualisasi Config:</strong>
                                                <pre class="bg-light p-2 rounded mt-1"><?= esc(json_encode($vizConfig, JSON_PRETTY_PRINT)) ?></pre>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (empty($statistic['description']) && empty($statistic['group_by_fields']) && empty($statistic['filters']) && empty($statistic['calculation_config']) && empty($statistic['visualization_config'])): ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <p class="mb-0">Tidak ada konfigurasi tambahan.</p>
                                            <small>Statistik ini menggunakan pengaturan dasar.</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hasil Perhitungan -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calculator"></i> Hasil Perhitungan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($error) && !empty($error)): ?>
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Error:</strong> <?= esc($error) ?>
                                            <hr>
                                            <small class="text-muted">Silakan periksa konfigurasi statistik atau hubungi administrator.</small>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!isset($statistic) || empty($statistic)): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Data statistik tidak ditemukan.
                                        </div>
                                    <?php else: ?>
                                        <?php
                                        // Prepare data for visualization
                                        $resultData = $result['data'] ?? [];
                                        $resultMetadata = $result['metadata'] ?? [];
                                        $chartType = $statistic['visualization_type'] ?? 'table';

                                        // Transform data for charts if needed
                                        $chartLabels = [];
                                        $chartValues = [];
                                        if (!empty($resultData) && is_array($resultData)) {
                                            foreach ($resultData as $item) {
                                                if (is_array($item)) {
                                                    $chartLabels[] = $item['label'] ?? ($item['name'] ?? 'Unknown');
                                                    $chartValues[] = $item['value'] ?? 0;
                                                }
                                            }
                                        }
                                        ?>

                                        <?php if (!empty($resultData) && is_array($resultData)): ?>
                                            <!-- Render hasil berdasarkan tipe visualisasi -->
                                            <?php if ($chartType === 'table'): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <?php
                                                                // Get headers from first row
                                                                $headers = !empty($resultData) ? array_keys($resultData[0]) : ['Label', 'Value'];
                                                                foreach ($headers as $header): ?>
                                                                    <th><?= esc(ucfirst(str_replace('_', ' ', $header))) ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($resultData as $row): ?>
                                                                <tr>
                                                                    <?php foreach ($row as $cell): ?>
                                                                        <td><?= esc(is_array($cell) ? json_encode($cell) : (is_numeric($cell) ? number_format($cell, 2) : $cell)) ?></td>
                                                                    <?php endforeach; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>

                                            <?php elseif (in_array($chartType, ['bar_chart', 'pie_chart', 'line_chart', 'area_chart', 'donut_chart', 'scatter_chart'])): ?>
                                                <?php 
                                                $vizConfig = is_string($statistic['visualization_config'] ?? '') ? json_decode($statistic['visualization_config'], true) : ($statistic['visualization_config'] ?? []);
                                                $chartColors = $vizConfig['colors'] ?? ['#198754', '#36A2EB', '#FFCE56', '#FF6384', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF'];
                                                ?>
                                                <?php if (!empty($chartLabels) && !empty($chartValues)): ?>
                                                    <div class="chart-container" style="position: relative; height: 400px;">
                                                        <canvas id="resultChart"></canvas>
                                                    </div>
                                                    <input type="hidden" id="chartLabels" value="<?= esc(json_encode($chartLabels)) ?>">
                                                    <input type="hidden" id="chartValues" value="<?= esc(json_encode($chartValues)) ?>">
                                                    <input type="hidden" id="chartType" value="<?= esc($chartType) ?>">
                                                    <input type="hidden" id="chartTitle" value="<?= esc($vizConfig['chart_title'] ?? $statistic['stat_name']) ?>">
                                                    <input type="hidden" id="chartColors" value="<?= esc(json_encode($chartColors)) ?>">
                                                <?php else: ?>
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Data untuk chart tidak tersedia atau kosong.
                                                    </div>
                                                <?php endif; ?>

                                            <?php elseif ($chartType === 'kpi_card'): ?>
                                                <?php
                                                // Get first value for KPI
                                                $kpiValue = 0;
                                                if (!empty($resultData[0]['value'])) {
                                                    $kpiValue = $resultData[0]['value'];
                                                } elseif (is_numeric($resultData[0] ?? null)) {
                                                    $kpiValue = $resultData[0];
                                                }
                                                ?>
                                                <div class="text-center py-4">
                                                    <h1 class="display-2 text-success"><?= esc(number_format($kpiValue, 2)) ?></h1>
                                                    <p class="text-muted"><?= esc($resultData[0]['label'] ?? $statistic['stat_name']) ?></p>
                                                </div>

                                            <?php elseif ($chartType === 'progress_bar'): ?>
                                                <?php
                                                $value = 0;
                                                if (!empty($resultData[0]['value'])) {
                                                    $value = floatval($resultData[0]['value']);
                                                }
                                                ?>
                                                <div class="progress" style="height: 30px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, max(0, $value)) ?>%;">
                                                        <?= esc(number_format($value, 2)) ?>%
                                                    </div>
                                                </div>
                                                <p class="text-center mt-2"><?= esc($resultData[0]['label'] ?? $statistic['stat_name']) ?></p>

                                            <?php else: ?>
                                                <!-- Default: Display as table -->
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <?php
                                                                $headers = !empty($resultData) ? array_keys($resultData[0]) : ['Data'];
                                                                foreach ($headers as $header): ?>
                                                                    <th><?= esc(ucfirst(str_replace('_', ' ', $header))) ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($resultData as $row): ?>
                                                                <tr>
                                                                    <?php foreach ($row as $cell): ?>
                                                                        <td><?= esc(is_array($cell) ? json_encode($cell) : (is_numeric($cell) ? number_format($cell, 2) : $cell)) ?></td>
                                                                    <?php endforeach; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Metadata -->
                                            <?php if (!empty($resultMetadata)): ?>
                                                <div class="mt-3 text-muted small">
                                                    <strong>Informasi Perhitungan:</strong>
                                                    <ul class="mb-0">
                                                        <?php if (isset($resultMetadata['total_rows'])): ?>
                                                            <li>Total Data: <?= esc(number_format($resultMetadata['total_rows'])) ?></li>
                                                        <?php endif; ?>
                                                        <?php if (isset($resultMetadata['metric_type'])): ?>
                                                            <li>Tipe Metrik: <?= esc(ucfirst($resultMetadata['metric_type'])) ?></li>
                                                        <?php endif; ?>
                                                        <?php if (isset($resultMetadata['calculated_at'])): ?>
                                                            <li>Dihitung pada: <?= esc(date('d M Y H:i:s', strtotime($resultMetadata['calculated_at']))) ?></li>
                                                        <?php endif; ?>
                                                        <?php if (isset($resultMetadata['dataset_name'])): ?>
                                                            <li>Dataset: <?= esc($resultMetadata['dataset_name']) ?></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <div class="text-center py-5">
                                                <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                                                <h4 class="text-muted">Data tidak tersedia</h4>
                                                <p class="text-muted">Tidak ada hasil perhitungan untuk statistik ini.</p>
                                                <p class="text-muted small">Pastikan dataset sudah diupload dan statistik sudah dikonfigurasi dengan benar.</p>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-info" id="recalculateBtn">
                                        <i class="fas fa-sync-alt"></i> Hitung Ulang
                                    </button>
                                    <a href="<?= base_url('owner/statistics/export/' . $statistic['id']) ?>" class="btn btn-success">
                                        <i class="fas fa-download"></i> Export
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Modal Loading -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2" id="loadingText">Memproses...</p>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Render chart if data exists
        const chartLabels = $('#chartLabels').val();
        const chartValues = $('#chartValues').val();
        const chartType = $('#chartType').val();
        const chartTitle = $('#chartTitle').val();
        const chartColorsInput = $('#chartColors').val();

        if (chartLabels && chartValues && chartType && !['table', 'kpi_card', 'progress_bar'].includes(chartType)) {
            try {
                const labels = JSON.parse(chartLabels);
                const values = JSON.parse(chartValues);
                let bgColors, borderColors;
                
                // Parse colors from visualization_config or use defaults
                if (chartColorsInput) {
                    const colors = JSON.parse(chartColorsInput);
                    bgColors = colors.map(c => c + 'CC'); // Add transparency
                    borderColors = colors;
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

                if (labels.length > 0 && values.length > 0) {
                    const ctx = document.getElementById('resultChart');
                    if (ctx) {
                        let chartJsType = chartType.replace('_chart', '');
                        if (chartJsType === 'donut') {
                            chartJsType = 'doughnut';
                        }
                        
                        new Chart(ctx, {
                            type: chartJsType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: chartTitle,
                                    data: values,
                                    backgroundColor: bgColors,
                                    borderColor: borderColors,
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom'
                                    },
                                    title: {
                                        display: !!chartTitle,
                                        text: chartTitle
                                    }
                                },
                                scales: chartJsType === 'pie' || chartJsType === 'doughnut' ? {} : {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                }
            } catch (e) {
                console.log('Error rendering chart:', e);
            }
        }

        // Recalculate button
        $('#recalculateBtn').on('click', function() {
            $('#loadingModal').modal('show');

            $.ajax({
                url: `<?= base_url('owner/statistics/recalculate/' . $statistic['id']) ?>`,
                method: 'POST',
                success: function(response) {
                    $('#loadingModal').modal('hide');

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Statistik berhasil dihitung ulang',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Terjadi kesalahan saat menghitung ulang',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    $('#loadingModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menghitung ulang',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });
</script>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<?= $this->endSection() ?>
