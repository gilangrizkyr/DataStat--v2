<?php
// Script untuk memperbaiki timezone dan chart
$appConfig = file_get_contents('/home/Gilang/DPMPTSP/DataStat1/app/Config/App.php');
$appConfig = str_replace("public string \$appTimezone = 'UTC';", "public string \$appTimezone = 'Asia/Makassar';", $appConfig);
file_put_contents('/home/Gilang/DPMPTSP/DataStat1/app/Config/App.php', $appConfig);
echo "Timezone berhasil diubah ke Asia/Makassar (WITA)\n";

// Fix ComputationEngine.php
$computationEngine = file_get_contents('/home/Gilang/DPMPTSP/DataStat1/app/Libraries/ComputationEngine.php');

// Replace error messages with more helpful ones
$computationEngine = str_replace(
    'throw new \Exception("dataset_id required in config");',
    'throw new \Exception("dataset_id diperlukan untuk menghitung statistik. Pastikan statistik sudah dikonfigurasi dengan dataset yang valid.");',
    $computationEngine
);

$computationEngine = str_replace(
    'throw new \Exception("metric_type is required in config");',
    'throw new \Exception("metric_type diperlukan untuk menghitung statistik. Pastikan statistik sudah dikonfigurasi dengan metric yang valid (count, sum, average, dll).");',
    $computationEngine
);

file_put_contents('/home/Gilang/DPMPTSP/DataStat1/app/Libraries/ComputationEngine.php', $computationEngine);
echo "ComputationEngine.php diperbaiki\n";

echo "\nPerbaikan selesai!\n";
