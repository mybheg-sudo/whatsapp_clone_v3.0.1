<?php
/**
 * MYBHEG - Kurumsal İletişim Paneli
 * index.php - Ana Gösterge Paneli
 */
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MYBHEG Kurumsal İletişim ve CRM Paneli">
    <meta name="theme-color" content="#1E293B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>MYBHEG Kurumsal İletişim Paneli</title>

    <!-- Favicon & PWA -->
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="manifest" href="manifest.json">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/i18n.js"></script>
</head>

<body>

    <div class="app-container">

        <!-- COLUMN 1: Narrow Sidebar -->
        <div class="sidebar-nav">
            <div class="sidebar-logo">MY<br>BHEG</div>
            <a href="index.php" class="nav-item active" title="Gelen Kutusu">
                <i class="bi bi-chat-left-text-fill"></i>
            </a>
            <a href="contacts_page.php" class="nav-item" title="Kişiler">
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
            <button class="theme-toggle-btn" id="langToggleBtn" onclick="toggleLanguage()" title="Dil" style="font-size:0.75rem;font-weight:800;">TR</button>
            <div class="user-avatar mt-2 mb-2" title="Profil">AD</div>
        </div>

        <!-- COLUMN 2: Chat List -->
        <div class="chat-list-pane" id="chatListPane">
            <div class="list-header">
                <h5 data-i18n="messages">Mesajlar</h5>
                <!-- Mini Stats -->
                <div class="mini-stats d-flex gap-2 mt-2 mb-2">
                    <div class="mini-stat-card" title="Bugünün Mesajları">
                        <i class="bi bi-chat-dots-fill text-primary"></i>
                        <span id="statTodayMessages" class="mini-stat-val skeleton skeleton-text" style="width:24px;height:14px;display:inline-block;"></span>
                    </div>
                    <div class="mini-stat-card" title="Aktif Müşteriler">
                        <i class="bi bi-people-fill text-success"></i>
                        <span id="statActiveContacts" class="mini-stat-val skeleton skeleton-text" style="width:24px;height:14px;display:inline-block;"></span>
                    </div>
                    <div class="mini-stat-card" title="Bekleyen">
                        <i class="bi bi-clock-fill text-warning"></i>
                        <span id="statPending" class="mini-stat-val skeleton skeleton-text" style="width:24px;height:14px;display:inline-block;"></span>
                    </div>
                </div>
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Müşteri ara..." data-i18n-placeholder="search_customer">
                </div>
            </div>

            <div class="conversations" id="contactsList">
                <!-- Skeleton Loading State -->
                <div class="p-3 d-flex align-items-center gap-3 opacity-75">
                    <div class="skeleton skeleton-avatar"></div>
                    <div class="flex-grow-1">
                        <div class="skeleton skeleton-text title"></div>
                        <div class="skeleton skeleton-text short"></div>
                    </div>
                </div>
                <div class="p-3 d-flex align-items-center gap-3 opacity-50">
                    <div class="skeleton skeleton-avatar"></div>
                    <div class="flex-grow-1">
                        <div class="skeleton skeleton-text title" style="width: 50%;"></div>
                        <div class="skeleton skeleton-text short" style="width: 80%;"></div>
                    </div>
                </div>
                <div class="p-3 d-flex align-items-center gap-3 opacity-25">
                    <div class="skeleton skeleton-avatar"></div>
                    <div class="flex-grow-1">
                        <div class="skeleton skeleton-text title" style="width: 60%;"></div>
                        <div class="skeleton skeleton-text short" style="width: 40%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMN 3: Main Chat Window & CRM -->
        <div class="main-content">

            <!-- Chat Area -->
            <div class="chat-window d-none" id="mainChatWindow">

                <!-- Chat Header -->
                <div class="chat-header glass-panel" style="border-radius: 0; border-top: none; border-left: none; border-right: none;">
                    <div class="header-user-info">
                        <button class="mobile-back-btn" id="mobileBackBtn">
                            <i class="bi bi-arrow-left"></i>
                        </button>
                        <div>
                            <h6 class="mb-0" id="headerName">...</h6>
                            <span id="headerPhone"></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div id="conversationStatusBadge" class="status-badge status-ai d-none d-md-flex">
                            <i class="bi bi-robot"></i> AI Yanıtlıyor
                        </div>
                        <button class="action-btn" id="toggleCrmBtn" title="Müşteri Detayları">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="messages-area" id="messagesArea">
                    <div class="text-center text-muted my-auto" style="font-size:0.9rem;">Mesaj geçmişi yükleniyor...</div>
                </div>

                <!-- Input Area -->
                <div class="chat-input-area glass-panel" style="border-radius: 0; border-bottom: none; border-left: none; border-right: none;">
                    <form id="sendMessageForm">
                        <div class="input-container">
                            <button type="button" class="action-btn" title="Emoji (Yakında)">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                            <button type="button" class="action-btn" title="Dosya (Yakında)">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            <input type="text" id="messageInput" class="message-input" placeholder="Müşteriye yanıt yazın..." autocomplete="off">
                            <button type="button" class="action-btn d-none d-sm-flex" title="Ses Kaydı (Yakında)">
                                <i class="bi bi-mic"></i>
                            </button>
                            <button type="submit" class="btn-send ms-1" title="Gönder">
                                <i class="bi bi-send-fill" style="margin-left: -2px; margin-top: 1px;"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- CRM Sidebar -->
            <div class="crm-sidebar glass-panel" id="crmSidebar" style="border-radius: 0; border-top: none; border-bottom: none; border-right: none;">
                <div class="crm-sidebar-inner">
                    <div class="crm-header">
                        <i class="bi bi-person-badge"></i> Müşteri Özeti
                    </div>

                    <div class="crm-card">
                        <div class="info-row">
                            <span class="info-label">Ad Soyad</span>
                            <span class="info-value" id="crmName">...</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">İletişim</span>
                            <span class="info-value text-muted" id="crmPhone" style="font-weight: 500; font-family:'Inter'">...</span>
                        </div>

                        <div class="info-row mt-3">
                            <span class="info-label mb-2">Segment & Durum</span>
                            <div id="crmTags" class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-secondary border rounded-pill fw-normal">Yükleniyor...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Son Siparişler -->
                    <div class="d-flex align-items-center mb-3 mt-4 gap-2">
                        <i class="bi bi-bag-check text-secondary"></i>
                        <h6 class="text-secondary fw-bold mb-0" style="font-size: 0.8rem; text-transform: uppercase;">Son Siparişler</h6>
                    </div>

                    <div id="crmOrders" class="mb-4">
                        <div class="text-muted small">Sipariş geçmişi aranıyor...</div>
                    </div>

                    <!-- Bot Kontrolü -->
                    <div class="d-flex align-items-center mb-3 mt-4 gap-2">
                        <i class="bi bi-sliders text-secondary"></i>
                        <h6 class="text-secondary fw-bold mb-0" style="font-size: 0.8rem; text-transform: uppercase;">Otomasyon</h6   >
                    </div>

                    <div class="toggle-switch-container">
                        <div>
                            <span class="info-label d-block text-dark fw-bold mb-1" style="font-size:0.9rem;">Manuel Müdahale</span>
                            <small class="text-muted d-block" style="font-size:0.75rem; line-height: 1.2;">AI yanıtlarını durdur ve sohbeti kilitle.</small>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="manualInterventionSwitch">
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Selection State -->
            <div class="d-flex w-100 align-items-center justify-content-center h-100" id="noSelectionState">
                <div class="glass-panel" style="padding: 3rem 4rem; text-align: center; border-radius: 24px;">
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent-color) 0%, var(--brand-secondary) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; opacity: 0.9; box-shadow: 0 10px 25px var(--accent-glow);">
                            <i class="bi bi-chat-dots-fill text-white" style="font-size: 2.2rem;"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">Görüşme Seçilmedi</h4>
                    <p class="text-muted" style="font-size: 0.95rem; max-width: 250px; margin: 0 auto;">
                        Sol taraftaki listeden bir müşteri seçerek CRM detaylarına ulaşın ve yanıt verin.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        // Mobil menü geçişleri için ek JS cila
        document.addEventListener('DOMContentLoaded', () => {
            const chatList = document.getElementById('chatListPane');
            const backBtn = document.getElementById('mobileBackBtn');
            const chatItems = document.querySelectorAll('.chat-list-item');
            
            // Item tıklandığında mobilde listeyi gizle, chat window'u göster
            document.getElementById('contactsList').addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    const item = e.target.closest('.chat-list-item');
                    if (item) {
                        chatList.classList.add('hidden-mobile');
                    }
                }
            });

            // Geri tuşuna basınca mobilde listeyi tekrar göster
            if (backBtn) {
                backBtn.addEventListener('click', () => {
                    chatList.classList.remove('hidden-mobile');
                    // Mesaj alanını temizleyebiliriz veya arkada bırakabiliriz
                });
            }
        });
    </script>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>

</html>