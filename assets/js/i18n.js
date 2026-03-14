/**
 * MYBHEG i18n — Multi-language Support
 * Supports TR (default) and EN
 * Uses data-i18n attributes for text, data-i18n-placeholder for placeholders
 */
(function() {
    'use strict';

    const STORAGE_KEY = 'mybheg_lang';

    const translations = {
        tr: {
            messages: 'Mesajlar',
            search_customer: 'Müşteri ara...',
            contacts: 'Kişiler',
            reports: 'Raporlar',
            settings: 'Ayarlar',
            send_placeholder: 'Müşteriye yanıt yazın...',
            login: 'Sisteme Giriş Yap',
            username: 'Kullanıcı Adı',
            password: 'Şifre',
            customer_summary: 'Müşteri Özeti',
            name_surname: 'Ad Soyad',
            contact_info: 'İletişim',
            segment_status: 'Segment & Durum',
            last_orders: 'Son Siparişler',
            automation: 'Otomasyon',
            manual_intervention: 'Manuel Müdahale',
            manual_desc: 'AI yanıtlarını durdur ve sohbeti kilitle.',
            ai_responding: 'Yapay Zeka Yanıtlıyor',
            manual_active: 'Manuel Müdahale',
            no_conversations: 'Henüz görüşme bulunmuyor.',
            no_results: 'Sonuç bulunamadı.',
            connection_error: 'Bağlantı hatası.',
            sent_success: 'Mesaj başarıyla iletildi.',
            send_failed: 'Mesaj iletilemedi.',
            today_messages: 'Bugünün Mesajları',
            active_contacts: 'Aktif Müşteriler',
            pending: 'Bekleyen',
            theme_toggle: 'Tema Değiştir',
            lang_toggle: 'Dil'
        },
        en: {
            messages: 'Messages',
            search_customer: 'Search customer...',
            contacts: 'Contacts',
            reports: 'Reports',
            settings: 'Settings',
            send_placeholder: 'Type a reply to customer...',
            login: 'Sign In',
            username: 'Username',
            password: 'Password',
            customer_summary: 'Customer Summary',
            name_surname: 'Full Name',
            contact_info: 'Contact',
            segment_status: 'Segment & Status',
            last_orders: 'Recent Orders',
            automation: 'Automation',
            manual_intervention: 'Manual Intervention',
            manual_desc: 'Stop AI replies and lock conversation.',
            ai_responding: 'AI Responding',
            manual_active: 'Manual Mode',
            no_conversations: 'No conversations yet.',
            no_results: 'No results found.',
            connection_error: 'Connection error.',
            sent_success: 'Message sent successfully.',
            send_failed: 'Message could not be sent.',
            today_messages: "Today's Messages",
            active_contacts: 'Active Customers',
            pending: 'Pending',
            theme_toggle: 'Toggle Theme',
            lang_toggle: 'Language'
        }
    };

    function getCurrentLang() {
        return localStorage.getItem(STORAGE_KEY) || 'tr';
    }

    function applyTranslations(lang) {
        const dict = translations[lang] || translations.tr;
        
        // Text elements
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key]) el.textContent = dict[key];
        });

        // Placeholder elements
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (dict[key]) el.placeholder = dict[key];
        });

        // Title elements
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            if (dict[key]) el.title = dict[key];
        });

        // Update lang button label
        const langBtn = document.getElementById('langToggleBtn');
        if (langBtn) {
            langBtn.textContent = lang.toUpperCase();
        }

        document.documentElement.lang = lang;
        localStorage.setItem(STORAGE_KEY, lang);
    }

    // Apply on load
    document.addEventListener('DOMContentLoaded', () => {
        applyTranslations(getCurrentLang());
    });

    // Expose global functions
    window.toggleLanguage = function() {
        const current = getCurrentLang();
        const next = current === 'tr' ? 'en' : 'tr';
        applyTranslations(next);
    };

    window.t = function(key) {
        const lang = getCurrentLang();
        return (translations[lang] || translations.tr)[key] || key;
    };
})();
