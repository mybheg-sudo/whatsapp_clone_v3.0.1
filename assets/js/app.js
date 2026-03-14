/**
 * MYBHEG Kurumsal İletişim Paneli - UI Etkileşimleri
 */

function escapeHtml(unsafe) {
    return (unsafe || '').toString()
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

document.addEventListener('DOMContentLoaded', function () {

    // --- API CONFIGURATION ---
    const API_BASE_URL = 'api'; // Artık yerel PHP API'sine bakıyor
    const TOKEN = localStorage.getItem('mybheg_auth_token');
    const USER = JSON.parse(localStorage.getItem('mybheg_user') || '{}');

    // 0. Authentication Check
    if (!TOKEN) {
        window.location.href = 'login.php';
        return;
    }

    // --- STATE ---
    let currentContactId = null;
    let currentContactPhone = null;
    let currentContactName = null;
    let pollingInterval = null;
    let contactPollingInterval = null;
    let isManualMode = false;

    // Helper to fetch with Bearer Token
    async function apiFetch(endpoint, options = {}) {
        options.headers = {
            ...options.headers,
            'Authorization': `Bearer ${TOKEN}`,
            'Content-Type': 'application/json'
        };
        const response = await fetch(`${API_BASE_URL}${endpoint}`, options);
        if (response.status === 401 || response.status === 403) {
            localStorage.removeItem('mybheg_auth_token');
            window.location.href = 'login.php';
            throw new Error('Unauthorized');
        }
        
        const data = await response.json();
        if (!response.ok || data.error) {
            throw new Error(data.message || data.error || 'API Hatası');
        }
        return data;
    }

    // 1. Load Contacts
    async function loadContacts() {
        const contactsListEl = document.getElementById('contactsList');
        try {
            const contacts = await apiFetch('/contacts.php');

            if (!contacts || contacts.length === 0) {
                contactsListEl.innerHTML = `
                    <div class="p-4 text-center opacity-50">
                        <i class="bi bi-chat-text" style="font-size: 2.5rem; color: var(--text-light);"></i>
                        <p class="text-muted small mt-2 mb-0">Henüz görüşme bulunmuyor.</p>
                    </div>
                `;
                return;
            }

            contactsListEl.innerHTML = ''; // Clear loading

            contacts.forEach((conv, index) => {
                const nameDisplay = conv.name || conv.phone;
                const initials = (nameDisplay.match(/[a-zA-ZğüşıöçĞÜŞİÖÇ]/g) || []).slice(0, 2).join('').toUpperCase() || '#';
                const isActive = (conv.phone === currentContactPhone) ? 'active' : '';

                // Format time
                let timeStr = '';
                if (conv.last_message_time) {
                    const date = new Date(conv.last_message_time);
                    timeStr = `${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
                }

                // Unread mockup
                const hasUnread = index === 0 && !currentContactPhone;

                const chatItemHTML = `
                    <div class="chat-list-item ${isActive}" data-id="${escapeHtml(conv.id)}" data-phone="${escapeHtml(conv.phone)}" data-name="${escapeHtml(nameDisplay)}">
                        <div class="avatar">
                            ${escapeHtml(initials)}
                            ${index < 2 ? '<div class="online-dot" title="Çevrimiçi"></div>' : ''}
                        </div>
                        <div class="chat-info">
                            <div class="chat-name-row">
                                <span class="chat-name">${escapeHtml(nameDisplay)}</span>
                                <span class="chat-time">${escapeHtml(timeStr)}</span>
                            </div>
                            <div class="chat-snippetRow">
                                <span class="chat-snippet">${escapeHtml(conv.last_message || 'Mesajı görüntüle...')}</span>
                                ${hasUnread ? '<div class="unread-dot"></div>' : ''}
                            </div>
                        </div>
                    </div>
                `;
                contactsListEl.insertAdjacentHTML('beforeend', chatItemHTML);
            });

            // Bind click events to the new items
            bindContactClicks();

        } catch (error) {
            console.error('Error fetching contacts:', error);
            contactsListEl.innerHTML = `
                <div class="p-4 text-center opacity-50">
                    <i class="bi bi-wifi-off" style="font-size: 2.5rem; color: var(--text-light);"></i>
                    <p class="text-muted small mt-2 mb-0">Bağlantı hatası. Tekrar deneyin.</p>
                </div>
            `;
            if (typeof showToast === 'function') showToast({ type: 'error', title: 'Bağlantı Hatası', message: 'Kişi listesi yüklenemedi.' });
        }
    }

    // Bind clicks to dynamically injected chat list items
    function bindContactClicks() {
        const chatItems = document.querySelectorAll('.chat-list-item');
        chatItems.forEach(item => {
            item.addEventListener('click', function () {
                // UI Updates
                chatItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                const unreadDot = this.querySelector('.unread-dot');
                if (unreadDot) unreadDot.style.display = 'none';

                // Mobil Cihazda Chat Listesini Gizle, Mesajlaşma Ekranını Göster
                if (window.innerWidth <= 768) {
                    document.querySelector('.chat-sidebar').classList.add('d-none');
                    document.querySelector('.main-chat').classList.add('mobile-active');
                    document.querySelector('.crm-sidebar').classList.add('d-none');
                }

                // Load Chat
                const id = this.dataset.id;
                const phone = this.dataset.phone;
                const name = this.dataset.name;
                currentContactId = id;
                currentContactPhone = phone;
                currentContactName = name;
                
                loadMessages(id, phone, name);
            });
        });

        // Otomatik olarak ilk kişiyi seç (Sadece masaüstünde)
        if (chatItems && chatItems.length > 0 && !currentContactId && window.innerWidth > 768) {
            chatItems[0].click();
        }
    }

    // Initial Load
    loadContacts();
    loadMiniStats();

    // Kişi listesi polling (30 saniye)
    contactPollingInterval = setInterval(() => loadContacts(), 30000);

    // Load mini dashboard stats
    async function loadMiniStats() {
        try {
            const data = await apiFetch('/stats.php');
            const el1 = document.getElementById('statTodayMessages');
            const el2 = document.getElementById('statActiveContacts');
            const el3 = document.getElementById('statPending');
            if (el1) { el1.textContent = data.total_messages ?? '0'; el1.className = 'mini-stat-val'; }
            if (el2) { el2.textContent = data.total_contacts ?? '0'; el2.className = 'mini-stat-val'; }
            if (el3) { el3.textContent = data.pending_orders ?? '0'; el3.className = 'mini-stat-val'; }
        } catch (e) {
            // Stats are non-critical, fail silently and show 0
            ['statTodayMessages', 'statActiveContacts', 'statPending'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.textContent = '0'; el.className = 'mini-stat-val'; }
            });
        }
    }

    // 2. Load Messages for a Contact
    let lastMessageCount = 0;

    async function loadMessages(contactId, phone, name) {
        document.getElementById('noSelectionState').classList.add('d-none');
        document.getElementById('mainChatWindow').classList.remove('d-none');

        // Update Header & CRM UI
        document.getElementById('headerName').textContent = name;
        document.getElementById('headerPhone').textContent = `+${phone}`;
        document.getElementById('crmName').textContent = name;
        document.getElementById('crmPhone').textContent = `+${phone}`;

        // Mobil Geri Butonu İşlevselliği
        const backBtn = document.getElementById('mobileBackBtn');
        if (backBtn) {
            backBtn.onclick = () => {
                document.querySelector('.chat-sidebar').classList.remove('d-none');
                document.querySelector('.main-chat').classList.remove('mobile-active');
                document.querySelector('.crm-sidebar').classList.add('d-none');
            };
        }

        // CRM zenginleştirme: sipariş + durum (sadece ilk yüklemede)
        const messageArea = document.getElementById('messagesArea');
        const isFirstLoad = messageArea.querySelector('.text-muted') !== null || messageArea.innerHTML.trim() === '';
        
        if (isFirstLoad) {
            loadOrders(phone);
            loadContactStatus(phone);
        }

        // İlk yükleme değilse (polling), scroll pozisyonunu koru

        if (isFirstLoad) {
            messageArea.innerHTML = `
                <div class="p-3 w-100 h-100 d-flex flex-column justify-content-end gap-3 pb-4">
                    <div class="message-bubble msg-received skeleton opacity-25" style="width: 50%; height: 40px; border:none; box-shadow:none;"></div>
                    <div class="message-bubble msg-sent skeleton opacity-50" style="width: 40%; height: 40px; border:none; box-shadow:none; align-self: flex-end;"></div>
                    <div class="message-bubble msg-received skeleton opacity-75" style="width: 65%; height: 55px; border:none; box-shadow:none;"></div>
                </div>
            `;
        }

        try {
            const messages = await apiFetch(`/messages.php?contact_id=${contactId}`);

            // Polling: mesaj sayısı değişmediyse DOM'u güncelleme
            if (!isFirstLoad && messages.length === lastMessageCount) {
                return;
            }
            lastMessageCount = messages.length;

            messageArea.innerHTML = ''; // Clear

            if (!messages || messages.length === 0) {
                messageArea.innerHTML = `
                    <div class="d-flex w-100 h-100 flex-column align-items-center justify-content-center text-muted opacity-50">
                        <i class="bi bi-chat-dots" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p style="font-size: 0.95rem;">Bu müşteriyle henüz mesajınız yok.</p>
                    </div>
                `;
                startPolling(contactId);
                return;
            }

            // Date Badge
            messageArea.innerHTML = `<div class="text-center my-3"><span class="badge bg-white text-secondary border shadow-sm px-3 py-1 rounded-pill" style="font-size:0.75rem;">Bugün</span></div>`;

            messages.forEach(msg => {
                const cssClass = msg.direction === 'incoming' ? 'msg-received' : 'msg-sent';
                let timeStr = '';
                if (msg.timestamp) {
                    const date = new Date(msg.timestamp);
                    timeStr = `${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
                }

                const div = document.createElement('div');
                div.className = `message-bubble ${cssClass}`;

                const contentDiv = document.createElement('div');
                contentDiv.innerText = msg.content;

                const timeSpan = document.createElement('span');
                timeSpan.className = 'msg-time';
                timeSpan.innerHTML = `${timeStr} ${cssClass === 'msg-sent' ? '<i class="bi bi-check2-all ms-1 text-primary"></i>' : ''}`;

                div.appendChild(contentDiv);
                div.appendChild(timeSpan);
                messageArea.appendChild(div);
            });

            scrollToBottom(messageArea);

            // Mesaj polling'i başlat
            startPolling(contactId);

        } catch (error) {
            console.error('Error fetching messages:', error);
            if (isFirstLoad) {
                messageArea.innerHTML = `
                    <div class="d-flex w-100 h-100 align-items-center justify-content-center text-danger" style="font-size:0.9rem;">
                        <i class="bi bi-exclamation-triangle me-2"></i> Mesajlar yüklenemedi.
                    </div>
                `;
            }
        }
    }

    // Polling: her 5 saniyede yeni mesaj kontrolü
    function startPolling(contactId) {
        stopPolling();
        pollingInterval = setInterval(() => {
            if (currentContactId === contactId) {
                loadMessages(contactId, currentContactPhone, currentContactName);
            }
        }, 5000);
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    // --- CRM: Sipariş Bilgisi ---
    async function loadOrders(phone) {
        const ordersEl = document.getElementById('crmOrders');
        if (!ordersEl) return;
        
        ordersEl.innerHTML = `
            <div class="card mb-2 skeleton opacity-50" style="border-radius: 10px; height: 54px; border:none; box-shadow:none;"></div>
            <div class="card mb-2 skeleton opacity-25" style="border-radius: 10px; height: 54px; border:none; box-shadow:none;"></div>
        `;
        
        try {
            const orders = await apiFetch(`/orders.php?phone=${phone}`);
            
            if (!orders || orders.length === 0) {
                ordersEl.innerHTML = `
                    <div class="text-center p-3 opacity-50">
                        <i class="bi bi-bag-x" style="font-size: 2rem; color: var(--text-light);"></i>
                        <p class="text-muted small mt-2 mb-0" style="font-family: 'Inter';">Gemiş sipariş bulunmuyor</p>
                    </div>
                `;
                document.getElementById('crmTags').innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 fw-normal">Sipariş Yok</span>';
                return;
            }

            // Etiketleri sipariş durumuna göre oluştur
            const tags = new Set();
            orders.forEach(o => {
                if (o.status) tags.add(o.status);
                if (o.payment) tags.add(o.payment === 'paid' ? 'Ödendi' : o.payment);
            });
            
            const tagsEl = document.getElementById('crmTags');
            tagsEl.innerHTML = '';
            tags.forEach(tag => {
                const colors = {
                    'BEKLEMEDE': 'bg-warning text-dark border-warning-subtle',
                    'ONAYLANDI': 'bg-success bg-opacity-10 text-success border-success-subtle',
                    'Ödendi': 'bg-info bg-opacity-10 text-info border-info-subtle',
                    'paid': 'bg-info bg-opacity-10 text-info border-info-subtle',
                    'pending': 'bg-warning bg-opacity-10 text-warning border-warning-subtle'
                };
                const cls = colors[tag] || 'bg-primary bg-opacity-10 text-primary border-primary-subtle';
                tagsEl.innerHTML += `<span class="badge ${cls} rounded-pill me-1 mb-1 px-2 fw-normal border">${tag}</span>`;
            });

            // Sipariş kartlarını render et
            ordersEl.innerHTML = '';
            orders.forEach(order => {
                const statusColor = order.status === 'ONAYLANDI' ? 'success' : (order.status === 'BEKLEMEDE' ? 'warning' : 'secondary');
                const date = order.date ? new Date(order.date).toLocaleDateString('tr-TR') : '';
                
                ordersEl.innerHTML += `
                    <div class="card border mb-2 shadow-sm" style="border-radius: 10px;">
                        <div class="card-body p-2 px-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold" style="font-size: 0.85rem;">₺${parseFloat(order.total || 0).toLocaleString('tr-TR')}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">${escapeHtml(date)}</div>
                                </div>
                                <span class="badge bg-${escapeHtml(statusColor)} bg-opacity-10 text-${escapeHtml(statusColor)} rounded-pill" style="font-size: 0.7rem;">${escapeHtml(order.status || 'Bilinmiyor')}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
        } catch (error) {
            console.error('Orders error:', error);
            ordersEl.innerHTML = '<div class="text-muted small">Sipariş bilgisi alınamadı</div>';
        }
    }

    // --- CRM: Konuşma Durumu ---
    async function loadContactStatus(phone) {
        try {
            const status = await apiFetch(`/contact_status.php?phone=${phone}`);
            
            isManualMode = status.is_manual;
            
            // Switch durumunu güncelle
            if (interventionSwitch) {
                interventionSwitch.checked = status.is_manual;
            }
            
            // Badge güncelle
            updateStatusBadge(status.is_manual);
            
        } catch (error) {
            console.error('Contact status error:', error);
        }
    }


    // 3. Manuel Müdahale Switch (Manual Intervention Toggle)
    const interventionSwitch = document.getElementById('manualInterventionSwitch');
    const statusBadge = document.getElementById('conversationStatusBadge');

    if (interventionSwitch) {
        interventionSwitch.addEventListener('change', async function () {
            if (!currentContactPhone) return;

            const action = this.checked ? 'add' : 'remove';
            try {
                await apiFetch('/manual_list.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        phone: currentContactPhone,
                        action: action
                    })
                });

                isManualMode = this.checked;
                updateStatusBadge(isManualMode);

                if (this.checked) {
                    if (typeof showToast === 'function') showToast({ type: 'info', title: 'Manuel Müdahale', message: 'AI yanıtları durduruldu.' });
                } else {
                    if (typeof showToast === 'function') showToast({ type: 'success', title: 'AI Aktif', message: 'AI yanıtları devam ediyor.' });
                }
            } catch (error) {
                console.error('Manual list error', error);
                this.checked = !this.checked; // Geri al
                if (typeof showToast === 'function') showToast({ type: 'error', title: 'Hata', message: error.message || 'İşlem başarısız.' });
            }
        });
    }

    function updateStatusBadge(isManual) {
        if (!statusBadge) return;
        if (isManual) {
            statusBadge.className = 'status-badge status-manual';
            statusBadge.innerHTML = '<i class="bi bi-person-fill"></i> Manuel Müdahale';
        } else {
            statusBadge.className = 'status-badge status-ai';
            statusBadge.innerHTML = '<i class="bi bi-robot"></i> Yapay Zeka Yanıtlıyor';
        }
    }

    // 3b. CRM Toggle
    const toggleCrmBtn = document.getElementById('toggleCrmBtn');
    const crmSidebar = document.getElementById('crmSidebar');

    if (toggleCrmBtn && crmSidebar) {
        toggleCrmBtn.addEventListener('click', function () {
            crmSidebar.classList.toggle('collapsed');
        });
    }

    // 4. Scroll messages to bottom on load
    const messageArea = document.getElementById('messagesArea');
    if (messageArea) {
        // messageArea.scrollTop = messageArea.scrollHeight;
        scrollToBottom(messageArea);
    }

    // 5. Send Message — n8n webhook proxy üzerinden WhatsApp'a gönderir
    const sendForm = document.getElementById('sendMessageForm');
    const msgInput = document.getElementById('messageInput');

    if (sendForm) {
        sendForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = msgInput.value.trim();
            if (!text || !currentContactPhone) return;

            // UI'ya hemen ekle (optimistic)
            appendMessage(text, 'sent');
            msgInput.value = '';
            setTimeout(() => {
                scrollToBottom(document.getElementById('messagesArea'));
            }, 50);

            try {
                await apiFetch('/send_message.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        phone: currentContactPhone,
                        message: text,
                        contact_id: currentContactId
                    })
                });
                if (typeof showToast === 'function') showToast({ type: 'success', title: 'Gönderildi', message: 'Mesaj başarıyla iletildi.' });
            } catch (error) {
                console.error('Message send failed', error);
                if (typeof showToast === 'function') showToast({ type: 'error', title: 'Gönderilemedi', message: error.message || 'Mesaj iletilemedi.' });
            }
        });
    }

    // Helpers
    function scrollToBottom(el) {
        el.scrollTo({
            top: el.scrollHeight,
            behavior: 'smooth'
        });
    }

    function appendMessage(text, type, options = {}) {
        if (!messageArea) return;

        const div = document.createElement('div');
        div.className = `message-bubble msg-${type}`;

        // Image attachment
        if (options.imageUrl) {
            const img = document.createElement('img');
            img.src = options.imageUrl;
            img.className = 'msg-image';
            img.alt = 'Gönderilen resim';
            img.loading = 'lazy';
            div.appendChild(img);
        }

        // File attachment
        if (options.fileUrl && !options.imageUrl) {
            const a = document.createElement('a');
            a.href = options.fileUrl;
            a.className = 'msg-attachment';
            a.target = '_blank';
            a.innerHTML = `
                <i class="bi bi-file-earmark-text file-icon"></i>
                <div class="file-info">
                    <div class="file-name">${escapeHtml(options.fileName || 'Dosya')}</div>
                    <div class="file-size">${options.fileSize ? formatFileSize(options.fileSize) : ''}</div>
                </div>
                <i class="bi bi-download"></i>
            `;
            div.appendChild(a);
        }

        // Text content
        if (text) {
            const content = document.createElement('div');
            content.textContent = text;
            div.appendChild(content);
        }

        // Time + status ticks
        const timeEl = document.createElement('span');
        timeEl.className = 'msg-time';
        const ts = options.timestamp ? new Date(options.timestamp) : new Date();
        timeEl.innerHTML = `${ts.getHours().toString().padStart(2, '0')}:${ts.getMinutes().toString().padStart(2, '0')}`;
        
        if (type === 'sent') {
            const status = options.status || 'delivered';
            const statusIcon = status === 'read' ? 'bi-check-all msg-status-read' 
                : status === 'delivered' ? 'bi-check-all msg-status-delivered' 
                : 'bi-check msg-status-sent';
            timeEl.innerHTML += ` <i class="bi ${statusIcon} msg-status"></i>`;
        }

        div.appendChild(timeEl);
        messageArea.appendChild(div);
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // SSE — Real-time message updates
    let sseSource = null;
    
    function startSSE(contactId) {
        stopSSE();
        if (!contactId || !TOKEN) return;

        // Get last message ID from DOM
        const msgs = messageArea?.querySelectorAll('.message-bubble');
        let lastId = 0; // Will get all new messages

        try {
            sseSource = new EventSource(
                `${API_BASE_URL}/sse.php?contact_id=${contactId}&last_id=${lastId}&token=${TOKEN}`
            );
            
            sseSource.onmessage = function(event) {
                try {
                    const msg = JSON.parse(event.data);
                    if (msg.error) return;
                    
                    const type = msg.direction === 'outgoing' ? 'sent' : 'received';
                    appendMessage(msg.content, type, { timestamp: msg.timestamp, status: 'delivered' });
                    scrollToBottom(messageArea);
                    lastId = Math.max(lastId, parseInt(msg.id));
                } catch(e) { /* heartbeat or parse error */ }
            };
            
            sseSource.onerror = function() {
                // SSE failed — fallback to polling
                stopSSE();
            };
        } catch(e) {
            // SSE not supported — keep polling
        }
    }

    function stopSSE() {
        if (sseSource) {
            sseSource.close();
            sseSource = null;
        }
    }

    // File Upload Handler
    const fileBtn = document.querySelector('.input-icon[title="Dosya Ekle"]');
    if (fileBtn) {
        fileBtn.classList.add('file-upload-btn');
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt';
        fileBtn.appendChild(fileInput);

        fileInput.addEventListener('change', async function() {
            if (!this.files[0] || !currentContactPhone) return;

            const file = this.files[0];
            const formData = new FormData();
            formData.append('file', file);

            if (typeof showToast === 'function') showToast({ type: 'info', title: 'Yükleniyor...', message: file.name, duration: 2000 });

            try {
                const response = await fetch(`${API_BASE_URL}/upload.php`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${TOKEN}` },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    const isImage = data.is_image;
                    appendMessage(isImage ? '' : file.name, 'sent', {
                        imageUrl: isImage ? data.url : null,
                        fileUrl: data.url,
                        fileName: data.filename,
                        fileSize: data.size,
                        status: 'sent'
                    });
                    scrollToBottom(messageArea);
                    if (typeof showToast === 'function') showToast({ type: 'success', title: 'Yüklendi', message: data.filename });
                } else {
                    if (typeof showToast === 'function') showToast({ type: 'error', title: 'Hata', message: data.error || 'Yükleme başarısız' });
                }
            } catch(e) {
                if (typeof showToast === 'function') showToast({ type: 'error', title: 'Yükleme Hatası', message: e.message });
            }

            this.value = '';
        });
    }

    // 6. Sidebar Navigation Interactivity
    const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function () {
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // 7. Debounced Search
    const searchInput = document.querySelector('.search-input');
    let searchTimeout = null;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            searchTimeout = setTimeout(() => {
                loadContacts(q);
            }, 350);
        });
    }

    // Override loadContacts to support search parameter
    const _origLoadContacts = loadContacts;
    loadContacts = async function(searchQuery) {
        const contactsListEl = document.getElementById('contactsList');
        try {
            const endpoint = searchQuery
                ? `/contacts.php?search=${encodeURIComponent(searchQuery)}`
                : '/contacts.php';
            const contacts = await apiFetch(endpoint);

            if (!contacts || contacts.length === 0) {
                contactsListEl.innerHTML = `
                    <div class="p-4 text-center opacity-50">
                        <i class="bi ${searchQuery ? 'bi-search' : 'bi-chat-text'}" style="font-size: 2.5rem; color: var(--text-light);"></i>
                        <p class="text-muted small mt-2 mb-0">${searchQuery ? 'Sonuç bulunamadı.' : 'Henüz görüşme bulunmuyor.'}</p>
                    </div>
                `;
                return;
            }

            contactsListEl.innerHTML = '';

            contacts.forEach((conv, index) => {
                const nameDisplay = conv.name || conv.phone;
                const initials = (nameDisplay.match(/[a-zA-ZğüşıöçĞÜŞİÖÇ]/g) || []).slice(0, 2).join('').toUpperCase() || '#';
                const isActive = (conv.phone === currentContactPhone) ? 'active' : '';

                let timeStr = '';
                if (conv.last_message_time) {
                    const date = new Date(conv.last_message_time);
                    timeStr = `${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
                }

                const hasUnread = index === 0 && !currentContactPhone;

                const chatItemHTML = `
                    <div class="chat-list-item ${isActive}" data-id="${escapeHtml(conv.id)}" data-phone="${escapeHtml(conv.phone)}" data-name="${escapeHtml(nameDisplay)}">
                        <div class="avatar">
                            ${escapeHtml(initials)}
                            ${index < 2 ? '<div class="online-dot" title="Çevrimiçi"></div>' : ''}
                        </div>
                        <div class="chat-info">
                            <div class="chat-name-row">
                                <span class="chat-name">${escapeHtml(nameDisplay)}</span>
                                <span class="chat-time">${escapeHtml(timeStr)}</span>
                            </div>
                            <div class="chat-snippetRow">
                                <span class="chat-snippet">${escapeHtml(conv.last_message || 'Mesajı görüntüle...')}</span>
                                ${hasUnread ? '<div class="unread-dot"></div>' : ''}
                            </div>
                        </div>
                    </div>
                `;
                contactsListEl.insertAdjacentHTML('beforeend', chatItemHTML);
            });

            bindContactClicks();
        } catch (error) {
            console.error('Error fetching contacts:', error);
            contactsListEl.innerHTML = `
                <div class="p-4 text-center opacity-50">
                    <i class="bi bi-wifi-off" style="font-size: 2.5rem; color: var(--text-light);"></i>
                    <p class="text-muted small mt-2 mb-0">Bağlantı hatası.</p>
                </div>
            `;
        }
    };

    // 8. Keyboard Shortcuts
    document.addEventListener('keydown', function (e) {
        const tag = document.activeElement.tagName;
        const isInput = tag === 'INPUT' || tag === 'TEXTAREA' || document.activeElement.isContentEditable;

        // Ctrl+K or / → Focus search (when not in an input)
        if ((!isInput && e.key === '/') || (e.ctrlKey && e.key === 'k')) {
            e.preventDefault();
            if (searchInput) searchInput.focus();
            return;
        }

        // Escape — blur active input or go back on mobile
        if (e.key === 'Escape') {
            if (isInput) {
                document.activeElement.blur();
            } else if (window.innerWidth <= 768) {
                const chatSidebar = document.querySelector('.chat-sidebar');
                const mainChat = document.querySelector('.main-chat');
                if (chatSidebar && mainChat) {
                    chatSidebar.classList.remove('d-none');
                    mainChat.classList.remove('mobile-active');
                }
            }
            return;
        }
    });

    // Ctrl+Enter to send message from textarea
    if (msgInput) {
        msgInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendForm?.dispatchEvent(new Event('submit'));
            }
        });
    }
});
