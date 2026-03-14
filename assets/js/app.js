/**
 * MYBHEG Kurumsal İletişim Paneli - UI Etkileşimleri
 */

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
                contactsListEl.innerHTML = '<div class="p-4 text-center text-muted">Henüz görüşme bulunmuyor.</div>';
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
                    <div class="chat-list-item ${isActive}" data-id="${conv.id}" data-phone="${conv.phone}" data-name="${nameDisplay}">
                        <div class="avatar">
                            ${initials}
                            ${index < 2 ? '<div class="online-dot" title="Çevrimiçi"></div>' : ''}
                        </div>
                        <div class="chat-info">
                            <div class="chat-name-row">
                                <span class="chat-name">${nameDisplay}</span>
                                <span class="chat-time">${timeStr}</span>
                            </div>
                            <div class="chat-snippetRow">
                                <span class="chat-snippet">${conv.last_message || 'Mesajı görüntüle...'}</span>
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
            contactsListEl.innerHTML = '<div class="p-4 text-center text-danger">Veriler alınırken hata oluştu.</div>';
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

        // Otomatik olarak ilk kişiyi seç
        if (chatItems && chatItems.length > 0 && !currentContactId) {
            chatItems[0].click();
        }
    }

    // Initial Load
    loadContacts();

    // Kişi listesi polling (30 saniye)
    contactPollingInterval = setInterval(() => loadContacts(), 30000);

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

        // CRM zenginleştirme: sipariş + durum (sadece ilk yüklemede)
        const messageArea = document.getElementById('messagesArea');
        const isFirstLoad = messageArea.querySelector('.text-muted') !== null;
        
        if (isFirstLoad) {
            loadOrders(phone);
            loadContactStatus(phone);
        }

        // İlk yükleme değilse (polling), scroll pozisyonunu koru

        if (isFirstLoad) {
            messageArea.innerHTML = '<div class="text-center text-muted my-auto">Mesajlar yükleniyor...</div>';
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
                messageArea.innerHTML = '<div class="text-center text-muted my-auto">Bu müşteriyle henüz mesajınız yok.</div>';
                startPolling(contactId);
                return;
            }

            // Date Badge
            messageArea.innerHTML = `<div class="text-center my-2"><span class="badge bg-light text-secondary border">Bugün</span></div>`;

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
                timeSpan.textContent = timeStr;

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
                messageArea.innerHTML = '<div class="text-center text-danger my-auto">Mesajlar yüklenemedi.</div>';
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
        
        ordersEl.innerHTML = '<div class="text-muted small">Yükleniyor...</div>';
        
        try {
            const orders = await apiFetch(`/orders.php?phone=${phone}`);
            
            if (!orders || orders.length === 0) {
                ordersEl.innerHTML = '<div class="text-muted small"><i class="bi bi-inbox"></i> Sipariş bulunamadı</div>';
                document.getElementById('crmTags').innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 fw-normal">Sipariş Yok</span>';
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
                                    <div class="text-muted" style="font-size: 0.7rem;">${date}</div>
                                </div>
                                <span class="badge bg-${statusColor} bg-opacity-10 text-${statusColor} rounded-pill" style="font-size: 0.7rem;">${order.status || 'Bilinmiyor'}</span>
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
                    showToast('Manuel müdahale aktif — AI yanıtları durduruldu', 'info');
                } else {
                    showToast('Manuel müdahale kapatıldı — AI yanıtları devam ediyor', 'success');
                }
            } catch (error) {
                console.error('Manual list error', error);
                this.checked = !this.checked; // Geri al
                showToast('İşlem başarısız: ' + error.message, 'error');
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
                showToast('Mesaj gönderildi ✓', 'success');
            } catch (error) {
                console.error('Message send failed', error);
                showToast('Mesaj gönderilemedi: ' + error.message, 'error');
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

    function appendMessage(text, type) {
        if (!messageArea) return;

        const div = document.createElement('div');
        div.className = `message-bubble msg-${type}`;

        const content = document.createElement('div');
        content.textContent = text;

        const time = document.createElement('span');
        time.className = 'msg-time';
        const now = new Date();
        time.textContent = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;

        div.appendChild(content);
        div.appendChild(time);

        messageArea.appendChild(div);
    }

    // 6. Sidebar Navigation Interactivity & Toasts
    const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function () {
            // "Gelen Kutusu" is the only truly functional tab for now
            // But we will highlight the clicked one visually
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');

            const title = this.getAttribute('title');
            if (title && title !== 'Gelen Kutusu') {
                showToast(`"${title}" modülü geliştirme aşamasındadır.`, 'info');
            }
        });
    });

    // Helper: Dynamic Premium Toast
    function showToast(message, type = 'info') {
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed bottom-0 start-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast-' + Date.now();
        const iconClasses = type === 'info' ? 'bi-info-circle-fill text-primary' : 'bi-check-circle-fill text-success';

        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-bg-light border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex p-1">
                <div class="toast-body d-flex align-items-center fw-medium text-dark" style="font-size: 0.9rem;">
                  <i class="bi ${iconClasses} fs-5 me-2"></i>
                  ${message}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastEl = document.getElementById(toastId);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
        bsToast.show();

        // Auto remove from DOM after hidden
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
});
