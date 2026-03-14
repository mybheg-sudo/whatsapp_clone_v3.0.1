# 🚀 MYBHEG Kapsamlı Sistem Mimarisi & Altyapı Analizi
*(Coolify DevOps ve n8n MCP verileri doğrulamasında hazırlanmıştır.)*

Bu belge, sunucularınızdaki mevcut mimariyi uçtan uca analiz eden, bağlamları, servisleri ve veritabanı akışlarını anlatan en güncel mimari rapordur.

---

## 🏗 1. Coolify Altyapısı (Konteyner ve Servis Durumu)

Sisteminiz, Docker tabanlı olup Coolify üzerinde barındırılmaktadır. Canlı taranan verilere göre aktif olarak çalışan sunucu servisleri ve uygulamalarınız şunlardır:

### 🌐 A. Genel Uygulamalar (Applications)
1. **whatsapp_clone_v3.0.1 (PHP Web App):** 
   - **Tipi:** Github üzerinden çekilen Nixpacks / PHP uygulaması.
   - **URL:** `https://h8ocgwssg4o0kkooc8skw8ks.motomotomasyon.com`
   - **Durum:** Running (Aktif)
   - **Görev:** Müşteri temsilcilerinin mesajları okuyup cevapladığı, ayarları değiştirdiği frontend/backend kabuğudur. Doğrudan MySQL ile ve asenkron olarak n8n webhook'ları ile haberleşir.

### 🗄 B. Ana Veritabanları (Databases)
1. **whatsapp_clone_db (MySQL 8):**
   - **Genel Port:** `33060` (Dışa açık)
   - **Görev:** Panelin kalbidir. Sistemin kişiler (`contacts`), mesajlar (`messages`) ve sistem kullanıcıları (`users`) burada tutulur. 
   - **Bağlantılar:** Hem PHP uygulaması hem n8n bu MySQL'de okuma-yazma yapar.

### 📦 C. Kurumsal Servisler (Services / Docker Compose)
Coolify üzerinde `Service` mimarisiyle kurulmuş, birbirine bağlı kompleks modüller:

1. **n8n-with-postgresql** (`n8n.motomotomasyon.com`):
   - İçerisinde Node tabanlı otomasyon aracı **n8n**, arka plan görevleri için **task-runners** ve n8n'in kendi kritik konfigürasyonlarını sakladığı bir adet **PostgreSQL (16)** barındırır.
   - **Görev:** Webhookları (`webhook`, `API: WhatsApp Mesaj Gönder`, vb.) dinleyen, dış dünyaya açılan yapay zeka ve orkestrasyon merkezidir.

2. **evolution-api** (`evo.motomotomasyon.com`):
   - İçinde **Evolution API v2.3.7**, **Redis**, ve **PostgreSQL** barındırır.
   - **Görev:** Resmi olmayan (veya On-Premises Meta API) WhatsApp bağlantısı sağlayan mesajlaşma motorudur. RabbitMQ/Webhook yetenekleri olup muhtemelen n8n üzerinden WhatsApp numaranızı bağlamak için kullanılmaktadır.

3. **chatwoot** (`chatwoot.motomotomasyon.com`):
   - İçinde **Chatwoot** (Ruby on Rails), **Sidekiq** (Worker), **PostgreSQL** (pgvector destekli) ve **Redis** barındırır.
   - **Görev:** Olası bir Omnichannel (çok kanallı) müşteri hizmetleri denemeniz veya alternatif müşteri paneli. (Mevcut PHP Whatsapp Clone projesine rakip veya paralel kullanılan profesyonel çözüm).

4. **pgadmin**:
   - PostgreSQL veritabanlarınızı (n8n, chatwoot, evolution) görsel olarak yönetmek için açık bırakılmış UI paneli.

---

## 🧠 2. N8n Otomasyon & İş Akışı Analizi

N8n sunucunuz (v2.36.1) aktif ve sağlıklıdır. 24 adet tanımlı workflow (iş akışı) tespit edilmiş olup operasyonel kritik olan aktif olanlar şunlardır:

### 🟢 Aktif API / Entegrasyon İş Akışları (Web Uygulamasının Konuştuğu Nodelar)
- **`webhook` (ID: 0U7CFiCY28404otR):** WhatsApp'tan Evolution API (veya Meta) aracılığıyla dönen tüm mesajları dinleyen ve MySQL'e yazan / AI yanıtlarını tetikleyen ana gövde.
- **`API: WhatsApp Mesaj Gönder` (ID: 4kI4MAL0ef7KLUuy):** Müşteri temsilcinizin PHP Panelinden "Gönder" dediğinde tetiklenen, mesajı WhatsApp'a ve ardından `messages` adlı MySQL tablosuna "giden" olarak kaydeden görevdir.
- **`API: Manuel Listeye Ekle` ve `API: Manuel Listeden Çıkar`:** Web panelinden temsilcinin "AI sussun ben devralıyorum" veya "Açığı devrediyorum" butonları için kullandığı webhook'lardır. Bunlar MySQL `contact_status` gibi verilerle haberleşir.
- **`API: Sipariş Sorgula` ve `API: Manuel Durum Sorgula`:** CRM veya raporlamalardaki anlık müşteri/sipariş bilgisi için oluşturulan dinleyicilerdir.

