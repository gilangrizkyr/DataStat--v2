<?php

/**
 * ============================================================================
 * OWNER STATISTIC BUILDER CONTROLLER
 * ============================================================================
 *
 * Path: app/Controllers/Owner/StatisticBuilderController.php
 *
 * Deskripsi:
 * Controller khusus untuk Statistic Builder Interface.
 * Fitur advanced untuk membangun statistik dengan drag-drop, filter builder,
 * group by, calculation config, dan visualization config.
 * 
 * Fitur:
 * - Visual statistic builder interface
 * - Field selector dengan preview
 * - Metric configuration (count, sum, avg, custom formula, dll)
 * - Filter builder (AND/OR conditions)
 * - Group by selector
 * - Visualization config (colors, labels, format)
 * - Real-time preview
 * - Save configuration
 * 
 * Role: Owner
 * ============================================================================
 */

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\Owner\StatisticConfigModel;
use App\Models\Owner\DatasetModel;
use App\Models\Owner\DatasetRecordModel;
use App\Libraries\ComputationEngine;

class StatisticBuilderController extends BaseController
{
    protected $statisticModel;
    protected $datasetModel;
    protected $recordModel;
    protected $computationEngine;

    public function __construct()
    {
        $this->statisticModel = new StatisticConfigModel();
        $this->datasetModel = new DatasetModel();
        $this->recordModel = new DatasetRecordModel();
        $this->computationEngine = new ComputationEngine();
        helper(['form', 'url', 'text']);
    }

