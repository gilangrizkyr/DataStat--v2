<?= $this->extend('layouts/owner'); ?>

<?= $this->section('css'); ?>
<style>
    .dashboard-settings { padding: 20px; }
    .settings-header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef; }
    .settings-header h2 { font-size: 1.5rem; font-weight: 600; color: #198754; margin-bottom: 5px; }
    .settings-header .breadcrumb { font-size: 0.9rem; color: #6c757d; margin-bottom: 0; }
    .settings-card { background: white; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); }
    .settings-card .card-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f8f9fa; border-bottom: 1px solid #e9ecef; }
    .settings-card .card-header h5 { font-size: 1rem; font-weight: 600; margin: 0; }
    .settings-card .card-body { padding: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 500; margin-bottom: 5px; color: #495057; }
    .form-group .form-text { font-size: 0.85rem; color: #6c757d; }
    .form-check { margin-bottom: 10px; }
    .form-check-input:checked { background-color: #198754; border-color: #198754; }
    .switch-status { display: flex; align-items: center; gap: 10px; }
    .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
    .status-badge.active { background: #d1e7dd; color: #0a5132; }
    .status-badge.inactive { background: #f8d7da; color: #842029; }
    .action-buttons { display: flex; gap: 10px; margin-top: 20px; }
    .info-list { list-style: none; padding: 0; margin: 0; }
    .info-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f3f5; }
    .info-list li:last-child { border-bottom: none; }
    .info-list li strong { color: #495057; }
    .token-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 12px; font-family: monospace; word-break: break-all; }
    .danger-zone { border: 1px solid #f8d7da; background: #fff5f5; }
    .danger-zone .card-header { background: #f8d7da; border-bottom-color: #f5c2c7; }
    .danger-zone h5 { color: #842029; }
    .api-endpoint { background: #f8f9fa; border-radius: 6px; padding: 15px; margin-top: 10px; }
    .api-endpoint code { display: block; word-break: break-all; color: #d63384; }
    @media (max-width: 768px) {
        .dashboard-settings { padding: 10px; }
        .action-buttons { flex-direction: column; }
        .action-buttons .btn { width: 100%; }
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div class="dashboard-settings">
    <div class="settings-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('owner/dashboards'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('owner/dashboards/manage/' . $dashboard['id']); ?>"><?= esc($dashboard['dashboard_name']); ?></a></li>
                <li class="breadcrumb-item active">Pengaturan</li>
            </ol>
        </nav>
        <h2><i class="bi bi-gear me-2"></i>Pengaturan Dashboard</h2>
        <p class="text-muted mb-0">Kelola pengaturan dan konfigurasi dashboard Anda</p>
    </div>

    <!-- Basic Settings -->
    <div class="settings-card">
        <div class="card-header">
            <h5><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h5>
            <span class="status-badge <?= $dashboard['is_active'] ?? true ? 'active' : 'inactive'; ?>">
                <?= $dashboard['is_active'] ?? true ? 'Aktif' : 'Tidak Aktif'; ?>
            </span>
        </div>
        <div class="card-body">
            <ul class="info-list">
                <li>
                    <strong>Nama Dashboard</strong>
                    <span><?= esc($dashboard['dashboard_name']); ?></span>
                </li>
                <li>
                    <strong>Slug</strong>
                    <span><code><?= esc($dashboard['dashboard_slug']); ?></code></span>
                </li>
                <li>
                    <strong>Deskripsi</strong>
                    <span><?= !empty($dashboard['description']) ? esc($dashboard['description']) : '-'; ?></span>
                </li>
                <li>
                    <strong>Dibuat</strong>
                    <span><?= date('d M Y H:i', strtotime($dashboard['created_at'])); ?></span>
                </li>
                <li>
                    <strong>Terakhir Diupdate</strong>
                    <span><?= !empty($dashboard['updated_at']) ? date('d M Y H:i', strtotime($dashboard['updated_at'])) : '-'; ?></span>
                </li>
            </ul>
            <div class="action-buttons">
                <a href="<?= base_url('owner/dashboards/edit/' . $dashboard['id']); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-2"></i>Edit Informasi
                </a>
            </div>
        </div>
    </div>

    <!-- Public Access Settings -->
    <div class="settings-card">
        <div class="card-header">
            <h5><i class="bi bi-globe me-2"></i>Akses Publik</h5>
        </div>
        <div class="card-body">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="isPublicToggle" 
                    <?= $dashboard['is_public'] ? 'checked' : ''; ?> 
                    onchange="togglePublic(<?= $dashboard['id']; ?>)">
                <label class="form-check-label" for="isPublicToggle">
                    <?= $dashboard['is_public'] ? 'Publik (Siapa pun dapat melihat)' : 'Privat (Hanya anggota tim)'; ?>
                </label>
            </div>
            
            <?php if ($dashboard['is_public']): ?>
                <div class="api-endpoint mt-3">
                    <label class="mb-2"><strong>URL Akses Publik:</strong></label>
                    <code><?= base_url('viewer/public/' . $dashboard['dashboard_slug']); ?></code>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyPublicUrl()">
                            <i class="bi bi-clipboard me-1"></i>Salin URL
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Widget Count -->
    <div class="settings-card">
        <div class="card-header">
            <h5><i class="bi bi-grid-3x3-gap me-2"></i>Widget</h5>
        </div>
        <div class="card-body">
            <p>Dashboard ini memiliki <strong><?= $widgetCount ?? 0; ?></strong> widget.</p>
            <div class="action-buttons">
                <a href="<?= base_url('owner/dashboards/manage/' . $dashboard['id']); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-columns-gap me-2"></i>Kelola Widget
                </a>
                <a href="<?= base_url('owner/dashboards/preview/' . $dashboard['id']); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-eye me-2"></i>Lihat Preview
                </a>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="settings-card danger-zone">
        <div class="card-header">
            <h5><i class="bi bi-exclamation-triangle me-2"></i>Zona Bahaya</h5>
        </div>
        <div class="card-body">
            <p>Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan.</p>
            <div class="action-buttons">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $dashboard['id']; ?>)">
                    <i class="bi bi-trash me-2"></i>Hapus Dashboard
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function togglePublic(id) {
    fetch(`<?= base_url('owner/dashboards/toggle-public/'); ?>${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal mengubah status publik');
            document.getElementById('isPublicToggle').checked = !document.getElementById('isPublicToggle').checked;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
        document.getElementById('isPublicToggle').checked = !document.getElementById('isPublicToggle').checked;
    });
}

function copyPublicUrl() {
    const url = `<?= base_url('viewer/public/' . ($dashboard['dashboard_slug'] ?? '')); ?>`;
    navigator.clipboard.writeText(url).then(() => {
        alert('URL berhasil disalin ke clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
        prompt('Salin URL ini:', url);
    });
}

function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus dashboard ini? Tindakan ini tidak dapat dibatalkan.')) {
        window.location.href = `<?= base_url('owner/dashboards/delete/'); ?>${id}`;
    }
}
</script>

<?= $this->endSection(); ?>

