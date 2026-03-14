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
    <title>MYBHEG Kurumsal İletişim Paneli</title>

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

</head>

<body>

    <div class="app-container">

        <!-- COLUMN 1: Narrow Sidebar -->
        <div class="sidebar-nav">
            <div class="sidebar-logo">
                MY<br>BHEG
            </div>
            <a href="index.php" class="nav-item active" title="Gelen Kutusu" style="text-decoration:none;color:inherit;">
                <i class="bi bi-chat-left-text-fill"></i>
            </a>
            <a href="contacts_page.php" class="nav-item" title="Kişiler" style="text-decoration:none;color:inherit;">
                <i class="bi bi-people-fill"></i>
            </a>
            <a href="reports.php" class="nav-item" title="Raporlar" style="text-decoration:none;color:inherit;">
                <i class="bi bi-bar-chart-fill"></i>
            </a>
            <a href="settings.php" class="nav-item" title="Ayarlar" style="text-decoration:none;color:inherit;margin-top:auto;">
                <i class="bi bi-gear-fill"></i>
            </a>
            <div class="nav-item mb-4" title="Çıkış" style="margin-bottom: 2rem;">
                <img src="https://ui-avatars.com/api/?name=Admin&background=ffffff&color=1a2a6c&rounded=true"
                    alt="Admin" width="32" height="32" class="rounded-circle">
            </div>
        </div>

        <!-- COLUMN 2: Chat List -->
        <div class="chat-list-pane">
            <div class="list-header">
                <h5>Son Görüşmeler</h5>
                <div class="position-relative">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="search-box" placeholder="Müşteri veya numara ara...">
                </div>
            </div>

            <div class="conversations" id="contactsList">
                <div class="p-4 text-center text-muted">Kişiler Yükleniyor...</div>
            </div>
        </div>

        <!-- COLUMN 3: Main Chat Window & CRM -->
        <div class="main-content">

            <!-- Chat Area -->
            <div class="chat-window d-none" id="mainChatWindow">

                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="header-user-info">
                        <div>
                            <h6 class="mb-0 fw-bold" id="headerName">Yükleniyor...</h6>
                            <small class="text-muted" id="headerPhone"></small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div id="conversationStatusBadge" class="status-badge status-ai">
                            <i class="bi bi-robot"></i> Yapay Zeka Yanıtlıyor
                        </div>
                        <button class="btn btn-light border" id="toggleCrmBtn" title="Müşteri Kartını Aç/Kapa">
                            <i class="bi bi-layout-sidebar-reverse"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="messages-area" id="messagesArea">
                    <div class="text-center text-muted my-auto">Mesajlar Yükleniyor...</div>
                </div>

                    <!-- Input Area -->
                    <div class="chat-input-area">
                        <form id="sendMessageForm">
                            <div class="input-container d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-light rounded-circle text-muted flex-shrink-0"
                                    style="width:40px;height:40px;" title="Emoji">
                                    <i class="bi bi-emoji-smile"></i>
                                </button>
                                <button type="button" class="btn btn-light rounded-circle text-muted flex-shrink-0"
                                    style="width:40px;height:40px;" title="Dosya Ekle">
                                    <i class="bi bi-paperclip"></i>
                                </button>
                                <input type="text" id="messageInput" class="form-control message-input flex-grow-1"
                                    placeholder="Bir mesaj yazın (Müşteriye direkt iletilir)..." autocomplete="off">
                                <button type="button" class="btn btn-light rounded-circle text-muted flex-shrink-0 me-2"
                                    style="width:40px;height:40px;" title="Ses Kaydı">
                                    <i class="bi bi-mic"></i>
                                </button>
                                <button type="submit" class="btn-send border-0 flex-shrink-0 shadow-sm">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- CRM Sidebar -->
                <div class="crm-sidebar" id="crmSidebar">
                    <div class="crm-sidebar-inner">
                        <div class="crm-header">Müşteri Kartı</div>

                        <div class="crm-card shadow-sm border-0">
                            <div class="info-row">
                                <span class="info-label">Ad Soyad</span>
                                <span class="info-value" id="crmName">Yükleniyor...</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Telefon Numarası</span>
                                <span class="info-value" id="crmPhone">Yükleniyor...</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Etiketler</span>
                                <div class="mt-1" id="crmTags">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 fw-normal">Yükleniyor...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Son Siparişler -->
                        <h6 class="text-muted fw-bold mb-3 mt-4" style="font-size: 0.8rem; text-transform: uppercase;">
                            Son Siparişler</h6>

                        <div id="crmOrders" class="mb-3">
                            <div class="text-muted small">Sipariş bilgisi yükleniyor...</div>
                        </div>

                        <!-- Kontrol Paneli -->
                        <h6 class="text-muted fw-bold mb-3 mt-4" style="font-size: 0.8rem; text-transform: uppercase;">
                            Otomasyon
                            Kontrolü</h6>

                        <div class="card border border-danger-subtle bg-danger bg-opacity-10 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="card-title text-danger fw-bold" style="font-size: 0.9rem;">Manuel Devralma</h6>
                                <p class="card-text text-muted" style="font-size: 0.75rem;">Yapay zekanın otomatik yanıt
                                    vermesini durdurun ve görüşmeye dahil olun.</p>

                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="manualInterventionSwitch">
                                    <label class="form-check-label fw-bold text-dark ms-1 mt-1"
                                        for="manualInterventionSwitch" style="font-size: 0.85rem;">Müdahale Et
                                        (Kilitle)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Selection State -->
                <div class="d-flex w-100 align-items-center justify-content-center h-100" id="noSelectionState">
                    <div class="text-center p-5 bg-white rounded-3 shadow-sm border border-light">
                        <div class="mb-3">
                            <i class="bi bi-chat-dots"
                                style="font-size: 3rem; color: var(--brand-primary); opacity: 0.5;"></i>
                        </div>
                        <h5 class="text-secondary fw-bold">Görüşme Seçilmedi</h5>
                        <p class="text-muted small">Sol taraftan bir görüşme seçerek yanıtlamaya başlayın.</p>
                    </div>
                </div>

        </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>

</body>

</html>