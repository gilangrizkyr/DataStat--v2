<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Statistik: <?= esc($statistic['stat_name']) ?></h3>
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

                    <form action="<?= base_url('owner/statistics/update/' . $statistic['id']) ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stat_name">Nama Statistik <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= ($validation->hasError('stat_name')) ? 'is-invalid' : '' ?>" id="stat_name" name="stat_name" value="<?= old('stat_name', esc($statistic['stat_name'])) ?>" placeholder="Masukkan nama statistik" required>
                                    <?php if ($validation->hasError('stat_name')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('stat_name') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dataset_id">Dataset</label>
                                    <select class="form-control" id="dataset_id" name="dataset_id" disabled>
                                        <?php foreach ($datasets as $dataset): ?>
                                            <option value="<?= $dataset['id'] ?>" <?= ($statistic['dataset_id'] == $dataset['id']) ? 'selected' : '' ?>>
                                                <?= esc($dataset['dataset_name']) ?> (<?= format_file_size($dataset['file_size']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Dataset tidak dapat diubah setelah statistik dibuat</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi statistik (opsional)"><?= old('description', esc($statistic['description'])) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metric_type">Tipe Metrik <span class="text-danger">*</span></label>
                                    <select class="form-control <?= ($validation->hasError('metric_type')) ? 'is-invalid' : '' ?>" id="metric_type" name="metric_type" required>
                                        <option value="">Pilih Tipe Metrik</option>
                                        <option value="count" <?= (old('metric_type', $statistic['metric_type']) == 'count') ? 'selected' : '' ?>>Count (Jumlah)</option>
                                        <option value="sum" <?= (old('metric_type', $statistic['metric_type']) == 'sum') ? 'selected' : '' ?>>Sum (Jumlah Total)</option>
                                        <option value="average" <?= (old('metric_type', $statistic['metric_type']) == 'average') ? 'selected' : '' ?>>Average (Rata-rata)</option>
                                        <option value="min" <?= (old('metric_type', $statistic['metric_type']) == 'min') ? 'selected' : '' ?>>Minimum</option>
                                        <option value="max" <?= (old('metric_type', $statistic['metric_type']) == 'max') ? 'selected' : '' ?>>Maximum</option>
                                        <option value="percentage" <?= (old('metric_type', $statistic['metric_type']) == 'percentage') ? 'selected' : '' ?>>Percentage (Persentase)</option>
                                        <option value="ratio" <?= (old('metric_type', $statistic['metric_type']) == 'ratio') ? 'selected' : '' ?>>Ratio (Rasio)</option>
                                        <option value="growth" <?= (old('metric_type', $statistic['metric_type']) == 'growth') ? 'selected' : '' ?>>Growth (Pertumbuhan)</option>
                                        <option value="ranking" <?= (old('metric_type', $statistic['metric_type']) == 'ranking') ? 'selected' : '' ?>>Ranking (Peringkat)</option>
                                        <option value="custom_formula" <?= (old('metric_type', $statistic['metric_type']) == 'custom_formula') ? 'selected' : '' ?>>Custom Formula</option>
                                    </select>
                                    <?php if ($validation->hasError('metric_type')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('metric_type') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="visualization_type">Tipe Visualisasi <span class="text-danger">*</span></label>
                                    <select class="form-control <?= ($validation->hasError('visualization_type')) ? 'is-invalid' : '' ?>" id="visualization_type" name="visualization_type" required>
                                        <option value="">Pilih Tipe Visualisasi</option>
                                        <option value="table" <?= (old('visualization_type', $statistic['visualization_type']) == 'table') ? 'selected' : '' ?>>Table (Tabel)</option>
                                        <option value="bar_chart" <?= (old('visualization_type', $statistic['visualization_type']) == 'bar_chart') ? 'selected' : '' ?>>Bar Chart</option>
                                        <option value="pie_chart" <?= (old('visualization_type', $statistic['visualization_type']) == 'pie_chart') ? 'selected' : '' ?>>Pie Chart</option>
                                        <option value="line_chart" <?= (old('visualization_type', $statistic['visualization_type']) == 'line_chart') ? 'selected' : '' ?>>Line Chart</option>
                                        <option value="area_chart" <?= (old('visualization_type', $statistic['visualization_type']) == 'area_chart') ? 'selected' : '' ?>>Area Chart</option>
                                        <option value="kpi_card" <?= (old('visualization_type', $statistic['visualization_type']) == 'kpi_card') ? 'selected' : '' ?>>KPI Card</option>
                                        <option value="progress_bar" <?= (old('visualization_type', $statistic['visualization_type']) == 'progress_bar') ? 'selected' : '' ?>>Progress Bar</option>
                                        <option value="donut_chart" <?= (old('visualization_type', $statistic['visualization_type']) == 'donut_chart') ? 'selected' : '' ?>>Donut Chart</option>
                                        <option value="scatter_chart" <?= (old('visualization_type', $statistic['visualization_type']) == 'scatter_chart') ? 'selected' : '' ?>>Scatter Chart</option>
                                    </select>
                                    <?php if ($validation->hasError('visualization_type')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('visualization_type') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="target_field_group">
                            <label for="target_field">Field Target <span class="text-danger">*</span></label>
                            <select class="form-control <?= ($validation->hasError('target_field')) ? 'is-invalid' : '' ?>" id="target_field" name="target_field">
                                <option value="">Memuat field...</option>
                            </select>
                            <small class="form-text text-muted">Field yang akan digunakan untuk perhitungan statistik</small>
                            <?php if ($validation->hasError('target_field')): ?>
                                <div class="invalid-feedback d-block">
                                    <?= $validation->getError('target_field') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card border-warning">
                            <div class="card-header bg-warning">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle"></i> Perhatian
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Perubahan yang Anda buat akan mempengaruhi:</strong></p>
                                <ul class="mb-0">
                                    <li>Nama dan deskripsi statistik</li>
                                    <li>Tipe metrik dan visualisasi</li>
                                    <li>Dashboard yang menggunakan statistik ini</li>
                                </ul>
                                <p class="text-warning mt-2 mb-0">
                                    <small>Untuk perubahan konfigurasi yang lebih kompleks, gunakan Statistic Builder.</small>
                                </p>
                            </div>
                        </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Statistik
                    </button>
                    <a href="<?= base_url('owner/statistics/builder/' . $statistic['id']) ?>" class="btn btn-info">
                        <i class="fas fa-cogs"></i> Buka Builder
                    </a>
                    <a href="<?= base_url('owner/statistics') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Konfigurasi metric types yang memerlukan target field
    const METRIC_TYPES_WITH_TARGET = ['sum', 'average', 'min', 'max', 'percentage', 'ratio', 'growth', 'ranking'];
    const METRIC_TYPES_NO_TARGET = ['count', 'custom_formula'];
    
    // Dataset ID dari statistik yang sedang diedit
    const currentDatasetId = <?= $statistic['dataset_id'] ?>;
    const currentTargetField = '<?= esc($statistic['target_field'] ?? '') ?>';
    
    $(document).ready(function() {
        // Trigger load fields immediately
        loadTargetFields();
        
        // Load target fields when metric type changes
        $('#metric_type').on('change', function() {
            const metricType = $(this).val();
            const targetFieldGroup = $('#target_field_group');
            const targetField = $('#target_field');
            const targetFieldLabel = targetFieldGroup.find('label');
            
            if (METRIC_TYPES_WITH_TARGET.includes(metricType)) {
                targetFieldGroup.show();
                targetFieldLabel.html('Field Target <span class="text-danger">*</span>');
                targetField.prop('required', true);
                if (targetField.find('option').length <= 1) {
                    loadTargetFields();
                }
            } else if (METRIC_TYPES_NO_TARGET.includes(metricType)) {
                targetFieldGroup.hide();
                targetField.prop('required', false);
                targetField.html('<option value="">Tidak diperlukan</option>');
            } else {
                targetFieldGroup.show();
                targetFieldLabel.html('Field Target');
                targetField.prop('required', false);
                loadTargetFields();
            }
        });
        
        // Trigger change event on load
        $('#metric_type').trigger('change');
    });

    function loadTargetFields() {
        const targetFieldSelect = $('#target_field');
        
        targetFieldSelect.html('<option value="">Memuat field...</option>');
        
        $.ajax({
            url: '<?= base_url('owner/datasets/get-fields/') ?>' + currentDatasetId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.fields && response.fields.length > 0) {
                    let options = '<option value="">Pilih Field Target</option>';
                    
                    response.fields.forEach(function(field) {
                        // Use field_name from response
                        const fieldName = field.field_name || field.name || '';
                        const fieldType = field.type || 'string';
                        const selected = (fieldName === currentTargetField) ? 'selected' : '';
                        options += `<option value="${fieldName}" ${selected}>${fieldName} (${fieldType})</option>`;
                    });
                    
                    targetFieldSelect.html(options);
                    
                    // Select current value if exists
                    if (currentTargetField) {
                        targetFieldSelect.val(currentTargetField);
                    }
                } else {
                    targetFieldSelect.html('<option value="">Field tidak tersedia</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading fields:', error);
                targetFieldSelect.html('<option value="">Gagal memuat field</option>');
            }
        });
    }
</script>

<?= $this->endSection() ?>

