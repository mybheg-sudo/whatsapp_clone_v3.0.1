<?php
/**
 * MYBHEG - Raporlar Sayfası
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1E293B">
    <title>Raporlar — MYBHEG</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .reports-container {
            flex: 1;
            padding: 2.5rem;
            overflow-y: auto;
            /* background is handled by style.css --bg-main */
        }
        .stat-card {
            border-radius: 20px;
            padding: 1.75rem;
            background: var(--bg-panel-solid);
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            transition: var(--trans-smooth);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand-primary), var(--accent-color));
            opacity: 0;
            transition: var(--trans-smooth);
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .stat-card:hover::before {
            opacity: 1;
        }
        .stat-value {
            font-size: 2.2rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            line-height: 1.1;
            color: var(--text-primary) !important;
        }
        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            font-weight: 600;
        }
        .stat-sub {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .chart-card {
            background: var(--bg-panel-solid);
            border: 1px solid var(--border-light);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
        .chart-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .top-contact-item {
            display: flex;
            align-items: center;
            padding: 0.85rem 0;
            border-bottom: 1px dashed var(--border-light);
            transition: var(--trans-fast);
        }
        .top-contact-item:hover {
            padding-left: 0.5rem;
            background: rgba(241, 245, 249, 0.3);
            border-radius: 8px;
        }
        .top-contact-item:last-child { border-bottom: 0; }
        
        .contact-rank {
            width: 32px;
            height: 32px;
            border-radius: 10px; /* Squircle */
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            margin-right: 15px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .rank-1 { background: linear-gradient(135deg, #FFD700, #FBBF24); color: #78350F; }
        .rank-2 { background: linear-gradient(135deg, #E2E8F0, #CBD5E1); color: #334155; }
        .rank-3 { background: linear-gradient(135deg, #FDBA74, #FB923C); color: #7C2D12; }
        .rank-default { background: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-light); box-shadow: none; }
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
            <a href="reports.php" class="nav-item active" title="Raporlar">
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

        <!-- Reports Content -->
        <div class="reports-container position-relative">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Raporlar</h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Mesaj istatistikleri ve performans analizi</p>
                </div>
                <span class="badge bg-white text-dark border rounded-pill px-3 py-2 shadow-sm" id="lastUpdate">
                    <span class="spinner-border spinner-border-sm me-1 text-primary" role="status" style="width: 12px; height: 12px; border-width: 2px;"></span> Güncelleniyor...
                </span>
            </div>

            <!-- Stat Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card glass-panel">
                        <div class="stat-label mb-2">Bugün</div>
                        <div class="stat-value" id="todayTotal"><div class="skeleton skeleton-text d-inline-block m-0" style="height: 38px; width: 80px;"></div></div>
                        <div class="stat-sub mt-2 d-flex justify-content-between align-items-center">
                            <span class="text-success fw-bold d-flex align-items-center gap-1"><i class="bi bi-arrow-up-right"></i><span id="todaySent"><div class="skeleton skeleton-text d-inline-block m-0" style="width:20px;"></div></span> giden</span>
                            <span class="text-info fw-bold d-flex align-items-center gap-1"><i class="bi bi-arrow-down-left"></i><span id="todayReceived"><div class="skeleton skeleton-text d-inline-block m-0" style="width:20px;"></div></span> gelen</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card glass-panel">
                        <div class="stat-label mb-2">Bu Hafta</div>
                        <div class="stat-value" id="weekTotal"><div class="skeleton skeleton-text d-inline-block m-0" style="height: 38px; width: 80px;"></div></div>
                        <div class="stat-sub mt-2 d-flex justify-content-between align-items-center">
                            <span class="text-success fw-bold d-flex align-items-center gap-1"><i class="bi bi-arrow-up-right"></i><span id="weekSent"><div class="skeleton skeleton-text d-inline-block m-0" style="width:20px;"></div></span> giden</span>
                            <span class="text-info fw-bold d-flex align-items-center gap-1"><i class="bi bi-arrow-down-left"></i><span id="weekReceived"><div class="skeleton skeleton-text d-inline-block m-0" style="width:20px;"></div></span> gelen</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card glass-panel">
                        <div class="stat-label mb-2">Bu Ay</div>
                        <div class="stat-value" id="monthTotal"><div class="skeleton skeleton-text d-inline-block m-0" style="height: 38px; width: 80px;"></div></div>
                        <div class="stat-sub mt-2 d-flex justify-content-between align-items-center">
                            <span class="text-success fw-bold d-flex align-items-center gap-1"><i class="bi bi-arrow-up-right"></i><span id="monthSent"><div class="skeleton skeleton-text d-inline-block m-0" style="width:20px;"></div></span> giden</span>
                            <span class="text-info fw-bold d-flex align-items-center gap-1"><i class="bi bi-arrow-down-left"></i><span id="monthReceived"><div class="skeleton skeleton-text d-inline-block m-0" style="width:20px;"></div></span> gelen</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card glass-panel">
                        <div class="stat-label mb-2">Toplam Müşteri</div>
                        <div class="stat-value" id="totalContacts"><div class="skeleton skeleton-text d-inline-block m-0" style="height: 38px; width: 80px;"></div></div>
                        <div class="stat-sub mt-2 d-flex align-items-center gap-1">
                            <i class="bi bi-chat-dots"></i> <span id="totalMessages"><div class="skeleton skeleton-text d-inline-block m-0" style="width:30px;"></div></span> toplam mesaj
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="chart-card glass-panel h-100">
                        <div class="chart-title text-primary"><i class="bi bi-graph-up-arrow"></i> Son 7 Gün — Aktivite Trendi</div>
                        <div style="height: 280px;">
                            <canvas id="weeklyChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-card glass-panel h-100">
                        <div class="chart-title text-warning"><i class="bi bi-trophy-fill"></i> En Aktif Müşteriler</div>
                        <div id="topContactsList" class="mt-3">
                            <div class="top-contact-item opacity-75 border-0">
                                <div class="skeleton contact-rank border-0" style="background:transparent;"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-text m-0 mb-2" style="width: 100px; height:14px;"></div>
                                    <div class="skeleton skeleton-text m-0" style="width: 80px; height:10px;"></div>
                                </div>
                                <div class="skeleton skeleton-text m-0" style="width: 30px; height: 20px; border-radius:10px;"></div>
                            </div>
                            <div class="top-contact-item opacity-50 border-0">
                                <div class="skeleton contact-rank border-0" style="background:transparent;"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-text m-0 mb-2" style="width: 80px; height:14px;"></div>
                                    <div class="skeleton skeleton-text m-0" style="width: 100px; height:10px;"></div>
                                </div>
                                <div class="skeleton skeleton-text m-0" style="width: 25px; height: 20px; border-radius:10px;"></div>
                            </div>
                            <div class="top-contact-item opacity-25 border-0">
                                <div class="skeleton contact-rank border-0" style="background:transparent;"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-text m-0 mb-2" style="width: 120px; height:14px;"></div>
                                    <div class="skeleton skeleton-text m-0" style="width: 90px; height:10px;"></div>
                                </div>
                                <div class="skeleton skeleton-text m-0" style="width: 20px; height: 20px; border-radius:10px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donut Chart & System Info -->
            <div class="row g-4 mt-1">
                <div class="col-md-4">
                    <div class="chart-card glass-panel h-100">
                        <div class="chart-title text-success"><i class="bi bi-pie-chart-fill"></i> Bu Ayki Dağılım</div>
                        <div style="height: 220px; display: flex; align-items: center; justify-content: center;">
                            <canvas id="directionChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="chart-card glass-panel h-100">
                        <div class="chart-title text-info"><i class="bi bi-hdd-network-fill"></i> Sistem Bilgisi & Altyapı</div>
                        <div class="row mt-4 g-4">
                            <div class="col-6">
                                <div class="mb-4">
                                    <div class="stat-label mb-1">Bağlantı Türü</div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                        <i class="bi bi-whatsapp" style="color:#25D366;"></i> WhatsApp Business API
                                    </div>
                                </div>
                                <div>
                                    <div class="stat-label mb-1">Otomasyon Merkezi</div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                        <i class="bi bi-diagram-3-fill text-danger"></i> n8n (motomotomasyon.com)
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-4">
                                    <div class="stat-label mb-1">Veritabanı Yapısı</div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                        <i class="bi bi-database-fill text-primary"></i> MySQL + PostgreSQL
                                    </div>
                                </div>
                                <div>
                                    <div class="stat-label mb-1">Yapay Zeka Motoru</div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                        <i class="bi bi-cpu-fill text-warning"></i> OpenAI GPT-4
                                    </div>
                                </div>
                            </div>
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

        try {
            const res = await fetch('/api/stats.php', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const s = await res.json();

            // Today
            document.getElementById('todayTotal').textContent = s.today.total.toLocaleString('tr-TR');
            document.getElementById('todaySent').textContent = s.today.sent.toLocaleString('tr-TR');
            document.getElementById('todayReceived').textContent = s.today.received.toLocaleString('tr-TR');

            // Week
            document.getElementById('weekTotal').textContent = s.week.total.toLocaleString('tr-TR');
            document.getElementById('weekSent').textContent = s.week.sent.toLocaleString('tr-TR');
            document.getElementById('weekReceived').textContent = s.week.received.toLocaleString('tr-TR');

            // Month
            document.getElementById('monthTotal').textContent = s.month.total.toLocaleString('tr-TR');
            document.getElementById('monthSent').textContent = s.month.sent.toLocaleString('tr-TR');
            document.getElementById('monthReceived').textContent = s.month.received.toLocaleString('tr-TR');

            // Total
            document.getElementById('totalMessages').textContent = s.total.messages.toLocaleString('tr-TR');
            document.getElementById('totalContacts').textContent = s.total.contacts.toLocaleString('tr-TR');

            // Last update
            document.getElementById('lastUpdate').innerHTML = `<i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('tr-TR')}`;

            // Weekly Chart
            if (s.recent_activity && s.recent_activity.length > 0) {
                const labels = s.recent_activity.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('tr-TR', { weekday: 'short', day: 'numeric' });
                });
                new Chart(document.getElementById('weeklyChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Giden',
                                data: s.recent_activity.map(d => d.sent),
                                backgroundColor: 'rgba(37, 99, 235, 0.8)',
                                borderRadius: 6
                            },
                            {
                                label: 'Gelen',
                                data: s.recent_activity.map(d => d.received),
                                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f0f2f5' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Direction Donut
            new Chart(document.getElementById('directionChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Giden', 'Gelen'],
                    datasets: [{
                        data: [s.month.sent, s.month.received],
                        backgroundColor: ['rgba(37, 99, 235, 0.8)', 'rgba(16, 185, 129, 0.8)'],
                        borderWidth: 0,
                        spacing: 4,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '65%'
                }
            });

            // Top Contacts
            const listEl = document.getElementById('topContactsList');
            if (s.top_contacts && s.top_contacts.length > 0) {
                listEl.innerHTML = '';
                s.top_contacts.forEach((c, i) => {
                    const rankClass = i < 3 ? `rank-${i + 1}` : 'rank-default';
                    listEl.innerHTML += `
                        <div class="top-contact-item">
                            <div class="contact-rank ${rankClass}">${i + 1}</div>
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="font-size: 0.85rem;">${c.name}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">${c.phone}</div>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">${parseInt(c.msg_count).toLocaleString('tr-TR')}</span>
                        </div>
                    `;
                });
            } else {
                listEl.innerHTML = '<div class="text-muted small">Henüz veri yok</div>';
            }

        } catch (err) {
            console.error('Stats error:', err);
        }
    });
    </script>
</body>
</html>
