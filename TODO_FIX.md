# Perbaikan Error - Undefined array key "schema_config"

## Masalah
Error 500 saat membuka `/owner/statistics/builder/5` karena dataset tidak memiliki `schema_config`.

## Penyebab
- Dataset terkait dengan statistic tidak memiliki field `schema_config` yang valid
- Tidak ada pengecekan null/empty sebelum decode JSON
- Method recalculate() memanggil compute dengan parameter kedua yang tidak didukung

## Plan Fix

### 1. Fix StatisticBuilderController.php - Method index()
- [x] Tambahkan null check untuk `$dataset['schema_config']`
- [x] Gunakan `json_decode($dataset['schema_config'] ?? '[]', true)`

### 2. Fix StatisticBuilderController.php - Method getDatasetSchema()
- [x] Tambahkan null check untuk `$dataset['schema_config']`
- [x] Return error message yang jelas jika schema tidak tersedia

### 3. Fix ComputationEngine.php - Method calculate()
- [x] Tambahkan null check untuk `$this->dataset['schema_config']`
- [x] Throw exception yang jelas jika schema tidak valid

### 4. Fix StatisticController.php - Method recalculate()
- [x] Hapus parameter kedua yang tidak digunakan di `calculate($config, true)`
- [x] Sekarang memanggil `calculate($config)` dengan benar

### 5. Upgrade alert di builder.php dengan SweetAlert2
- [x] Tambah library SweetAlert2
- [x] Ganti alert() dengan Swal.fire() yang lebih menarik

### 6. Optimasi Performance ComputationEngine
- [x] Tambah limit 1000 records di loadRecords() untuk preview
- [x] Tambah validasi json_decode agar tidak error pada data invalid
- [x] Optimasi count: langsung hitung dari database tanpa load records
- [x] Tambah method metricTypeNeedsRecords() untuk skip loadRecords jika tidak diperlukan

## Status
- [x] Fix StatisticBuilderController.php
- [x] Fix ComputationEngine.php
- [x] Fix StatisticController.php
- [x] Upgrade alert dengan SweetAlert2
- [x] Optimasi performance

