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
    <title>Ayarlar — MYBHEG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .page-container {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: #f0f2f5;
        }
        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 1rem;
        }
        .settings-card h6 {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1a1d21;
        }
        .settings-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f2f5;
        }
        .settings-item:last-child { border-bottom: 0; }
        .settings-label {
            font-size: 0.85rem;
            font-weight: 500;
        }
        .settings-value {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .status-online { background: #10b981; }
        .status-offline { background: #ef4444; }
        .status-unknown { background: #f59e0b; }
    </style>
</head>
<body>
    <div class="app-container">

        <!-- Sidebar -->
        <div class="sidebar-nav">
            <div class="sidebar-logo">MY<br>BHEG</div>
            <a href="index.php" class="nav-item" title="Gelen Kutusu" style="text-decoration:none;color:inherit;">
                <i class="bi bi-chat-left-text-fill"></i>
            </a>
            <a href="contacts_page.php" class="nav-item" title="Kişiler" style="text-decoration:none;color:inherit;">
                <i class="bi bi-people-fill"></i>
            </a>
            <a href="reports.php" class="nav-item" title="Raporlar" style="text-decoration:none;color:inherit;">
                <i class="bi bi-bar-chart-fill"></i>
            </a>
            <a href="settings.php" class="nav-item active" title="Ayarlar" style="text-decoration:none;color:inherit;margin-top:auto;">
                <i class="bi bi-gear-fill"></i>
            </a>
            <div class="user-avatar" style="margin-top: 1rem;">AD</div>
        </div>

        <div class="page-container">
            <div class="mb-4">
                <h4 class="fw-bold mb-0">Ayarlar</h4>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Sistem yapılandırması ve bağlantı durumu</p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <!-- Profil -->
                    <div class="settings-card">
                        <h6><i class="bi bi-person-circle me-2"></i>Profil Bilgileri</h6>
                        <div class="settings-item">
                            <span class="settings-label">Kullanıcı Adı</span>
                            <span class="settings-value" id="settingsUsername">—</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Sistem Telefonu</span>
                            <span class="settings-value" id="settingsPhone">—</span>
                        </div>
                    </div>

                    <!-- Bağlantı Durumu -->
                    <div class="settings-card">
                        <h6><i class="bi bi-plug me-2"></i>Bağlantı Durumu</h6>
                        <div class="settings-item">
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
                    <div class="settings-card">
                        <h6><i class="bi bi-code-slash me-2"></i>API Yapılandırması</h6>
                        <div class="settings-item">
                            <span class="settings-label">n8n URL</span>
                            <span class="settings-value"><code>n8n.motomotomasyon.com</code></span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Mesaj Gönderme</span>
                            <span class="settings-value"><code>/webhook/send-whatsapp</code></span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Manuel Liste Ekle</span>
                            <span class="settings-value"><code>/webhook/add-manual-list</code></span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Sipariş Sorgula</span>
                            <span class="settings-value"><code>/webhook/get-orders</code></span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-label">Durum Sorgula</span>
                            <span class="settings-value"><code>/webhook/get-contact-status</code></span>
                        </div>
                    </div>

                    <!-- Oturum -->
                    <div class="settings-card">
                        <h6><i class="bi bi-box-arrow-right me-2"></i>Oturum</h6>
                        <button class="btn btn-outline-danger rounded-pill w-100" id="logoutBtn">
                            <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
        if (!token) { window.location.href = 'login.php'; return; }

        // Profil bilgileri
        const userData = JSON.parse(localStorage.getItem('user_data') || sessionStorage.getItem('user_data') || '{}');
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
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            sessionStorage.removeItem('auth_token');
            sessionStorage.removeItem('user_data');
            window.location.href = 'login.php';
        });
    });
    </script>
</body>
</html>
