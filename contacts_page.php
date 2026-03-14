<?php
/**
 * MYBHEG - Kişiler Sayfası
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1E293B">
    <title>Kişiler — MYBHEG</title>
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
            /* bg-main already handles background */
        }
        
        .contact-table-card {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border-light);
            background: var(--bg-panel-solid);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            transition: var(--trans-smooth);
        }

        .contact-table-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
        }

        .contact-table th {
            background: var(--bg-main);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: var(--text-secondary);
            border: none;
            padding: 1.25rem 1.5rem;
            font-family: 'Inter', sans-serif;
        }

        .contact-table td {
            vertical-align: middle;
            padding: 1rem 1.5rem;
            border-color: var(--border-light);
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .contact-table tbody tr {
            transition: var(--trans-fast);
        }

        .contact-table tbody tr:hover {
            background-color: rgba(241, 245, 249, 0.5);
        }

        .contact-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px; /* Squircle */
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
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
            <a href="contacts_page.php" class="nav-item active" title="Kişiler">
                <i class="bi bi-people-fill"></i>
            </a>
            <a href="reports.php" class="nav-item" title="Raporlar">
                <i class="bi bi-bar-chart-fill"></i>
            </a>
            <a href="settings.php" class="nav-item" title="Ayarlar" style="margin-top:auto;">
                <i class="bi bi-gear-fill"></i>
            </a>
            <button class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Tema Değiştir">
                <i class="bi bi-moon-fill"></i>
            </button>
            <div class="user-avatar mt-2 mb-2" title="Profil">AD</div>
        </div>

        <div class="page-container position-relative">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Kişiler</h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Tüm müşteri listesi ve yönetimi</p>
                </div>
                <div class="search-box m-0" style="width: 350px;">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="search-input" id="searchInput" placeholder="İsim veya numara ara...">
                </div>
            </div>

            <div class="contact-table-card glass-panel" style="border-radius: 20px;">
                <div class="table-responsive">
                    <table class="table contact-table mb-0">
                        <thead>
                            <tr>
                                <th>Kişi</th>
                                <th>Telefon</th>
                                <th>Son Mesaj</th>
                                <th>Tarih</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="contactsTableBody">
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2 opacity-75">
                                        <div class="skeleton skeleton-avatar" style="border-radius: 12px;"></div>
                                        <div class="skeleton skeleton-text title m-0" style="width: 100px;"></div>
                                    </div>
                                </td>
                                <td><div class="skeleton skeleton-text opacity-75" style="width: 80px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-75" style="width: 150px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-75" style="width: 70px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-75" style="width: 60px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-75" style="width: 30px;"></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2 opacity-50">
                                        <div class="skeleton skeleton-avatar" style="border-radius: 12px;"></div>
                                        <div class="skeleton skeleton-text title m-0" style="width: 80px;"></div>
                                    </div>
                                </td>
                                <td><div class="skeleton skeleton-text opacity-50" style="width: 90px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-50" style="width: 120px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-50" style="width: 60px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-50" style="width: 50px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-50" style="width: 30px;"></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2 opacity-25">
                                        <div class="skeleton skeleton-avatar" style="border-radius: 12px;"></div>
                                        <div class="skeleton skeleton-text title m-0" style="width: 110px;"></div>
                                    </div>
                                </td>
                                <td><div class="skeleton skeleton-text opacity-25" style="width: 75px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-25" style="width: 180px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-25" style="width: 65px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-25" style="width: 55px;"></div></td>
                                <td><div class="skeleton skeleton-text opacity-25" style="width: 30px;"></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-muted text-center mt-4 fw-medium" style="font-size: 0.85rem;">
                Toplam <span id="contactCount" class="text-dark fw-bold">0</span> kişi listelendi
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function escapeHtml(unsafe) {
        return (unsafe || '').toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    document.addEventListener('DOMContentLoaded', async function() {
        const token = localStorage.getItem('mybheg_auth_token') || sessionStorage.getItem('mybheg_auth_token');
        if (!token) { window.location.href = 'login.php'; return; }

        let allContacts = [];

        async function loadContacts() {
            try {
                const res = await fetch('/api/contacts.php', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                allContacts = await res.json();
                renderContacts(allContacts);
            } catch (err) {
                console.error('Contacts error:', err);
                // Fallback empty state
                document.getElementById('contactsTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-exclamation-triangle fs-3 text-warning"></i><br>Veriler yüklenemedi.</td></tr>';
            }
        }

        function renderContacts(contacts) {
            const tbody = document.getElementById('contactsTableBody');
            document.getElementById('contactCount').textContent = contacts.length;

            if (contacts.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-5 opacity-50">
                            <i class="bi bi-people mb-2 d-block" style="font-size: 2.5rem; color: var(--text-light);"></i>
                            <p class="text-muted mb-0">Henüz kayıtlı müşteri yok.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = '';
            contacts.forEach(c => {
                const initials = (c.name || 'M').substring(0, 2).toUpperCase();
                const lastMsg = c.last_message ? (c.last_message.length > 50 ? c.last_message.substring(0, 50) + '...' : c.last_message) : '—';
                const date = c.last_message_time ? new Date(c.last_message_time).toLocaleDateString('tr-TR') : '—';

                tbody.innerHTML += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="contact-avatar">${escapeHtml(initials)}</div>
                                <div class="fw-bold">${escapeHtml(c.name || 'Bilinmiyor')}</div>
                            </div>
                        </td>
                        <td><span class="text-muted">+${escapeHtml(c.phone)}</span></td>
                        <td><span class="text-muted" style="font-size: 0.8rem;">${escapeHtml(lastMsg)}</span></td>
                        <td><span class="text-muted" style="font-size: 0.8rem;">${escapeHtml(date)}</span></td>
                        <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.7rem;">AI</span></td>
                        <td>
                            <a href="index.php#contact=${escapeHtml(c.id)}" class="btn btn-sm btn-outline-primary rounded-pill" style="font-size: 0.75rem;">
                                <i class="bi bi-chat"></i> Aç
                            </a>
                        </td>
                    </tr>
                `;
            });
        }

        // Search
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const filtered = allContacts.filter(c =>
                (c.name && c.name.toLowerCase().includes(query)) ||
                (c.phone && c.phone.includes(query))
            );
            renderContacts(filtered);
        });

        loadContacts();
    });
    </script>
</body>
</html>
