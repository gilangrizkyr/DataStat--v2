<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs mr-2"></i>
                        Statistic Builder: <?= esc($statistic['stat_name']) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/statistics/detail/' . $statistic['id']) ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                        <a href="<?= base_url('owner/statistics') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
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

                    <form id="statisticBuilderForm">
                        <?= csrf_field() ?>

                        <!-- Basic Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle"></i> Informasi Dasar
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="stat_name">Nama Statistik</label>
                                            <input type="text" class="form-control" id="stat_name" name="stat_name" value="<?= esc($statistic['stat_name']) ?>" placeholder="Masukkan nama statistik" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Dataset</label>
                                            <input type="text" class="form-control" value="<?= esc($statistic['dataset_name']) ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipe Metrik</label>
                                            <input type="text" class="form-control" value="<?= ucfirst(str_replace('_', ' ', $statistic['metric_type'])) ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipe Visualisasi</label>
                                            <input type="text" class="form-control" value="<?= ucfirst(str_replace('_', ' ', $statistic['visualization_type'])) ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-filter"></i> Konfigurasi Filter
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="filters">Filter Data</label>
                                            <textarea class="form-control" id="filters" name="filters" rows="4" placeholder="Masukkan filter dalam format JSON atau kosongkan untuk semua data"><?= old('filters', $config['filters'] ?? '') ?></textarea>
                                            <small class="form-text text-muted">
                                                Contoh: {"column_name": "value"} atau {"column_name": {"operator": ">", "value": 100}}
                                            </small>
                                        </div>
                                        <div class="form-group">
                                            <label for="group_by">Group By</label>
                                            <input type="text" class="form-control" id="group_by" name="group_by" value="<?= old('group_by', $config['group_by'] ?? '') ?>" placeholder="Nama kolom untuk grouping">
                                            <small class="form-text text-muted">Kosongkan jika tidak perlu grouping</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-calculator"></i> Konfigurasi Perhitungan
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="target_field">Field Target</label>
                                            <select class="form-control" id="target_field" name="target_field">
                                                <option value="">Pilih Field Target</option>
                                                <?php if (!empty($schema)): ?>
                                                    <?php foreach ($schema as $field): ?>
                                                        <?php $fieldName = $field['field_name'] ?? $field['name']; ?>
                                                        <option value="<?= esc($fieldName) ?>" <?= (old('target_field', $config['target_field'] ?? $statistic['target_field']) == $fieldName) ? 'selected' : '' ?>>
                                                            <?= esc($field['display_label'] ?? $fieldName) ?> (<?= esc($field['type']) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <small class="form-text text-muted">Field yang akan digunakan untuk perhitungan</small>
                                        </div>

                                        <?php if ($statistic['metric_type'] === 'custom_formula'): ?>
                                            <div class="form-group">
                                                <label for="custom_formula">Custom Formula</label>
                                                <textarea class="form-control" id="custom_formula" name="custom_formula" rows="3" placeholder="Masukkan formula matematika"><?= old('custom_formula', $config['custom_formula'] ?? '') ?></textarea>
                                                <small class="form-text text-muted">
                                                    Gunakan nama kolom sebagai variabel. Contoh: (harga * jumlah) + pajak
                                                </small>
                                            </div>
                                        <?php endif; ?>

                                        <div class="form-group">
                                            <label for="decimal_places">Desimal</label>
                                            <input type="number" class="form-control" id="decimal_places" name="decimal_places" value="<?= old('decimal_places', $config['decimal_places'] ?? 2) ?>" min="0" max="10">
                                            <small class="form-text text-muted">Jumlah digit desimal untuk hasil</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-bar"></i> Konfigurasi Visualisasi
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="chart_title">Judul Chart</label>
                                            <input type="text" class="form-control" id="chart_title" name="chart_title" value="<?= old('chart_title', $config['chart_title'] ?? $statistic['stat_name']) ?>" placeholder="Judul untuk chart">
                                        </div>

                                        <div class="form-group">
                                            <label for="x_axis_label">Label Sumbu X</label>
                                            <input type="text" class="form-control" id="x_axis_label" name="x_axis_label" value="<?= old('x_axis_label', $config['x_axis_label'] ?? '') ?>" placeholder="Label untuk sumbu X">
                                        </div>

                                        <div class="form-group">
                                            <label for="y_axis_label">Label Sumbu Y</label>
                                            <input type="text" class="form-control" id="y_axis_label" name="y_axis_label" value="<?= old('y_axis_label', $config['y_axis_label'] ?? '') ?>" placeholder="Label untuk sumbu Y">
                                        </div>

                                        <div class="form-group">
                                            <label for="colors">Warna Chart</label>
                                            <input type="text" class="form-control" id="colors" name="colors" value="<?= old('colors', $config['colors'] ?? '') ?>" placeholder="Warna dalam format hex atau nama warna">
                                            <small class="form-text text-muted">
                                                Pisahkan dengan koma. Contoh: #FF6384,#36A2EB,#FFCE56
                                            </small>
                                            
                                            <!-- Color Picker -->
                                            <div class="mt-2">
                                                <div id="colorPickerBtn" class="border rounded p-2 bg-white" style="cursor: pointer; display: inline-block;">
                                                    <span class="text-muted small"><i class="fas fa-palette"></i> Pilih Warna</span>
                                                </div>
                                                <!-- Color Preview Container -->
                                                <div id="colorPreviewContainer" class="d-flex gap-1 flex-wrap mt-2"></div>
                                            </div>
                                            <!-- Hidden Pickr Container -->
                                            <div id="colorPickerContainer" style="display: none;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Section - Outside Visualization Config Box -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-eye"></i> Preview
                                        </h5>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-primary btn-sm" id="previewBtn">
                                                <i class="fas fa-sync-alt"></i> Preview
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="previewContainer">
                                            <div class="text-center py-5">
                                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                                <h4 class="text-muted">Klik "Preview" untuk melihat hasil</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <button type="submit" form="statisticBuilderForm" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Konfigurasi
                    </button>
                    <button type="button" class="btn btn-info" id="testCalculationBtn">
                        <i class="fas fa-calculator"></i> Test Perhitungan
                    </button>
                    <a href="<?= base_url('owner/statistics/detail/' . $statistic['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- Chart.js with all controllers registered -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- Pickr Color Picker -->
<link href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/monolith.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>

<script>
    $(document).ready(function() {
        // Preview functionality
        $('#previewBtn').on('click', function() {
            const formData = new FormData(document.getElementById('statisticBuilderForm'));
            const config = {};

            // Convert FormData to JSON object
            for (let [key, value] of formData.entries()) {
                if (value !== '' && value !== null) {
                    try {
                        config[key] = JSON.parse(value);
                    } catch {
                        config[key] = value;
                    }
                }
            }

            // Build visualization_config object
            const visualizationConfig = {
                chart_title: config.chart_title || config.stat_name || '',
                x_axis_label: config.x_axis_label || '',
                y_axis_label: config.y_axis_label || '',
                colors: config.colors ? config.colors.split(',').map(c => c.trim()) : ['#198754', '#36A2EB', '#FFCE56', '#FF6384']
            };
            
            config.visualization_config = visualizationConfig;

            // Add required fields for preview
            config.dataset_id = <?= $statistic['dataset_id'] ?>;
            config.metric_type = '<?= $statistic['metric_type'] ?>';
            config.visualization_type = '<?= $statistic['visualization_type'] ?>';

            showLoading();

            $.ajax({
                url: `<?= base_url('owner/statistic-builder/preview') ?>`,
                method: 'POST',
                data: JSON.stringify(config),
                contentType: 'application/json',
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        renderPreview(response.data);
                    } else {
                        $('#previewContainer').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error: ' + response.message + '</div>');
                    }
                },
                error: function() {
                    hideLoading();
                    $('#previewContainer').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan saat memuat preview</div>');
                }
            });
        });

        // Test calculation
        $('#testCalculationBtn').on('click', function() {
            const formData = new FormData(document.getElementById('statisticBuilderForm'));
            const config = {};

            for (let [key, value] of formData.entries()) {
                if (value !== '' && value !== null) {
                    try {
                        config[key] = JSON.parse(value);
                    } catch {
                        config[key] = value;
                    }
                }
            }

            // Build visualization_config object
            const visualizationConfig = {
                chart_title: config.chart_title || config.stat_name || '',
                x_axis_label: config.x_axis_label || '',
                y_axis_label: config.y_axis_label || '',
                colors: config.colors ? config.colors.split(',').map(c => c.trim()) : ['#198754', '#36A2EB', '#FFCE56', '#FF6384']
            };
            
            config.visualization_config = visualizationConfig;
            config.statistic_id = <?= $statistic['id'] ?? 'null' ?>;

            showLoading();

            $.ajax({
                url: `<?= base_url('owner/statistics/recalculate/' . $statistic['id']) ?>`,
                method: 'POST',
                data: JSON.stringify(config),
                contentType: 'application/json',
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Perhitungan berhasil.', confirmButtonText: 'OK'});
                    } else {
                        Swal.fire({icon: 'error', title: 'Gagal!', text: response.message || 'Terjadi kesalahan', confirmButtonText: 'OK'});
                    }
                },
                error: function() {
                    hideLoading();
                    Swal.fire({icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan', confirmButtonText: 'OK'});
                }
            });
        });

        // Form validation
        $('#statisticBuilderForm').on('submit', function(e) {
            e.preventDefault();
            
            const targetField = $('#target_field').val();
            if (!targetField) {
                Swal.fire({icon: 'warning', title: 'Peringatan!', text: 'Field target harus dipilih', confirmButtonText: 'OK'});
                return false;
            }

            const formData = new FormData(this);
            const config = {};
            
            for (let [key, value] of formData.entries()) {
                if (value !== '' && value !== null) {
                    try {
                        config[key] = JSON.parse(value);
                    } catch {
                        config[key] = value;
                    }
                }
            }
            
            // Build visualization_config object
            const visualizationConfig = {
                chart_title: config.chart_title || config.stat_name || '',
                x_axis_label: config.x_axis_label || '',
                y_axis_label: config.y_axis_label || '',
                colors: config.colors ? config.colors.split(',').map(c => c.trim()) : ['#198754', '#36A2EB', '#FFCE56', '#FF6384'],
                decimal_places: config.decimal_places || 2
            };
            
            config.visualization_config = visualizationConfig;
            config.statistic_id = <?= $statistic['id'] ?? 'null' ?>;
            config.metric_type = '<?= $statistic['metric_type'] ?>';
            config.visualization_type = '<?= $statistic['visualization_type'] ?>';
            config.dataset_id = <?= $statistic['dataset_id'] ?>;

            showLoading();

            $.ajax({
                url: `<?= base_url('owner/statistics/builder/save/' . ($statistic['id'] ?? '')) ?>`,
                method: 'POST',
                data: JSON.stringify(config),
                contentType: 'application/json',
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        Swal.fire({icon: 'success', title: 'Berhasil!', text: response.message, confirmButtonText: 'OK'}).then(() => {
                            window.location.href = `<?= base_url('owner/statistics/detail/') ?>${response.statistic_id}`;
                        });
                    } else {
                        Swal.fire({icon: 'error', title: 'Gagal!', text: response.message || 'Terjadi kesalahan', confirmButtonText: 'OK'});
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    let errorMsg = 'Terjadi kesalahan saat menyimpan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({icon: 'error', title: 'Gagal!', text: errorMsg, confirmButtonText: 'OK'});
                }
            });
            
            return false;
        });

        function renderPreview(data) {
            let html = '';

            // If it's a chart type, always render as chart
            const chartTypes = ['bar_chart', 'pie_chart', 'line_chart', 'doughnut_chart', 'area_chart', 'horizontal_bar', 'polar_area', 'radar'];
            
            if (data.chart_type === 'table') {
                html = '<div class="table-responsive"><table class="table table-bordered table-striped">';
                if (data.headers && data.headers.length > 0) {
                    html += '<thead><tr>';
                    data.headers.forEach(header => {html += `<th>${header}</th>`;});
                    html += '</tr></thead>';
                }
                if (data.rows && data.rows.length > 0) {
                    html += '<tbody>';
                    data.rows.forEach(row => {
                        html += '<tr>';
                        row.forEach(cell => {html += `<td>${cell}</td>`;});
                        html += '</tr>';
                    });
                    html += '</tbody>';
                }
                html += '</table></div>';
            } else if (data.chart_type && chartTypes.includes(data.chart_type)) {
                html = '<div class="chart-container" style="height: 300px;"><canvas id="previewChart"></canvas></div>';
                // Small delay to ensure DOM is ready
                setTimeout(() => {renderChart(data);}, 100);
            } else if (data.chart_type) {
                // Unknown chart type, still try to render as chart
                html = '<div class="chart-container" style="height: 300px;"><canvas id="previewChart"></canvas></div>';
                setTimeout(() => {renderChart(data);}, 100);
            } else {
                // No chart type, show data as info card
                html = `<div class="alert alert-info"><i class="fas fa-info-circle"></i> Data tersedia, klik "Preview" untuk melihat chart</div>`;
            }

            $('#previewContainer').html(html);
        }

        function renderChart(data) {
            const ctx = document.getElementById('previewChart');
            if (!ctx) return;

            // Get colors - use from data or default
            const colors = data.colors && data.colors.length > 0 
                ? data.colors 
                : ['#198754', '#36A2EB', '#FFCE56', '#FF6384', '#4BC0C0'];
            
            // For pie/doughnut, use all colors. For others, use first color
            const backgroundColors = (data.chart_type === 'pie' || data.chart_type === 'doughnut' || data.chart_type === 'polar_area') 
                ? colors 
                : (colors[0] || 'rgba(25, 135, 84, 0.5)');
                
            const borderColor = (data.chart_type === 'pie' || data.chart_type === 'doughnut' || data.chart_type === 'polar_area') 
                ? colors 
                : (colors[0] || 'rgba(25, 135, 84, 1)');

            // Convert chart type (donut_chart -> doughnut, bar_chart -> bar, etc.)
            let chartType = data.chart_type;
            if (chartType) {
                chartType = chartType.replace('_chart', '');
                if (chartType === 'donut') chartType = 'doughnut';
                if (chartType === 'horizontal_bar') chartType = 'bar';
            }
            
            const fill = data.chart_type === 'area_chart' || data.chart_type === 'line_chart';

            // Check if should show legend
            const showLegend = ['pie', 'doughnut', 'polar_area', 'radar'].includes(chartType);
            
            // Check if should show scales
            const showScales = !showLegend;

            const config = {
                type: chartType || 'bar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: data.title || 'Data',
                        data: data.values || [],
                        backgroundColor: backgroundColors,
                        borderColor: borderColor,
                        borderWidth: 1,
                        fill: fill,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: chartType === 'bar' ? 'y' : 'x',
                    plugins: {
                        legend: {display: showLegend},
                        title: {display: !!data.title, text: data.title || ''}
                    },
                    scales: showScales ? {
                        y: {
                            beginAtZero: true, 
                            title: {display: !!data.y_axis_label, text: data.y_axis_label || ''}
                        },
                        x: {
                            title: {display: !!data.x_axis_label, text: data.x_axis_label || ''}
                        }
                    } : {}
                }
            };

            new Chart(ctx, config);
        }
        
        // Color Picker using Pickr Library
        let selectedColor = '';
        let pickr;
        
        // Initialize Pickr Color Picker
        $(document).ready(function() {
            // Register Chart.js controllers manually
            if (typeof Chart !== 'undefined') {
                // Doughnut is included in the core, but let's ensure it's registered
                // Chart.js 4.x includes all controllers in the bundle
            }
            
            // Show color picker when button is clicked
            $('#colorPickerBtn').on('click', function() {
                if (pickr) {
                    pickr.show();
                }
            });
            
            // Create pickr instance
            pickr = Pickr.create({
                el: '#colorPickerContainer',
                theme: 'monolith',
                default: '#198754',
                components: {
                    preview: true,
                    opacity: true,
                    hue: true,
                    interaction: {
                        hex: true,
                        rgba: true,
                        input: true,
                        save: true
                    }
                },
                i18n: {
                    'btn:save': 'Tambah',
                    'btn:cancel': 'Batal',
                    'btn:clear': 'Hapus'
                }
            });
            
            // Handle save button click
            pickr.on('save', (color) => {
                if (color) {
                    const hex = color.toHEXA().toString();
                    addColor(hex);
                }
                pickr.hide();
            });
            
            // Initialize color preview
            updateColorPreview();
        });
        
        function addColor(color) {
            const colorsInput = document.getElementById('colors');
            let currentColors = colorsInput.value ? colorsInput.value.split(',').map(c => c.trim()).filter(c => c) : [];
            
            // Add color if not already exists
            if (!currentColors.includes(color)) {
                currentColors.push(color);
                colorsInput.value = currentColors.join(', ');
                updateColorPreview();
            }
        }
        
        function removeColor(color) {
            const colorsInput = document.getElementById('colors');
            let currentColors = colorsInput.value ? colorsInput.value.split(',').map(c => c.trim()).filter(c => c) : [];
            currentColors = currentColors.filter(c => c !== color);
            colorsInput.value = currentColors.join(', ');
            updateColorPreview();
        }
        
        function updateColorPreview() {
            const colorsInput = document.getElementById('colors');
            const container = document.getElementById('colorPreviewContainer');
            const colors = colorsInput.value ? colorsInput.value.split(',').map(c => c.trim()).filter(c => c) : [];
            
            if (colors.length === 0) {
                container.innerHTML = '<span class="text-muted small">Belum ada warna dipilih</span>';
                return;
            }
            
            container.innerHTML = colors.map(color => 
                `<div class="position-relative" style="width: 28px; height: 28px; border-radius: 4px; border: 1px solid #dee2e6; background-color: ${color}; cursor: pointer;" 
                     title="${color} (klik untuk hapus)" onclick="removeColor('${color}')">
                    <span class="position-absolute top-50 start-50 translate-middle" style="color: white; font-size: 14px; text-shadow: 0 0 2px black; line-height: 1;">×</span>
                </div>`
            ).join('');
        }
        
        // Sync colors input with preview
        const colorsInput = document.getElementById('colors');
        if (colorsInput) {
            colorsInput.addEventListener('input', updateColorPreview);
            colorsInput.addEventListener('change', updateColorPreview);
        }
    });
</script>

<?= $this->endSection() ?>