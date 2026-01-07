<?php

/**
 * ============================================================================
 * COMPUTATION ENGINE LIBRARY
 * ============================================================================
 * 
 * Path: app/Libraries/ComputationEngine.php
 * 
 * Deskripsi:
 * Library untuk menghitung statistik berdasarkan konfigurasi.
 * Mendukung berbagai metric types dan operasi kompleks.
 * 
 * Supported Metrics:
 * - count: Hitung jumlah data
 * - sum: Total nilai field
 * - average: Rata-rata
 * - min: Nilai minimum
 * - max: Nilai maksimum
 * - percentage: Persentase
 * - ratio: Rasio antar field
 * - growth: Pertumbuhan (YoY, MoM, dll)
 * - ranking: Ranking/urutan
 * - custom_formula: Formula custom
 * 
 * Features:
 * - Group by support
 * - Filter conditions (AND/OR)
 * - Sorting & limiting
 * - Result caching
 * - Multiple aggregations
 * 
 * Used by: Owner/StatisticController, Viewer/StatisticController
 * ============================================================================
 */

namespace App\Libraries;

use App\Models\Owner\DatasetRecordModel;

class ComputationEngine
{
    protected $datasetRecordModel;
    protected $dataset;
    protected $records = [];
    protected $schema = [];

    public function __construct()
    {
        $this->datasetRecordModel = new DatasetRecordModel();
    }

    /**
     * Calculate statistic berdasarkan config
     * 
     * Supports two signatures:
     * 1. calculate($datasetId, $config) - Standard
     * 2. calculate($config) - If config contains dataset_id
     */
    public function calculate($datasetIdOrConfig, $config = null)
    {
        // Detect signature
        if ($config === null && is_array($datasetIdOrConfig)) {
            // Signature 2: calculate($config)
            $config = $datasetIdOrConfig;
            $datasetId = $config['dataset_id'] ?? null;
            
            if (!$datasetId) {
                throw new \Exception("dataset_id diperlukan untuk menghitung statistik. Pastikan statistik sudah dikonfigurasi dengan dataset yang valid.");
            }
        } else {
            // Signature 1: calculate($datasetId, $config)
            $datasetId = $datasetIdOrConfig;
        }

        // Validate metric_type
        $metricType = $config['metric_type'] ?? null;
        if (empty($metricType)) {
            throw new \Exception("metric_type diperlukan untuk menghitung statistik. Pastikan statistik sudah dikonfigurasi dengan metric yang valid (count, sum, average, dll).");
        }
        
        // Valid metric types
        $validMetricTypes = ['count', 'sum', 'average', 'avg', 'min', 'max', 'percentage', 'ratio', 'growth', 'ranking', 'custom_formula'];
        if (!in_array($metricType, $validMetricTypes)) {
            throw new \Exception("Metric type '{$metricType}' tidak valid. Supported: " . implode(', ', $validMetricTypes));
        }

        // Load dataset schema
        $datasetModel = new \App\Models\Owner\DatasetModel();
        $this->dataset = $datasetModel->find($datasetId);
        
        if (!$this->dataset) {
            throw new \Exception("Dataset tidak ditemukan (ID: {$datasetId})");
        }

        $this->schema = json_decode($this->dataset['schema_config'] ?? '[]', true);
        
        if (!is_array($this->schema)) {
            throw new \Exception("Dataset schema tidak valid");
        }

        // Load data records HANYA jika diperlukan untuk metric type tertentu
        // Count tanpa group by TIDAK perlu load records
        $needsRecords = $this->metricTypeNeedsRecords($metricType, $config);
        
        if ($needsRecords) {
            $this->loadRecords($datasetId, $config);
        } else {
            $this->records = [];
        }

        // Calculate berdasarkan metric type
        
        switch ($metricType) {
            case 'count':
                $result = $this->calculateCount($config);
                break;
            
            case 'sum':
                $result = $this->calculateSum($config);
                break;
            
            case 'average':
            case 'avg':
                $result = $this->calculateAverage($config);
                break;
            
            case 'min':
                $result = $this->calculateMin($config);
                break;
            
            case 'max':
                $result = $this->calculateMax($config);
                break;
            
            case 'percentage':
                $result = $this->calculatePercentage($config);
                break;
            
            case 'ratio':
                $result = $this->calculateRatio($config);
                break;
            
            case 'growth':
                $result = $this->calculateGrowth($config);
                break;
            
            case 'ranking':
                $result = $this->calculateRanking($config);
                break;
            
            case 'custom_formula':
                $result = $this->calculateCustomFormula($config);
                break;
            
            default:
                // This should not happen due to validation above
                throw new \Exception("Metric type '{$metricType}' tidak didukung");
        }

        // Ensure result is always an array
        if (!is_array($result)) {
            $result = [];
        }

        // Apply sorting
        if (!empty($config['sort_by'])) {
            $result = $this->applySorting($result, $config);
        }

        // Apply limit
        if (!empty($config['limit_rows'])) {
            $result = $this->applyLimit($result, $config['limit_rows']);
        }

        return [
            'data' => $result,
            'metadata' => [
                'total_rows' => count($result),
                'metric_type' => $metricType,
                'calculated_at' => date('Y-m-d H:i:s'),
                'dataset_name' => $this->dataset['dataset_name'] ?? 'Unknown',
                'dataset_id' => $datasetId
            ]
        ];
    }

