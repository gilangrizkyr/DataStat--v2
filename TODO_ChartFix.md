# TODO - Perbaikan Chart di Dashboard

## Masalah
- Chart di halaman preview (http://localhost:8080/owner/dashboards/preview/2) tidak muncul
- JavaScript mencari canvas tapi tidak menemukan karena `visualization_type` tidak ter-load

## Solusi

### 1. DashboardController.php - Method preview()
**Masalah**: Widget tidak memiliki field `visualization_type` - itu ada di table `statistic_configs`

**Solusi**: Controller sekarang mengambil `visualization_type` dari statistic config:
```php
if ($statistic) {
    // Add visualization_type to widget
    $widget['visualization_type'] = $statistic['visualization_type'] ?? 'table';
    
    // Calculate statistic data
    $calculationResult = $statisticModel->calculateStatistic($widget['statistic_config_id']);
    $widget['statistic_data'] = $calculationResult['data'] ?? [];
    $widget['statistic_error'] = $calculationResult['error'] ?? null;
}
```

### 2. preview.php View
- Widget rendering menggunakan data pre-kalkulasi dari controller
- Canvas memiliki data attributes `data-chart-labels` dan `data-chart-values`
- JavaScript prioritas menggunakan embedded data sebelum AJAX
- Fallback ke AJAX jika data embedded tidak tersedia

### 3. manage.php View
- Sama seperti preview.php, menggunakan data embedded + AJAX fallback

## Hasil
- Chart berhasil dirender di halaman preview
- Tidak ada console error
- Clean code tanpa debug logging

## URL yang Dipengaruhi
- http://localhost:8080/owner/dashboards/preview/{id}
- http://localhost:8080/owner/dashboards/manage/{id}

