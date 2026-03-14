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
    <title>Raporlar — MYBHEG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .reports-container {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: #f0f2f5;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
        }
        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
        }
        .stat-sub {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .chart-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1a1d21;
            margin-bottom: 1rem;
        }
        .top-contact-item {
            display: flex;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f0f2f5;
        }
        .top-contact-item:last-child { border-bottom: 0; }
        .contact-rank {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .rank-1 { background: #FFD700; color: #1a1d21; }
        .rank-2 { background: #C0C0C0; color: #1a1d21; }
        .rank-3 { background: #CD7F32; color: white; }
        .rank-default { background: #e9ecef; color: #6c757d; }
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
            <a href="reports.php" class="nav-item active" title="Raporlar" style="text-decoration:none;color:inherit;">
                <i class="bi bi-bar-chart-fill"></i>
            </a>
            <a href="settings.php" class="nav-item" title="Ayarlar" style="text-decoration:none;color:inherit;margin-top:auto;">
                <i class="bi bi-gear-fill"></i>
            </a>
            <div class="user-avatar" style="margin-top: 1rem;">AD</div>
        </div>

        <!-- Reports Content -->
        <div class="reports-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Raporlar</h4>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Mesaj istatistikleri ve performans analizi</p>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2" id="lastUpdate">
                    <i class="bi bi-clock"></i> Güncelleniyor...
                </span>
            </div>

            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label mb-2">Bugün</div>
                        <div class="stat-value text-primary" id="todayTotal">—</div>
                        <div class="stat-sub mt-1">
                            <span class="text-success"><i class="bi bi-arrow-up-right"></i> <span id="todaySent">0</span> giden</span>
                            &middot;
                            <span class="text-info"><span id="todayReceived">0</span> gelen</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label mb-2">Bu Hafta</div>
                        <div class="stat-value text-success" id="weekTotal">—</div>
                        <div class="stat-sub mt-1">
                            <span class="text-success"><i class="bi bi-arrow-up-right"></i> <span id="weekSent">0</span> giden</span>
                            &middot;
                            <span class="text-info"><span id="weekReceived">0</span> gelen</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label mb-2">Bu Ay</div>
                        <div class="stat-value text-warning" id="monthTotal">—</div>
                        <div class="stat-sub mt-1">
                            <span class="text-success"><i class="bi bi-arrow-up-right"></i> <span id="monthSent">0</span> giden</span>
                            &middot;
                            <span class="text-info"><span id="monthReceived">0</span> gelen</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label mb-2">Toplam</div>
                        <div class="stat-value text-dark" id="totalMessages">—</div>
                        <div class="stat-sub mt-1">
                            <i class="bi bi-people"></i> <span id="totalContacts">0</span> kişi
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="chart-card">
                        <div class="chart-title"><i class="bi bi-graph-up"></i> Son 7 Gün — Mesaj Trendi</div>
                        <canvas id="weeklyChart" height="260"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-card" style="height: 100%;">
                        <div class="chart-title"><i class="bi bi-trophy"></i> En Aktif Kişiler</div>
                        <div id="topContactsList">
                            <div class="text-muted small">Yükleniyor...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donut Chart -->
            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <div class="chart-card">
                        <div class="chart-title"><i class="bi bi-pie-chart"></i> Gelen vs Giden (Bu Ay)</div>
                        <canvas id="directionChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="chart-card">
                        <div class="chart-title"><i class="bi bi-info-circle"></i> Sistem Bilgisi</div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="stat-label">Platform</div>
                                    <div class="fw-bold">WhatsApp Business API</div>
                                </div>
                                <div class="mb-3">
                                    <div class="stat-label">Otomasyon</div>
                                    <div class="fw-bold">n8n (motomotomasyon.com)</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="stat-label">Veritabanı</div>
                                    <div class="fw-bold">MySQL + PostgreSQL</div>
                                </div>
                                <div class="mb-3">
                                    <div class="stat-label">AI Engine</div>
                                    <div class="fw-bold">OpenAI GPT</div>
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