    /**
     * Determine apakah metric type memerlukan load records
     * Optimasi untuk performance
     */
    protected function metricTypeNeedsRecords($metricType, $config)
    {
        // Count tanpa group by TIDAK perlu load records (gunakan COUNT query)
        if ($metricType === 'count') {
            $groupByFields = $config['group_by_fields'] ?? [];
            return !empty($groupByFields); // Hanya perlu records jika ada group by
        }
        
        // Metric type lain memerlukan records
        return true;
    }

    /**
     * Load records dari database
     * Dioptimasi dengan limit untuk preview
     */
    protected function loadRecords($datasetId, $config)
    {
        // Default limit untuk mencegah memory overflow
        $defaultLimit = 1000;
        
        $records = $this->datasetRecordModel->getByDataset($datasetId, $defaultLimit);
        
        $this->records = [];
        foreach ($records as $record) {
            $decoded = json_decode($record['data_json'], true);
            if (is_array($decoded)) {
                $this->records[] = $decoded;
            }
        }
    }

    /**
     * Apply filters
     */
    protected function applyFilters($records, $filters)
    {
        if (empty($filters)) {
            return $records;
        }

        $logic = $filters['logic'] ?? 'AND'; // AND or OR
        $conditions = $filters['conditions'] ?? [];

        return array_filter($records, function($record) use ($conditions, $logic) {
            $results = [];

            foreach ($conditions as $condition) {
                $field = $condition['field'];
                $operator = $condition['operator'];
                $value = $condition['value'];

                $recordValue = $record[$field] ?? null;
                $results[] = $this->evaluateCondition($recordValue, $operator, $value);
            }

            // Apply logic
            if ($logic === 'OR') {
                return in_array(true, $results);
            } else {
                return !in_array(false, $results);
            }
        });
    }

    /**
     * Evaluate single condition
     */
    protected function evaluateCondition($recordValue, $operator, $compareValue)
    {
        switch ($operator) {
            case '=':
            case '==':
                return $recordValue == $compareValue;
            
            case '!=':
                return $recordValue != $compareValue;
            
            case '>':
                return $recordValue > $compareValue;
            
            case '>=':
                return $recordValue >= $compareValue;
            
            case '<':
                return $recordValue < $compareValue;
            
            case '<=':
                return $recordValue <= $compareValue;
            
            case 'contains':
                return stripos($recordValue, $compareValue) !== false;
            
            case 'not_contains':
                return stripos($recordValue, $compareValue) === false;
            
            case 'starts_with':
                return stripos($recordValue, $compareValue) === 0;
            
            case 'ends_with':
                return substr($recordValue, -strlen($compareValue)) === $compareValue;
            
            case 'in':
                $values = is_array($compareValue) ? $compareValue : explode(',', $compareValue);
                return in_array($recordValue, $values);
            
            case 'not_in':
                $values = is_array($compareValue) ? $compareValue : explode(',', $compareValue);
                return !in_array($recordValue, $values);
            
            case 'is_null':
                return $recordValue === null || $recordValue === '';
            
            case 'is_not_null':
                return $recordValue !== null && $recordValue !== '';
            
            default:
                return false;
        }
    }

