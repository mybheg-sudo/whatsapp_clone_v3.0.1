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
    <title>Kişiler — MYBHEG</title>
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
        .contact-table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .contact-table th {
            background: #f8f9fa;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #6c757d;
            border: none;
            padding: 1rem;
        }
        .contact-table td {
            vertical-align: middle;
            padding: 0.8rem 1rem;
            border-color: #f0f2f5;
            font-size: 0.85rem;
        }
        .contact-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #1a1d21;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .search-bar {
            max-width: 350px;
        }
        .search-bar input {
            border-radius: 25px;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #e0e0e0;
            font-size: 0.85rem;
        }
        .search-bar .bi-search {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .manual-badge-on { background: #dc3545; color: white; }
        .manual-badge-off { background: #e9ecef; color: #6c757d; }
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
            <a href="contacts_page.php" class="nav-item active" title="Kişiler" style="text-decoration:none;color:inherit;">
                <i class="bi bi-people-fill"></i>
            </a>
            <a href="reports.php" class="nav-item" title="Raporlar" style="text-decoration:none;color:inherit;">
                <i class="bi bi-bar-chart-fill"></i>
            </a>
            <a href="settings.php" class="nav-item" title="Ayarlar" style="text-decoration:none;color:inherit;margin-top:auto;">
                <i class="bi bi-gear-fill"></i>
            </a>
            <div class="user-avatar" style="margin-top: 1rem;">AD</div>
        </div>

        <div class="page-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Kişiler</h4>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Tüm müşteri listesi ve yönetimi</p>
                </div>
                <div class="search-bar position-relative">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="İsim veya numara ara...">
                </div>
            </div>

            <div class="contact-table-card">
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
                            <td colspan="6" class="text-center text-muted py-4">Yükleniyor...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-muted text-center mt-3" style="font-size: 0.8rem;">
                <span id="contactCount">0</span> kişi listelendi
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
            }
        }

        function renderContacts(contacts) {
            const tbody = document.getElementById('contactsTableBody');
            document.getElementById('contactCount').textContent = contacts.length;

            if (contacts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Kişi bulunamadı</td></tr>';
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
                                <div class="contact-avatar">${initials}</div>
                                <div class="fw-bold">${c.name || 'Bilinmiyor'}</div>
                            </div>
                        </td>
                        <td><span class="text-muted">+${c.phone}</span></td>
                        <td><span class="text-muted" style="font-size: 0.8rem;">${lastMsg}</span></td>
                        <td><span class="text-muted" style="font-size: 0.8rem;">${date}</span></td>
                        <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.7rem;">AI</span></td>
                        <td>
                            <a href="index.php#contact=${c.id}" class="btn btn-sm btn-outline-primary rounded-pill" style="font-size: 0.75rem;">
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
