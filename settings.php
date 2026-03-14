<?php
/**
 * MYBHEG - Ayarlar Sayfası
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1E293B">
    <title>Ayarlar — MYBHEG</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="manifest" href="manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/i18n.js"></script>
    <style>
        .page-container {
            flex: 1;
            padding: 2.5rem;
            overflow-y: auto;
            /* bg-main handles background */
        }
        .settings-card {
            border-radius: 20px;
            padding: 2rem;
            background: var(--bg-panel-solid);
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            margin-bottom: 1.5rem;
            transition: var(--trans-smooth);
        }
        .settings-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.04);
            transform: translateY(-2px);
        }
        .settings-card h6 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .settings-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px dashed var(--border-light);
            transition: var(--trans-fast);
        }
        .settings-item:hover {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            background: rgba(241, 245, 249, 0.3);
            border-radius: 8px;
        }
        .settings-item:last-child { border-bottom: 0; }
        .settings-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .settings-value {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        .settings-value code {
            font-family: 'Inter', monospace;
            background: rgba(37, 99, 235, 0.08);
            color: var(--brand-primary);
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.5);
        }
        .status-online { background: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
        .status-offline { background: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2); }
        .status-unknown { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2); }
    </style>
</head>
<body>
    <div class="app-container">

        <!-- Sidebar -->
        <div class="sidebar-nav">
            <div class="sidebar-logo">MY<br>BHEG</div>
            <a href="index.php" class="nav-item" title="Gelen Kutusu">
                <i class="bi bi-chat-left-text-fill"></i>
            </a>
            <a href="contacts_page.php" class="nav-item" title="Kişiler">
                <i class="bi bi-people-fill"></i>
            </a>
            <a href="reports.php" class="nav-item" title="Raporlar">
                <i class="bi bi-bar-chart-fill"></i>
            </a>
            <a href="settings.php" class="nav-item active" title="Ayarlar" style="margin-top:auto;">
                <i class="bi bi-gear-fill"></i>
            </a>
            <button class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Tema Değiştir">
                <i class="bi bi-moon-fill"></i>
            </button>
            <div class="user-avatar mt-2 mb-2" title="Profil">AD</div>
        </div>

        <div class="page-container position-relative">
            <div class="mb-5">
                <h4 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Ayarlar</h4>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Sistem yapılandırması ve bağlantı durumu</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <!-- Profil -->
                    <div class="settings-card glass-panel">
                        <h6 class="text-primary"><i class="bi bi-person-fill"></i>Profil Bilgileri</h6>
                        <div class="settings-item mt-3">
                            <span class="settings-label"><i class="bi bi-person-badge"></i> Kullanıcı Adı</span>
                            <span class="settings-value" id="settingsUsername">—</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label"><i class="bi bi-telephone"></i> Sistem Telefonu</span>
                            <span class="settings-value fw-bold text-dark" id="settingsPhone">—</span>
                        </div>
                    </div>

                    <!-- Bağlantı Durumu -->
                    <div class="settings-card glass-panel">
                        <h6 class="text-warning"><i class="bi bi-broadcast"></i>Bağlantı Durumu</h6>
                        <div class="settings-item mt-3">
                            <span class="settings-label">
                                <span class="status-dot status-unknown" id="n8nStatusDot"></span>
                                n8n Sunucusu
                            </span>
                            <span class="settings-value" id="n8nStatus">Kontrol ediliyor...</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">
                                <span class="status-dot status-unknown" id="mysqlStatusDot"></span>
                                MySQL Veritabanı
                            </span>
                            <span class="settings-value" id="mysqlStatus">Kontrol ediliyor...</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">
                                <span class="status-dot status-unknown" id="pgStatusDot"></span>
                                PostgreSQL (n8n)
                            </span>
                            <span class="settings-value" id="pgStatus">Kontrol ediliyor...</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- API Bilgileri -->
                    <div class="settings-card glass-panel h-100 d-flex flex-column">
                        <h6 class="text-success"><i class="bi bi-hdd-network-fill"></i>API Yapılandırması</h6>
                        <div class="settings-item mt-3">
                            <span class="settings-label">n8n URL</span>
                            <span class="settings-value"><code>n8n.motomotomasyon.com</code></span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Mesaj Gönderme</span>
                            <span class="settings-value"><code>/webhook/send-whatsapp</code></span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Manuel Liste</span>
                            <span class="settings-value"><code>/webhook/add-manual-list</code></span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Sipariş Sorgu</span>
                            <span class="settings-value"><code>/webhook/get-orders</code></span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Durum Sorgu</span>
                            <span class="settings-value"><code>/webhook/get-contact-status</code></span>
                        </div>

                        <!-- Oturum -->
                        <div class="mt-auto pt-4">
                            <hr class="mb-4 text-muted border-dashed" style="opacity: 0.15">
                            <button class="btn btn-outline-danger btn-lg rounded-3 w-100 fw-bold shadow-sm" id="logoutBtn" style="letter-spacing: 0.5px;">
                                <i class="bi bi-box-arrow-right me-2"></i> Sistemden Çıkış Yap
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('mybheg_auth_token') || sessionStorage.getItem('mybheg_auth_token');
        if (!token) { window.location.href = 'login.php'; return; }

        // Profil bilgileri
        const userData = JSON.parse(localStorage.getItem('mybheg_user') || sessionStorage.getItem('mybheg_user') || '{}');
        document.getElementById('settingsUsername').textContent = userData.username || 'admin1';
        document.getElementById('settingsPhone').textContent = userData.systemPhone ? `+${userData.systemPhone}` : '+905419682572';

        // n8n health check
        try {
            const res = await fetch('https://n8n.motomotomasyon.com/healthz', { mode: 'no-cors' });
            document.getElementById('n8nStatus').textContent = 'Çevrimiçi';
            document.getElementById('n8nStatusDot').className = 'status-dot status-online';
        } catch {
            document.getElementById('n8nStatus').textContent = 'Bağlantı sorunu';
            document.getElementById('n8nStatusDot').className = 'status-dot status-offline';
        }

        // MySQL check (via contacts API)
        try {
            const res = await fetch('/api/contacts.php', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                document.getElementById('mysqlStatus').textContent = 'Çevrimiçi';
                document.getElementById('mysqlStatusDot').className = 'status-dot status-online';
            } else {
                document.getElementById('mysqlStatus').textContent = 'Bağlantı sorunu';
                document.getElementById('mysqlStatusDot').className = 'status-dot status-offline';
            }
        } catch {
            document.getElementById('mysqlStatus').textContent = 'Erişilemez';
            document.getElementById('mysqlStatusDot').className = 'status-dot status-offline';
        }

        // PG check (via contact_status API)
        try {
            const res = await fetch('/api/contact_status.php?phone=test', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                document.getElementById('pgStatus').textContent = 'Çevrimiçi (webhook)';
                document.getElementById('pgStatusDot').className = 'status-dot status-online';
            } else {
                document.getElementById('pgStatus').textContent = 'Bağlantı sorunu';
                document.getElementById('pgStatusDot').className = 'status-dot status-offline';
            }
        } catch {
            document.getElementById('pgStatus').textContent = 'Erişilemez';
            document.getElementById('pgStatusDot').className = 'status-dot status-offline';
        }

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', function() {
            localStorage.removeItem('mybheg_auth_token');
            localStorage.removeItem('mybheg_user');
            sessionStorage.removeItem('mybheg_auth_token');
            sessionStorage.removeItem('mybheg_user');
            window.location.href = 'login.php';
        });
    });
    </script>
</body>
</html>
