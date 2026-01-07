<?php

/**
 * ============================================================================
 * STATISTIC API CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Api/StatisticApiController.php
 * 
 * Deskripsi:
 * Controller untuk API endpoint statistik.
 * Menyediakan data untuk chart dan widget di dashboard.
 * 
 * Features:
 * - Get statistic data for charts
 * - Calculate statistics on demand
 * - Cache support
 * 
 * Role: Authenticated users (Owner, Viewer)
 * ============================================================================
 */

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Owner\StatisticConfigModel;
use App\Models\Owner\DatasetModel;
use App\Libraries\ComputationEngine;

class StatisticApiController extends BaseController
{
    protected $statisticModel;
    protected $datasetModel;
    protected $computationEngine;

    public function __construct()
    {
        $this->statisticModel = new StatisticConfigModel();
        $this->datasetModel = new DatasetModel();
        $this->computationEngine = new ComputationEngine();
    }

    /**
     * Get statistic data for charts
     * GET /api/statistics/data/(:num)
     */
    public function getData($statisticId = null)
    {
        // Allow both AJAX and regular GET requests for dashboard widgets
        
        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            log_message('error', 'StatisticApiController: No application_id in session');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Application tidak ditemukan'
            ]);
        }

        if (empty($statisticId)) {
            log_message('error', 'StatisticApiController: Empty statistic ID');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Statistic ID diperlukan'
            ]);
        }

        log_message('info', 'StatisticApiController::getData - ID: ' . $statisticId . ', Application: ' . $applicationId);

        try {
            // Get statistic configuration
            $statistic = $this->statisticModel
                ->where('id', $statisticId)
                ->where('application_id', $applicationId)
                ->where('deleted_at', null)
                ->first();

            if (!$statistic) {
                log_message('error', 'StatisticApiController: Statistic not found - ID: ' . $statisticId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Statistik tidak ditemukan'
                ]);
            }

            if ($statistic['is_active'] != 1) {
                log_message('warning', 'StatisticApiController: Statistic not active - ID: ' . $statisticId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Statistik tidak aktif'
                ]);
            }

            log_message('info', 'StatisticApiController: Found statistic - ' . json_encode([
                'id' => $statistic['id'],
                'name' => $statistic['stat_name'],
                'metric_type' => $statistic['metric_type'],
                'viz_type' => $statistic['visualization_type']
            ]));

            // Prepare config for computation engine
            $config = $statistic;
            
            // Decode JSON fields safely
            $jsonFields = ['group_by_fields', 'filters', 'calculation_config', 'visualization_config'];
            foreach ($jsonFields as $field) {
                if (!empty($config[$field]) && is_string($config[$field])) {
                    $decoded = json_decode($config[$field], true);
                    // Only use decoded value if it's a valid array
                    if (is_array($decoded)) {
                        $config[$field] = $decoded;
                        log_message('debug', 'StatisticApiController: Decoded ' . $field . ': ' . json_encode($decoded));
                    }
                }
            }

            // Calculate statistic
            log_message('info', 'StatisticApiController: Starting calculation...');
            $calcResult = $this->computationEngine->calculate($config);

            log_message('debug', 'StatisticApiController - Calc result: ' . json_encode($calcResult));

            // Format result for charts - handle the wrapped format correctly
            $chartData = $this->formatForChart($statistic, $calcResult);

            log_message('info', 'StatisticApiController - Chart data: ' . json_encode($chartData));

            return $this->response->setJSON([
                'success' => true,
                'data' => $chartData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'StatisticApiController error for ID ' . $statisticId . ': ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate statistic (POST)
     * POST /api/statistics/calculate
     */
    public function calculate()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Application not found'
            ]);
        }

        $statisticId = $this->request->getPost('statistic_id');

        if (empty($statisticId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Statistic ID is required'
            ]);
        }

        try {
            $statistic = $this->statisticModel
                ->where('id', $statisticId)
                ->where('application_id', $applicationId)
                ->where('deleted_at', null)
                ->first();

            if (!$statistic) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Statistik tidak ditemukan'
                ]);
            }

            // Prepare config
            $config = $statistic;
            
            if (!empty($config['group_by_fields']) && is_string($config['group_by_fields'])) {
                $config['group_by_fields'] = json_decode($config['group_by_fields'], true);
            }
            if (!empty($config['filters']) && is_string($config['filters'])) {
                $config['filters'] = json_decode($config['filters'], true);
            }
            if (!empty($config['calculation_config']) && is_string($config['calculation_config'])) {
                $config['calculation_config'] = json_decode($config['calculation_config'], true);
            }
            if (!empty($config['visualization_config']) && is_string($config['visualization_config'])) {
                $config['visualization_config'] = json_decode($config['visualization_config'], true);
            }

            // Calculate
            $result = $this->computationEngine->calculate($config);

            // Update cache
            $this->statisticModel->updateCache($statisticId, $result);

            return $this->response->setJSON([
                'success' => true,
                'data' => $result,
                'message' => 'Statistik berhasil dihitung'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'StatisticApiController calculate error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Format statistic result for Chart.js
     * Handles the wrapped format from ComputationEngine: ['data' => [...], 'metadata' => [...]]
     */
    private function formatForChart($statistic, $calcResult)
    {
        $vizType = $statistic['visualization_type'] ?? 'table';
        
        // Handle wrapped format from ComputationEngine
        $result = $calcResult['data'] ?? $calcResult;
        $metadata = $calcResult['metadata'] ?? [];
        
        $chartData = [
            'chart_type' => $this->mapVizTypeToChartType($vizType),
            'label' => $statistic['stat_name'],
            'labels' => [],
            'values' => [],
            'backgroundColor' => [],
            'borderColor' => []
        ];

        // Handle different visualization types
        if (in_array($vizType, ['kpi_card', 'number'])) {
            // Single value - handle both wrapped and direct format
            if (is_array($result) && !empty($result)) {
                $firstItem = $result[0];
                $chartData['value'] = is_numeric($firstItem['value'] ?? null) ? (float)$firstItem['value'] : 0;
                $chartData['label'] = $firstItem['label'] ?? $statistic['stat_name'];
            } else {
                $chartData['value'] = 0;
                $chartData['label'] = $statistic['stat_name'];
            }
        } elseif ($vizType == 'table') {
            // Table data
            if (is_array($result) && !empty($result)) {
                $chartData['headers'] = ['Label', 'Value'];
                $chartData['rows'] = array_map(function($row) {
                    return [
                        'label' => $row['label'] ?? '-',
                        'value' => is_numeric($row['value'] ?? null) ? (float)$row['value'] : 0
                    ];
                }, $result);
                $chartData['labels'] = array_column($result, 'label');
                $chartData['values'] = array_map(function($row) {
                    return is_numeric($row['value'] ?? null) ? (float)$row['value'] : 0;
                }, $result);
            } else {
                $chartData['headers'] = ['Label', 'Value'];
                $chartData['rows'] = [];
                $chartData['labels'] = [];
                $chartData['values'] = [];
            }
        } elseif (in_array($vizType, ['bar_chart', 'pie_chart', 'line_chart', 'area_chart', 'donut_chart', 'scatter_chart'])) {
            // Chart data
            if (is_array($result) && !empty($result)) {
                $chartData['labels'] = array_column($result, 'label');
                $chartData['values'] = array_map(function($row) {
                    return is_numeric($row['value'] ?? null) ? (float)$row['value'] : 0;
                }, $result);
            }

            // Get colors from visualization_config first
            $vizConfig = !empty($statistic['visualization_config']) && is_string($statistic['visualization_config']) 
                ? json_decode($statistic['visualization_config'], true) 
                : ($statistic['visualization_config'] ?? []);
            
            if (!empty($vizConfig['colors']) && is_array($vizConfig['colors'])) {
                // Use colors from visualization_config
                $chartData['colors'] = $vizConfig['colors'];
                $chartData['backgroundColor'] = $vizConfig['colors'];
                $chartData['borderColor'] = array_map(function($color) {
                    return $color;
                }, $vizConfig['colors']);
            } else {
                // Generate colors if not configured
                $labelCount = count($chartData['labels']);
                if ($labelCount > 0) {
                    $chartData['colors'] = $this->generateColors($labelCount);
                    $chartData['backgroundColor'] = $chartData['colors'];
                    $chartData['borderColor'] = array_map(function($color) {
                        return $color;
                    }, $chartData['colors']);
                }
            }
        } elseif ($vizType == 'progress_bar') {
            if (is_array($result) && !empty($result)) {
                $firstItem = $result[0];
                $chartData['value'] = is_numeric($firstItem['value'] ?? null) ? (float)$firstItem['value'] : 0;
            } else {
                $chartData['value'] = 0;
            }
            $chartData['max'] = 100;
            $chartData['label'] = '';
        }

        // Add metadata if available
        if (!empty($metadata)) {
            $chartData['metadata'] = $metadata;
        }

        return $chartData;
    }

    /**
     * Map visualization type to Chart.js type
     */
    private function mapVizTypeToChartType($vizType)
    {
        $map = [
            'bar_chart' => 'bar',
            'pie_chart' => 'pie',
            'line_chart' => 'line',
            'area_chart' => 'line',
            'donut_chart' => 'doughnut',
            'scatter_chart' => 'scatter'
        ];

        return $map[$vizType] ?? 'bar';
    }

    /**
     * Generate random colors for charts
     */
    private function generateColors($count)
    {
        $colors = [
            'rgba(25, 135, 84, 0.5)',
            'rgba(13, 110, 253, 0.5)',
            'rgba(255, 193, 7, 0.5)',
            'rgba(220, 53, 69, 0.5)',
            'rgba(111, 66, 193, 0.5)',
            'rgba(253, 126, 20, 0.5)',
            'rgba(13, 202, 240, 0.5)',
            'rgba(108, 117, 125, 0.5)',
            'rgba(32, 201, 151, 0.5)',
            'rgba(246, 194, 62, 0.5)'
        ];

        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }

        return $result;
    }
}

