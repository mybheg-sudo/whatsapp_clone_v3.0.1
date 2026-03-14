<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MYBHEG Kurumsal İletişim Paneli - Giriş">
    <meta name="theme-color" content="#1E293B">
    <title>MYBHEG - Kurumsal Giriş</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="manifest" href="manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="assets/js/theme.js"></script>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-main);
            /* Soft animated background for login */
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.2) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.05) 0px, transparent 50%);
            animation: bgShift 15s ease-in-out infinite alternate;
        }

        @keyframes bgShift {
            0% { background-position: 0% 0%; }
            100% { background-position: 100% 100%; }
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 3rem 2.5rem;
            border-radius: 24px;
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .login-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 0.5rem;
            letter-spacing: 1.5px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.85rem 1.25rem;
            background-color: var(--bg-main);
            border: 1px solid var(--border-light);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: var(--trans-smooth);
        }

        .form-control:focus {
            background-color: var(--bg-panel-solid);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px var(--accent-glow);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 1.05rem;
            letter-spacing: 0.5px;
            padding: 0.85rem;
            border-radius: 12px;
            width: 100%;
            transition: var(--trans-smooth);
            border: none;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(30, 41, 59, 0.2);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 41, 59, 0.3);
            color: #ffffff;
        }

        .btn-login:active {
            transform: translateY(0);
        }
    </style>
</head>

<body>

    <div class="login-card glass-panel">
        <div class="login-logo">MYBHEG</div>
        <div class="login-subtitle">Kurumsal İletişim Paneli</div>

        <!-- Alert Boxt for Errors -->
        <div id="loginAlert" class="alert alert-danger d-none py-2" role="alert" style="font-size: 0.85rem; border-radius: 10px;">
            Giriş başarısız oldu. Lütfen bilgilerinizi kontrol edin.
        </div>

        <form id="loginForm">
            <div class="mb-3">
                <label for="username" class="form-label">Kullanıcı Adı</label>
                <input type="text" class="form-control" id="username" placeholder="Örn: admin" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" placeholder="Şifrenizi giriniz" required>
            </div>

            <button type="submit" class="btn btn-login" id="loginBtn">Sisteme Giriş Yap</button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted" style="font-size: 0.8rem;">
                Yardıma mı ihtiyacınız var? 
                <a href="#" class="text-decoration-none fw-bold" style="color: var(--accent-color)">Destek Ekibi</a> ile iletişime geçin.
            </small>
        </div>
    </div>

    <!-- Script to handle JWT Authentication -->
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const usernameInput = document.getElementById('username').value.trim();
            const passwordInput = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const alertBox = document.getElementById('loginAlert');

            // Reset UI
            alertBox.classList.add('d-none');
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Giriş Yapılıyor...';

            try {
                // Node.js yerine Yerel PHP API'sine istek atıyoruz
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: usernameInput,
                        password: passwordInput
                    })
                });

                const data = await response.json();

                if (response.ok && data.token) {
                    // Success! Save token and user details to localStorage
                    localStorage.setItem('mybheg_auth_token', data.token);
                    localStorage.setItem('mybheg_user', JSON.stringify(data.user));

                    // Redirect to the main dashboard
                    window.location.href = 'index.php';
                } else {
                    // Unauthorized or Server Error
                    throw new Error(data.message || 'Giriş başarısız.');
                }

            } catch (error) {
                console.error("Login Error:", error);

                let errorMsg = "Bağlantı hatası: Sisteme ulaşılamıyor (Port 3000 kapalı olabilir).";
                if (error.message !== "Failed to fetch") {
                    errorMsg = "Kullanıcı adı veya şifre hatalı.";
                }

                alertBox.textContent = errorMsg;
                alertBox.classList.remove('d-none');
            } finally {
                // Restore Button State
                loginBtn.disabled = false;
                loginBtn.innerHTML = 'Sisteme Giriş Yap';
            }
        });

        // If already logged in, redirect to dashboard immediately
        window.addEventListener('DOMContentLoaded', () => {
            const token = localStorage.getItem('mybheg_auth_token');
            if (token) {
                // Pre-flight check could go here later
                window.location.href = 'index.php';
            }
        });
    </script>
</body>

</html>