### 🛠 Yardımcı (Tool) ve Diğer Aktif Akışlar
- **`TOOL_Urun_Bilgisi` & `TOOL_Adres_Guncelle`:** AI motorunun (muhtemelen OpenAI) ürün bulmak veya adres değiştirmek istediğinde çağırdığı Function Calling veya Sub-workflow akışlarıdır.
- **`shopify to db`:** Dış Shopify e-ticaret sitenizden gelen ürünleri/müşterileri kendi yerel veritabanınıza eşitlemek için çalışan senkronizasyon botudur.

---

## 🔄 3. Sistemin Uçtan Uca Veri Akışı (Şeması)

Sistem heterojen (çoklu dil ve db barındıran) bir yapıya sahiptir. Süreç özetle şu şekilde akar:

1. **Meta / Müşteri ➡️ Evolution API:** Müşteri WhatsApp'a mesaj atar. Numaranız **Evolution API'ye** bağlıdır.
2. **Evolution API ➡️ n8n Webhook:** Evolution API, bu mesajı anında n8n ana **`webhook`** akışına fırlatır.
3. **n8n Karar Mekanizması:** n8n mesajı alır, ana MySQL'e sorar *(Bu müşteri manuel mi yapay zeka mı?)*. 
  - AI ise, `TOOL_Urun_Bilgisi` vb. akışları da kullanarak ChatGPT'ye verir. 
  - Gelen yanıtı n8n, MySQL'e kaydeder (**messages tablosu**) ve ardından tekrar Evolution API'ye göndererek müşteriye ulaştırır.
4. **PHP Web UI (WhatsApp Clone) ➡️ MySQL:** Müşteri temsilcileriniz saniyede bir Frontend tarafıyla `MySQL` veritabanını tarayarak (polling - anlık çekim) son mesajı ekranda görürler.
5. **PHP Web UI ➡️ n8n:** Temsilci cevap yazdığında PHP arayüzü JavaScript async `fetch` yardımıyla n8n'deki **`API: WhatsApp Mesaj Gönder`** isimli akışı tetikler (Webhook POST). Ve akış tekrardan çalışır.

---

## 🔒 4. Mimari Değerlendirme & Güvenlik Riskleri

### 🟢 Gelişmiş Özellikler
1. **Mikroservis Ayrımı:** Front-end, mesajlaşma sunucusu (Evolution), Beyin (N8n) ve Raporlama harika şekilde konteynerler halinde ayrılmıştır. Sunucunun çökme veya tek noktadan yıkılma ihtimali (SPOF) düşüktür.
2. **Kendi Bağımsız DB'leri:** Chatwoot'un, N8n'in ve Evolution'un kendi iç parametrelerini kendi PostgreSQL veritabanlarında tutup, ticari datayı MySQL 8.0'de merkezileştirmesi kusursuz bir mimari tercihtir.

### 🔴 Riskli Özellikler (Müdahale İsteyen Kısımlar)
1. **PHP Backdoor & Sızma İhtimali:** Web uygulamanızdaki `api/login.php` içinde yerleşik `admin1` `password123` isimli açık unutulmuş bir arka kapı mevcuttur. Acilen kapatılmalıdır.
2. **Web Uygulaması Auth (Kimlik Doğrulama) Zafiyeti:** JS Base64 mock kullanılmıştır, sahte token'larla yönetici paneline girilebilir. (JWT şifrelemesi acilen kurulmalıdır).
3. **N8n Webhook Açıklığı:** `API: WhatsApp Mesaj Gönder` gibi Webhook Nodelarına **Basic Auth** veya **Header tabanlı Token** koruması eklenmemiştir. Webhook linkini bulan hacker'lar WhatsApp numaranız üzerinden bot-spam gönderimi yapabilir veya numarayı banlatabilir.
4. **Performans (DDOS Benzeri Web Paneli) Kaybı:** PHP projenizdeki `assets/js/app.js`, **WebSocket (Socket.io tarzı)** bir yapı yerine **Saniyede Bir MySQL sorgulama (Polling)** sistemi kullanmaktadır. Veritabanı biraz dolduğunda Coolify sunucunuz saniyede binlerce isteği işleyemeyip kilitlenecektir.
5. **Açık MySQL Portu:** `33060` nolu port internete açıktır. Mümkün mertebe SSH Tunnel veya coolify internal networkü dışında izole edilmeli veya çok sert şifreler (mevcut şifreniz güçlü) sık sık otomatik rotasyona tabi tutulmalıdır.

*(Rapor kullanıcının isteği üzerine derin otomasyon ve sunucu analitiğine girilerek detaylandırılmıştır.)*