    /**
     * Calculate COUNT
     * Dioptimasi: untuk count tanpa group by, langsung hitung dari database
     */
    protected function calculateCount($config)
    {
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Optimasi: langsung hitung dari database tanpa load records
            $rowCount = $this->datasetRecordModel->getRowCount($this->dataset['id']);
            
            return [
                [
                    'label' => 'Total',
                    'value' => $rowCount
                ]
            ];
        }

        // Group by - perlu load records
        return $this->groupAndCount($groupByFields);
    }

    /**
     * Group and count
     */
    protected function groupAndCount($groupByFields)
    {
        $grouped = $this->groupRecords($groupByFields);
        
        $result = [];
        foreach ($grouped as $key => $records) {
            $result[] = [
                'label' => $key,
                'value' => count($records)
            ];
        }

        return $result;
    }

    /**
     * Calculate SUM
     */
    protected function calculateSum($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple sum
            $sum = 0;
            foreach ($this->records as $record) {
                $sum += floatval($record[$targetField] ?? 0);
            }

            return [
                [
                    'label' => 'Total',
                    'value' => $sum
                ]
            ];
        }

        // Group by and sum
        return $this->groupAndAggregate($groupByFields, $targetField, 'sum');
    }

    /**
     * Calculate AVERAGE
     */
    protected function calculateAverage($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple average
            $sum = 0;
            $count = 0;
            
            foreach ($this->records as $record) {
                $value = floatval($record[$targetField] ?? 0);
                $sum += $value;
                $count++;
            }

            $average = $count > 0 ? $sum / $count : 0;

            return [
                [
                    'label' => 'Average',
                    'value' => round($average, 2)
                ]
            ];
        }

        // Group by and average
        return $this->groupAndAggregate($groupByFields, $targetField, 'average');
    }

    /**
     * Calculate MIN
     */
    protected function calculateMin($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple min
            $min = null;
            
            foreach ($this->records as $record) {
                $value = floatval($record[$targetField] ?? 0);
                if ($min === null || $value < $min) {
                    $min = $value;
                }
            }

            return [
                [
                    'label' => 'Minimum',
                    'value' => $min ?? 0
                ]
            ];
        }

        // Group by and min
        return $this->groupAndAggregate($groupByFields, $targetField, 'min');
    }

    /**
     * Calculate MAX
     */
    protected function calculateMax($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple max
            $max = null;
            
            foreach ($this->records as $record) {
                $value = floatval($record[$targetField] ?? 0);
                if ($max === null || $value > $max) {
                    $max = $value;
                }
            }

            return [
                [
                    'label' => 'Maximum',
                    'value' => $max ?? 0
                ]
            ];
        }

        // Group by and max
        return $this->groupAndAggregate($groupByFields, $targetField, 'max');
    }

    /**
     * Calculate PERCENTAGE
     * Jika target_field kosong, hitung berdasarkan distribusi count per group
     * Jika target_field diisi, hitung persentase dari nilai field tersebut
     */
    protected function calculatePercentage($config)
    {
        $targetField = $config['target_field'] ?? null;
        $groupByFields = $config['group_by_fields'] ?? [];

        // Validasi: percentage memerlukan group_by_fields
        if (empty($groupByFields)) {
            throw new \Exception("Percentage memerlukan group_by_fields untuk distribusi");
        }

        // Validasi: jika ada records, load dulu jika belum
        if (empty($this->records)) {
            // Tidak ada data
            return [
                [
                    'label' => 'No Data',
                    'value' => 0,
                    'count' => 0,
                    'total' => 0
                ]
            ];
        }

        $grouped = $this->groupRecords($groupByFields);
        $totalRecords = count($this->records);

        $result = [];

        if (empty($targetField)) {
            // Tanpa target_field: hitung distribusi count per group (distribution)
            foreach ($grouped as $key => $records) {
                $count = count($records);
                $percentage = $totalRecords > 0 ? ($count / $totalRecords) * 100 : 0;

                $result[] = [
                    'label' => $key,
                    'value' => round($percentage, 2),
                    'count' => $count,
                    'total' => $totalRecords
                ];
            }
        } else {
            // Dengan target_field: hitung persentase nilai field per group
            $totalValue = 0;
            foreach ($this->records as $record) {
                $totalValue += floatval($record[$targetField] ?? 0);
            }

            foreach ($grouped as $key => $records) {
                $groupSum = 0;
                foreach ($records as $record) {
                    $groupSum += floatval($record[$targetField] ?? 0);
                }
                $percentage = $totalValue > 0 ? ($groupSum / $totalValue) * 100 : 0;

                $result[] = [
                    'label' => $key,
                    'value' => round($percentage, 2),
                    'group_sum' => round($groupSum, 2),
                    'total' => round($totalValue, 2)
                ];
            }
        }

        return $result;
    }

    /**
     * Calculate RATIO
     */
    protected function calculateRatio($config)
    {
        $numeratorField = $config['calculation_config']['numerator_field'] ?? '';
        $denominatorField = $config['calculation_config']['denominator_field'] ?? '';

        if (empty($numeratorField) || empty($denominatorField)) {
            throw new \Exception("Ratio memerlukan numerator_field dan denominator_field");
        }

        $numeratorSum = 0;
        $denominatorSum = 0;

        foreach ($this->records as $record) {
            $numeratorSum += floatval($record[$numeratorField] ?? 0);
            $denominatorSum += floatval($record[$denominatorField] ?? 0);
        }

        $ratio = $denominatorSum > 0 ? $numeratorSum / $denominatorSum : 0;

        return [
            [
                'label' => "Ratio ({$numeratorField} / {$denominatorField})",
                'value' => round($ratio, 4),
                'numerator' => $numeratorSum,
                'denominator' => $denominatorSum
            ]
        ];
    }

    /**
     * Calculate GROWTH
     * Menghitung pertumbuhan antar periode (YoY, MoM, dll)
     * Jika target_field kosong, hitung berdasarkan count per periode
     */
    protected function calculateGrowth($config)
    {
        $targetField = $config['target_field'] ?? null;
        $calculationConfig = $config['calculation_config'] ?? [];
        $periodField = $calculationConfig['period_field'] ?? '';

        // Validasi: growth memerlukan period_field
        if (empty($periodField)) {
            throw new \Exception("Growth memerlukan period_field dalam calculation_config");
        }

        // Validasi: jika ada records, load dulu jika belum
        if (empty($this->records)) {
            return [
                [
                    'label' => 'No Data',
                    'value' => 0,
                    'growth' => null,
                    'growth_percentage' => null
                ]
            ];
        }

        // Group by period
        $grouped = [];
        foreach ($this->records as $record) {
            $period = $record[$periodField] ?? 'Unknown';
            
            if (empty($targetField)) {
                // Tanpa target_field: hitung count per periode
                if (!isset($grouped[$period])) {
                    $grouped[$period] = 0;
                }
                $grouped[$period] += 1;
            } else {
                // Dengan target_field: sum nilai per periode
                $value = floatval($record[$targetField] ?? 0);
                if (!isset($grouped[$period])) {
                    $grouped[$period] = 0;
                }
                $grouped[$period] += $value;
            }
        }

        // Sort by period (natural sort untuk angka dalam string)
        uksort($grouped, function($a, $b) {
            return strnatcmp($a, $b);
        });

        // Calculate growth
        $result = [];
        $previousValue = null;
        $periods = array_keys($grouped);

        foreach ($periods as $index => $period) {
            $value = $grouped[$period];
            $growth = null;
            $growthPercentage = null;

            if ($previousValue !== null && $previousValue > 0) {
                $growth = $value - $previousValue;
                $growthPercentage = ($growth / $previousValue) * 100;
            } elseif ($previousValue !== null && $previousValue == 0 && $value > 0) {
                // Dari 0 ke nilai positif
                $growth = $value;
                $growthPercentage = 100;
            }

            $result[] = [
                'label' => $period,
                'value' => round($value, 2),
                'growth' => $growth !== null ? round($growth, 2) : null,
                'growth_percentage' => $growthPercentage !== null ? round($growthPercentage, 2) : null,
                'is_first' => $index === 0
            ];

            $previousValue = $value;
        }

        return $result;
    }

    /**
     * Calculate RANKING
     * Mengurutkan data berdasarkan nilai dan memberikan ranking
     * Jika target_field kosong, ranking berdasarkan count per group
     */
    protected function calculateRanking($config)
    {
        $targetField = $config['target_field'] ?? null;
        $groupByFields = $config['group_by_fields'] ?? [];
        $order = strtoupper($config['sort_order'] ?? 'DESC'); // DESC = highest first

        // Validasi: ranking memerlukan group_by_fields atau target_field
        if (empty($groupByFields) && empty($targetField)) {
            throw new \Exception("Ranking memerlukan group_by_fields atau target_field");
        }

        // Validasi: jika ada records, load dulu jika belum
        if (empty($this->records)) {
            return [
                [
                    'rank' => 1,
                    'label' => 'No Data',
                    'value' => 0
                ]
            ];
        }

        if (empty($groupByFields)) {
            // Tanpa group by: ranking berdasarkan target_field
            if (empty($targetField)) {
                throw new \Exception("Ranking memerlukan target_field jika tanpa group_by_fields");
            }

            $result = [];
            foreach ($this->records as $record) {
                $result[] = [
                    'value' => floatval($record[$targetField] ?? 0)
                ];
            }

            // Sort dan add rank
            usort($result, function($a, $b) use ($order) {
                if ($order === 'DESC') {
                    return $b['value'] <=> $a['value'];
                } else {
                    return $a['value'] <=> $b['value'];
                }
            });

            $finalResult = [];
            foreach ($result as $index => $item) {
                $finalResult[] = [
                    'rank' => $index + 1,
                    'label' => '#' . ($index + 1),
                    'value' => $item['value']
                ];
            }

            return $finalResult;
        }

        // Dengan group by
        $grouped = $this->groupAndAggregate($groupByFields, $targetField, empty($targetField) ? 'count' : 'sum');

        // Sort by value
        usort($grouped, function($a, $b) use ($order) {
            if ($order === 'DESC') {
                return $b['value'] <=> $a['value'];
            } else {
                return $a['value'] <=> $b['value'];
            }
        });

        // Add rank
        $result = [];
        foreach ($grouped as $index => $item) {
            $result[] = [
                'rank' => $index + 1,
                'label' => $item['label'],
                'value' => $item['value']
            ];
        }

        return $result;
    }

    /**
     * Calculate CUSTOM FORMULA
     * Parse dan hitung formula custom
     * Jika target_field kosong, hitung count
     */
    protected function calculateCustomFormula($config)
    {
        $formula = $config['custom_formula'] ?? '';
        $targetField = $config['target_field'] ?? null;

        if (empty($formula)) {
            // Fallback: hitung count jika formula kosong
            $count = count($this->records);
            return [
                [
                    'label' => 'Count',
                    'value' => $count,
                    'formula' => 'COUNT(*)'
                ]
            ];
        }

        // Validasi: jika ada records, load dulu jika belum
        if (empty($this->records)) {
            return [
                [
                    'label' => 'Custom Calculation',
                    'value' => 0,
                    'formula' => $formula,
                    'note' => 'No data available'
                ]
            ];
        }

        // Parse formula dan calculate
        // Mendukung format: SUM(field), AVG(field), COUNT(*), MIN(field), MAX(field)
        // Dan operasi dasar: field1 + field2, field1 - field2, dll
        
        $result = [];
        
        // Simple formula parser
        try {
            // Cek jika formula adalah aggregation function
            if (preg_match('/^(SUM|AVG|MIN|MAX|COUNT)\((\*|[a-zA-Z_][a-zA-Z0-9_]*)\)$/i', trim($formula), $matches)) {
                $func = strtoupper($matches[1]);
                $field = $matches[2];
                
                $values = [];
                foreach ($this->records as $record) {
                    if ($field === '*') {
                        $values[] = 1;
                    } else {
                        $values[] = floatval($record[$field] ?? 0);
                    }
                }
                
                switch ($func) {
                    case 'SUM':
                        $value = array_sum($values);
                        break;
                    case 'AVG':
                        $value = count($values) > 0 ? array_sum($values) / count($values) : 0;
                        break;
                    case 'MIN':
                        $value = count($values) > 0 ? min($values) : 0;
                        break;
                    case 'MAX':
                        $value = count($values) > 0 ? max($values) : 0;
                        break;
                    case 'COUNT':
                        $value = count($values);
                        break;
                    default:
                        $value = 0;
                }
                
                $result[] = [
                    'label' => $func . '(' . $field . ')',
                    'value' => round($value, 2),
                    'formula' => $formula
                ];
            } else {
                // Basic arithmetic expression dengan field names
                // Parse field names dari formula
                preg_match_all('/\[([a-zA-Z_][a-zA-Z0-9_]*)\]/', $formula, $fieldMatches);
                
                if (!empty($fieldMatches[1])) {
                    $evaluatedFormula = $formula;
                    foreach ($fieldMatches[1] as $fieldName) {
                        $sum = 0;
                        foreach ($this->records as $record) {
                            $sum += floatval($record[$fieldName] ?? 0);
                        }
                        $avg = count($this->records) > 0 ? $sum / count($this->records) : 0;
                        $evaluatedFormula = str_replace('[' . $fieldName . ']', $avg, $evaluatedFormula);
                    }
                    
                    // Evaluate basic expression
                    $value = eval('return ' . $evaluatedFormula . ';');
                    
                    $result[] = [
                        'label' => 'Custom Calculation',
                        'value' => round($value, 2),
                        'formula' => $formula,
                        'evaluated' => $evaluatedFormula
                    ];
                } else {
                    $result[] = [
                        'label' => 'Custom Calculation',
                        'value' => 0,
                        'formula' => $formula,
                        'note' => 'Invalid formula format'
                    ];
                }
            }
        } catch (\Exception $e) {
            $result[] = [
                'label' => 'Custom Calculation',
                'value' => 0,
                'formula' => $formula,
                'error' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * Group records by fields
     */
    protected function groupRecords($groupByFields)
    {
        $grouped = [];

        foreach ($this->records as $record) {
            // Build group key
            $keyParts = [];
            foreach ($groupByFields as $field) {
                $keyParts[] = $record[$field] ?? 'Unknown';
            }
            $key = implode(' - ', $keyParts);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }

            $grouped[$key][] = $record;
        }

        return $grouped;
    }

    /**
     * Group and aggregate
     */
    protected function groupAndAggregate($groupByFields, $targetField, $aggregateType)
    {
        $grouped = $this->groupRecords($groupByFields);
        
        $result = [];
        foreach ($grouped as $key => $records) {
            $value = 0;

            switch ($aggregateType) {
                case 'sum':
                    foreach ($records as $record) {
                        $value += floatval($record[$targetField] ?? 0);
                    }
                    break;

                case 'average':
                    $sum = 0;
                    $count = count($records);
                    foreach ($records as $record) {
                        $sum += floatval($record[$targetField] ?? 0);
                    }
                    $value = $count > 0 ? $sum / $count : 0;
                    break;

                case 'min':
                    $value = null;
                    foreach ($records as $record) {
                        $recordValue = floatval($record[$targetField] ?? 0);
                        if ($value === null || $recordValue < $value) {
                            $value = $recordValue;
                        }
                    }
                    break;

                case 'max':
                    $value = null;
                    foreach ($records as $record) {
                        $recordValue = floatval($record[$targetField] ?? 0);
                        if ($value === null || $recordValue > $value) {
                            $value = $recordValue;
                        }
                    }
                    break;

                case 'count':
                    $value = count($records);
                    break;
            }

            $result[] = [
                'label' => $key,
                'value' => is_float($value) ? round($value, 2) : $value
            ];
        }

        return $result;
    }

    /**
     * Apply sorting
     */
    protected function applySorting($result, $config)
    {
        $sortBy = $config['sort_by'] ?? 'value';
        $sortOrder = strtoupper($config['sort_order'] ?? 'DESC');

        usort($result, function($a, $b) use ($sortBy, $sortOrder) {
            $aVal = $a[$sortBy] ?? 0;
            $bVal = $b[$sortBy] ?? 0;

            if ($sortOrder === 'DESC') {
                return $bVal <=> $aVal;
            } else {
                return $aVal <=> $bVal;
            }
        });

        return $result;
    }

    /**
     * Apply limit
     */
    protected function applyLimit($result, $limit)
    {
        return array_slice($result, 0, $limit);
    }

    /**
     * Format result untuk visualization
     */
    public function formatForVisualization($result, $visualizationType)
    {
        switch ($visualizationType) {
            case 'bar_chart':
            case 'line_chart':
            case 'area_chart':
                return $this->formatForChart($result);
            
            case 'pie_chart':
            case 'donut_chart':
                return $this->formatForPieChart($result);
            
            case 'kpi_card':
                return $this->formatForKPI($result);
            
            case 'table':
                return $this->formatForTable($result);
            
            default:
                return $result;
        }
    }

    /**
     * Get formatted data for visualization preview
     * Returns format compatible with Chart.js and table rendering
     */
    public function getVisualizationData($calcResult, $visualizationType, $visualizationConfig = [])
    {
        $result = $calcResult['data'] ?? [];
        $metadata = $calcResult['metadata'] ?? [];
        
        // Get colors from config or use defaults
        $colors = $visualizationConfig['colors'] ?? ['#198754', '#36A2EB', '#FFCE56', '#FF6384', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF'];
        
        // Prepare labels and values from result
        $labels = [];
        $values = [];
        
        foreach ($result as $item) {
            $labels[] = $item['label'] ?? '';
            $values[] = $item['value'] ?? 0;
        }
        
        switch ($visualizationType) {
            case 'table':
                return [
                    'chart_type' => 'table',
                    'title' => $visualizationConfig['chart_title'] ?? '',
                    'headers' => !empty($result) ? array_keys($result[0]) : [],
                    'rows' => array_map(function($item) { return array_values($item); }, $result),
                    'labels' => $labels,
                    'values' => $values
                ];
            
            case 'bar_chart':
            case 'line_chart':
            case 'area_chart':
                return [
                    'chart_type' => $visualizationType,
                    'title' => $visualizationConfig['chart_title'] ?? '',
                    'labels' => $labels,
                    'values' => $values,
                    'colors' => $colors,
                    'x_axis_label' => $visualizationConfig['x_axis_label'] ?? '',
                    'y_axis_label' => $visualizationConfig['y_axis_label'] ?? ''
                ];
            
            case 'pie_chart':
            case 'donut_chart':
                return [
                    'chart_type' => $visualizationType,
                    'title' => $visualizationConfig['chart_title'] ?? '',
                    'labels' => $labels,
                    'values' => $values,
                    'colors' => $colors
                ];
            
            case 'kpi_card':
                return [
                    'chart_type' => 'kpi_card',
                    'title' => $visualizationConfig['chart_title'] ?? '',
                    'value' => !empty($values) ? $values[0] : 0,
                    'label' => !empty($labels) ? $labels[0] : ''
                ];
            
            default:
                return [
                    'chart_type' => 'table',
                    'headers' => !empty($result) ? array_keys($result[0]) : [],
                    'rows' => array_map(function($item) { return array_values($item); }, $result),
                    'labels' => $labels,
                    'values' => $values
                ];
        }
    }

    /**
     * Format untuk chart (labels & values)
     */
    protected function formatForChart($result)
    {
        return [
            'labels' => array_column($result, 'label'),
            'values' => array_column($result, 'value')
        ];
    }

    /**
     * Format untuk pie chart
     */
    protected function formatForPieChart($result)
    {
        $formatted = [];
        foreach ($result as $item) {
            $formatted[] = [
                'name' => $item['label'],
                'value' => $item['value']
            ];
        }
        return $formatted;
    }

    /**
     * Format untuk KPI card
     */
    protected function formatForKPI($result)
    {
        if (empty($result)) {
            return ['value' => 0];
        }

        // Ambil value pertama
        return [
            'value' => $result[0]['value'] ?? 0,
            'label' => $result[0]['label'] ?? '',
            'metadata' => $result[0]
        ];
    }

    /**
     * Format untuk table
     */
    protected function formatForTable($result)
    {
        return [
            'columns' => !empty($result) ? array_keys($result[0]) : [],
            'rows' => $result
        ];
    }
}