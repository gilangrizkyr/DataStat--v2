<?php

/**
 * ============================================================================
 * DASHBOARD WIDGET CONTROLLER
 * ============================================================================
 *
 * Path: app/Controllers/Owner/DashboardWidgetController.php
 *
 * Deskripsi:
 * Controller untuk mengelola widgets di dashboard.
 * Menangani operasi CRUD untuk manual widgets dan statistic widgets.
 *
 * Fitur:
 * - Add manual widget
 * - Update widget
 * - Delete widget
 * - Update position/layout
 * - Toggle visibility
 * - Duplicate widget
 *
 * Role: Owner
 * ============================================================================
 */

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\Owner\DashboardWidgetModel;
use App\Models\Owner\StatisticConfigModel;

class DashboardWidgetController extends BaseController
{
    protected $widgetModel;
    protected $statisticModel;

    public function __construct()
    {
        $this->widgetModel = new DashboardWidgetModel();
        $this->statisticModel = new StatisticConfigModel();
        helper(['form', 'url']);
    }

    /**
     * Add widget to dashboard
     */
    public function add()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        // Validate dashboard ownership
        $dashboardId = $this->request->getPost('dashboard_id');
        $dashboardModel = new \App\Models\Owner\DashboardModel();
        $dashboard = $dashboardModel->where('id', $dashboardId)
                                   ->where('application_id', $applicationId)
                                   ->first();

