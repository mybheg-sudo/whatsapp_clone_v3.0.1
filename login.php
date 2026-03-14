<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MYBHEG - Kurumsal Giriş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 16px;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(26, 42, 108, 0.08);
            /* brand-primary hint */
            border: 1px solid var(--border-color);
        }

        .login-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--brand-primary);
            text-align: center;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }

        .login-subtitle {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border-color: #e2e8f0;
        }

        .form-control:focus {
            border-color: rgba(26, 42, 108, 0.4);
            box-shadow: 0 0 0 4px rgba(26, 42, 108, 0.1);
        }

        .btn-login {
            background-color: var(--brand-primary);
            color: #ffffff;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-login:hover {
            background-color: var(--brand-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 78, 146, 0.2);
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-logo">MYBHEG</div>
        <div class="login-subtitle">Kurumsal İletişim Paneli Girişi</div>

        <!-- Alert Boxt for Errors -->
        <div id="loginAlert" class="alert alert-danger d-none py-2" role="alert" style="font-size: 0.85rem;">
            Giriş başarısız oldu. Lütfen bilgilerinizi kontrol edin.
        </div>

        <form id="loginForm">
            <div class="mb-3">
                <label for="username" class="form-label">Kullanıcı Adı</label>
                <input type="text" class="form-control" id="username" placeholder="Örn: admin1" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" placeholder="Şifrenizi giriniz" required>
            </div>

            <button type="submit" class="btn btn-login" id="loginBtn">Sisteme Giriş Yap</button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">Yardıma mı ihtiyacınız var? <a href="#" class="text-decoration-none"
                    style="color: var(--brand-secondary)">Destek Ekibi</a> ile iletişime geçin.</small>
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