    /**
     * Tampilkan statistic builder interface
     */
    public function index($id = null)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/owner/application/create');
        }

        $statistic = null;
        $dataset = null;
        $schema = [];

        if ($id) {
            // Edit mode - load existing statistic
            $statistic = $this->statisticModel
                ->where('id', $id)
                ->where('application_id', $applicationId)
                ->where('deleted_at', null)
                ->first();

            if (!$statistic) {
                return redirect()->to('/owner/statistics')
                    ->with('error', 'Statistik tidak ditemukan');
            }

            // Get dataset
            $dataset = $this->datasetModel->find($statistic['dataset_id']);
            if ($dataset) {
                $statistic['dataset_name'] = $dataset['dataset_name'];
                // Safe decode with null check
                $schemaConfig = $dataset['schema_config'] ?? '[]';
                $schema = json_decode($schemaConfig, true);
                if (!is_array($schema)) {
                    $schema = [];
                }
            } else {
                $statistic['dataset_name'] = 'Dataset tidak ditemukan';
                $schema = [];
            }
        }

        // Get available datasets
        $datasets = $this->datasetModel
            ->where('application_id', $applicationId)
            ->where('upload_status', 'completed')
            ->where('deleted_at', null)
            ->findAll();

        $data = [
            'title' => $id ? 'Edit Statistic Builder' : 'Statistic Builder',
            'statistic' => $statistic,
            'dataset' => $dataset,
            'datasets' => $datasets,
            'schema' => $schema
        ];

        return view('owner/statistics/builder', $data);
    }

    /**
     * Get dataset schema via AJAX
     */
    public function getDatasetSchema($datasetId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        $dataset = $this->datasetModel
            ->where('id', $datasetId)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dataset tidak ditemukan'
            ]);
        }

        $schema = json_decode($dataset['schema_config'] ?? '[]', true);

        if (!is_array($schema) || empty($schema)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Schema tidak tersedia untuk dataset ini'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'dataset' => $dataset,
                'schema' => $schema
            ]
        ]);
    }

    /**
     * Get sample data dari dataset
     */
    public function getSampleData($datasetId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        $dataset = $this->datasetModel
            ->where('id', $datasetId)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dataset tidak ditemukan'
            ]);
        }

        // Get 10 sample records
        $records = $this->recordModel
            ->where('dataset_id', $datasetId)
            ->limit(10)
            ->findAll();

        $sampleData = [];
        foreach ($records as $record) {
            $sampleData[] = json_decode($record['data_json'], true);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $sampleData
        ]);
    }

    /**
     * Preview calculation hasil statistik
     */
    public function previewCalculation()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        try {
            $config = $this->request->getJSON(true);

            // Build temporary statistic config
            $tempConfig = [
                'dataset_id' => $config['dataset_id'],
                'metric_type' => $config['metric_type'],
                'target_field' => $config['target_field'] ?? null,
                'group_by_fields' => isset($config['group_by']) ? json_encode($config['group_by']) : null,
                'filters' => isset($config['filters']) ? json_encode($config['filters']) : null,
                'calculation_config' => isset($config['calculation_config']) ? json_encode($config['calculation_config']) : null,
                'custom_formula' => $config['custom_formula'] ?? null
            ];

            // Calculate
            $calcResult = $this->computationEngine->calculate($tempConfig);

            // Get visualization config
            $visualizationConfig = [];
            if (isset($config['visualization_config']) && is_array($config['visualization_config'])) {
                $visualizationConfig = $config['visualization_config'];
            } else {
                $visualizationConfig = [
                    'chart_title' => $config['chart_title'] ?? $config['stat_name'] ?? '',
                    'x_axis_label' => $config['x_axis_label'] ?? '',
                    'y_axis_label' => $config['y_axis_label'] ?? '',
                    'colors' => isset($config['colors']) 
                        ? (is_string($config['colors']) ? array_map('trim', explode(',', $config['colors'])) : $config['colors'])
                        : ['#198754', '#36A2EB', '#FFCE56', '#FF6384']
                ];
            }

            // Format for visualization
            $visualizationType = $config['visualization_type'] ?? 'table';
            $previewData = $this->computationEngine->getVisualizationData($calcResult, $visualizationType, $visualizationConfig);

            return $this->response->setJSON([
                'success' => true,
                'data' => $previewData
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save builder configuration
     */
    public function saveConfiguration()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        try {
            $config = $this->request->getJSON(true);
            $applicationId = session()->get('application_id');
            $userId = session()->get('user_id');

            $statisticId = $config['statistic_id'] ?? null;

            // Build visualization_config if not provided as object
            $visualizationConfig = [];
            if (isset($config['visualization_config']) && is_array($config['visualization_config'])) {
                $visualizationConfig = $config['visualization_config'];
            } else {
                // Build from individual fields
                $visualizationConfig = [
                    'chart_title' => $config['chart_title'] ?? $config['stat_name'] ?? '',
                    'x_axis_label' => $config['x_axis_label'] ?? '',
                    'y_axis_label' => $config['y_axis_label'] ?? '',
                    'colors' => isset($config['colors']) 
                        ? (is_string($config['colors']) ? array_map('trim', explode(',', $config['colors'])) : $config['colors'])
                        : ['#198754', '#36A2EB', '#FFCE56', '#FF6384'],
                    'decimal_places' => $config['decimal_places'] ?? 2
                ];
            }

            if ($statisticId) {
                // Update existing
                $updateData = [
                    'stat_name' => $config['stat_name'],
                    'description' => $config['description'] ?? null,
                    'metric_type' => $config['metric_type'],
                    'target_field' => $config['target_field'] ?? null,
                    'group_by_fields' => isset($config['group_by']) ? json_encode($config['group_by']) : null,
                    'filters' => isset($config['filters']) ? json_encode($config['filters']) : null,
                    'custom_formula' => $config['custom_formula'] ?? null,
                    'calculation_config' => isset($config['calculation_config']) ? json_encode($config['calculation_config']) : null,
                    'visualization_type' => $config['visualization_type'],
                    'visualization_config' => json_encode($visualizationConfig),
                    'sort_by' => $config['sort_by'] ?? null,
                    'sort_order' => $config['sort_order'] ?? 'asc',
                    'limit_rows' => $config['limit_rows'] ?? null,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->statisticModel->update($statisticId, $updateData);

                $this->logActivity('update', 'statistics', 'Owner update config statistik via builder', [
                    'statistic_id' => $statisticId
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Konfigurasi berhasil disimpan',
                    'statistic_id' => $statisticId
                ]);
            } else {
                // Create new
                $slug = url_title($config['stat_name'], '-', true) . '-' . uniqid();

                $insertData = [
                    'application_id' => $applicationId,
                    'dataset_id' => $config['dataset_id'],
                    'stat_name' => $config['stat_name'],
                    'stat_slug' => $slug,
                    'description' => $config['description'] ?? null,
                    'metric_type' => $config['metric_type'],
                    'target_field' => $config['target_field'] ?? null,
                    'group_by_fields' => isset($config['group_by']) ? json_encode($config['group_by']) : null,
                    'filters' => isset($config['filters']) ? json_encode($config['filters']) : null,
                    'custom_formula' => $config['custom_formula'] ?? null,
                    'calculation_config' => isset($config['calculation_config']) ? json_encode($config['calculation_config']) : null,
                    'visualization_type' => $config['visualization_type'],
                    'visualization_config' => json_encode($visualizationConfig),
                    'sort_by' => $config['sort_by'] ?? null,
                    'sort_order' => $config['sort_order'] ?? 'asc',
                    'limit_rows' => $config['limit_rows'] ?? null,
                    'is_active' => 1,
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $newId = $this->statisticModel->insert($insertData);

                $this->logActivity('create', 'statistics', 'Owner create statistik via builder', [
                    'statistic_id' => $newId
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Statistik berhasil dibuat',
                    'statistic_id' => $newId
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Log aktivitas
     */
    private function logActivity($activityType, $module, $description, $data = [])
    {
        $logData = [
            'user_id' => session()->get('user_id'),
            'application_id' => session()->get('application_id'),
            'activity_type' => $activityType,
            'module' => $module,
            'description' => $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'request_data' => json_encode($data)
        ];

        $db = \Config\Database::connect();
        $db->table('log_activities')->insert($logData);
    }
}