        if (!$dashboard) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dashboard tidak ditemukan']);
        }

        $rules = [
            'dashboard_id' => 'required|integer',
            'widget_type' => 'required|in_list[manual,statistic]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $this->validator->getErrors())
            ]);
        }

        try {
            $widgetType = $this->request->getPost('widget_type');
            
            // Validate statistic_id for statistic widgets
            if ($widgetType === 'statistic') {
                $statisticId = $this->request->getPost('statistic_id');
                if (!$statisticId) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Statistic ID wajib diisi untuk widget statistik'
                    ]);
                }
            }

            if ($widgetType === 'manual') {
                // Manual widget
                $widgetData = [
                    'dashboard_id' => $dashboardId,
                    'statistic_config_id' => null,
                    'widget_title' => $this->request->getPost('widget_title'),
                    'widget_content' => $this->request->getPost('widget_content'),
                    'widget_type' => 'manual',
                    'width' => 12,
                    'height' => 200,
                    'sort_order' => $this->request->getPost('sort_order') ?? $this->getNextSortOrder($dashboardId),
                    'is_visible' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            } else {
                // Statistic widget
                $statisticId = $this->request->getPost('statistic_id');
                if (!$statisticId) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Statistic ID required']);
                }

                // Check if statistic exists and belongs to application
                $statistic = $this->statisticModel->where('id', $statisticId)
                                                 ->where('application_id', $applicationId)
                                                 ->first();
                if (!$statistic) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Statistik tidak ditemukan']);
                }

                // Check if statistic already exists in dashboard
                if ($this->widgetModel->isStatisticExists($dashboardId, $statisticId)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Statistik sudah ada di dashboard']);
                }

                $widgetData = [
                    'dashboard_id' => $dashboardId,
                    'statistic_config_id' => $statisticId,
                    'widget_title' => $statistic['stat_name'],
                    'widget_type' => 'statistic',
                    'width' => 6,
                    'height' => 300,
                    'sort_order' => $this->request->getPost('sort_order') ?? $this->getNextSortOrder($dashboardId),
                    'is_visible' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            $widgetId = $this->widgetModel->insert($widgetData);

            $this->logActivity('create', 'dashboard_widgets', 'Owner add widget ke dashboard', [
                'widget_id' => $widgetId,
                'widget_type' => $widgetType
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Widget berhasil ditambahkan',
                'widget_id' => $widgetId
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update widget
     */
    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        // Validate widget ownership
        $widget = $this->widgetModel->select('dashboard_widgets.*, dashboards.application_id')
                                   ->join('dashboards', 'dashboards.id = dashboard_widgets.dashboard_id')
                                   ->where('dashboard_widgets.id', $id)
                                   ->where('dashboards.application_id', $applicationId)
                                   ->first();

        if (!$widget) {
            return $this->response->setJSON(['success' => false, 'message' => 'Widget tidak ditemukan']);
        }

        try {
            $updateData = [
                'widget_title' => $this->request->getPost('widget_title'),
                'widget_content' => $this->request->getPost('widget_content'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->widgetModel->update($id, $updateData);

            $this->logActivity('update', 'dashboard_widgets', 'Owner update widget', ['widget_id' => $id]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Widget berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete widget
     */
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        // Validate widget ownership
        $widget = $this->widgetModel->select('dashboard_widgets.*, dashboards.application_id')
                                   ->join('dashboards', 'dashboards.id = dashboard_widgets.dashboard_id')
                                   ->where('dashboard_widgets.id', $id)
                                   ->where('dashboards.application_id', $applicationId)
                                   ->first();

        if (!$widget) {
            return $this->response->setJSON(['success' => false, 'message' => 'Widget tidak ditemukan']);
        }

        try {
            $this->widgetModel->delete($id);

            $this->logActivity('delete', 'dashboard_widgets', 'Owner delete widget', ['widget_id' => $id]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Widget berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update widget position
     */
    public function updatePosition($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        // Validate widget ownership
        $widget = $this->widgetModel->select('dashboard_widgets.*, dashboards.application_id')
                                   ->join('dashboards', 'dashboards.id = dashboard_widgets.dashboard_id')
                                   ->where('dashboard_widgets.id', $id)
                                   ->where('dashboards.application_id', $applicationId)
                                   ->first();

        if (!$widget) {
            return $this->response->setJSON(['success' => false, 'message' => 'Widget tidak ditemukan']);
        }

        try {
            $updateData = [
                'position_x' => $this->request->getPost('position_x'),
                'position_y' => $this->request->getPost('position_y'),
                'width' => $this->request->getPost('width'),
                'height' => $this->request->getPost('height'),
                'sort_order' => $this->request->getPost('sort_order'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->widgetModel->update($id, $updateData);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Posisi widget berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Batch update positions
     */
    public function batchUpdatePositions()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $widgets = $this->request->getJSON(true);
        $applicationId = session()->get('application_id');

        try {
            foreach ($widgets as $widgetData) {
                if (!isset($widgetData['id'])) continue;

                // Validate ownership
                $widget = $this->widgetModel->select('dashboard_widgets.*, dashboards.application_id')
                                           ->join('dashboards', 'dashboards.id = dashboard_widgets.dashboard_id')
                                           ->where('dashboard_widgets.id', $widgetData['id'])
                                           ->where('dashboards.application_id', $applicationId)
                                           ->first();

                if ($widget) {
                    $updateData = array_intersect_key($widgetData, array_flip(['sort_order']));
                    if (!empty($updateData)) {
                        $updateData['updated_at'] = date('Y-m-d H:i:s');
                        $this->widgetModel->update($widgetData['id'], $updateData);
                    }
                }
            }

            $this->logActivity('update', 'dashboard_widgets', 'Owner batch update widget positions', []);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Layout berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle widget visibility
     */
    public function toggleVisibility($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        // Validate widget ownership
        $widget = $this->widgetModel->select('dashboard_widgets.*, dashboards.application_id')
                                   ->join('dashboards', 'dashboards.id = dashboard_widgets.dashboard_id')
                                   ->where('dashboard_widgets.id', $id)
                                   ->where('dashboards.application_id', $applicationId)
                                   ->first();

        if (!$widget) {
            return $this->response->setJSON(['success' => false, 'message' => 'Widget tidak ditemukan']);
        }

        try {
            $this->widgetModel->toggleVisibility($id);

            $this->logActivity('update', 'dashboard_widgets', 'Owner toggle widget visibility', ['widget_id' => $id]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Visibility berhasil diubah'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Duplicate widget
     */
    public function duplicate($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        // Validate widget ownership
        $widget = $this->widgetModel->select('dashboard_widgets.*, dashboards.application_id')
                                   ->join('dashboards', 'dashboards.id = dashboard_widgets.dashboard_id')
                                   ->where('dashboard_widgets.id', $id)
                                   ->where('dashboards.application_id', $applicationId)
                                   ->first();

        if (!$widget) {
            return $this->response->setJSON(['success' => false, 'message' => 'Widget tidak ditemukan']);
        }

        try {
            $newWidgetId = $this->widgetModel->duplicate($id);

            $this->logActivity('create', 'dashboard_widgets', 'Owner duplicate widget', [
                'original_widget_id' => $id,
                'new_widget_id' => $newWidgetId
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Widget berhasil diduplikasi',
                'widget_id' => $newWidgetId
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get next sort order for dashboard
     */
    private function getNextSortOrder($dashboardId)
    {
        $maxOrder = $this->widgetModel->selectMax('sort_order')
                                     ->where('dashboard_id', $dashboardId)
                                     ->first();
        return ($maxOrder['sort_order'] ?? 0) + 1;
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