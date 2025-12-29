<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - PLFIS</title>
        <!-- Dynamic Favicon -->
    <link rel="icon" id="appFavicon" type="image/png" href="{{ asset('image/logo/favicon-dark.png') }}">
    <script>
        // Preload Theme Logic
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            
            // Set Theme Immediately
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- RESET & VARIABLES --- */
        :root {
            --bg-color: #0f0f12;
            --card-bg: #18181b;
            --primary: #8b5cf6;
            --primary-hover: #7c3aed;
            --text-main: #ffffff;
            --text-muted: #a1a1aa;
            --border-color: #27272a;
            --input-bg: #27272a;
            --radius-iphone: 30px;
            --radius-input: 10px;
            --danger-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            min-height: 100vh;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
        }

        /* --- MAIN LAYOUT (DESKTOP FIRST OPTIMIZATION) --- */
        .main-wrapper {
            width: 100%;
            max-width: 1100px;
            height: 90vh;
            max-height: 720px;
            display: grid;
            grid-template-columns: 45% 55%;
            gap: 20px;
            padding: 0 20px;
        }

        /* --- LEFT PANEL (IMAGE) --- */
        .left-panel {
            position: relative;
            background-image: url('https://images.unsplash.com/photo-1542401886-65d6c61db217?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80');
            background-size: cover;
            background-position: center;
            border-radius: var(--radius-iphone);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 30px;
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.6);
            height: 100%;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.3), rgba(15, 15, 18, 0.95));
            z-index: 1;
        }

        .left-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logo {
            font-size: 20px;
            font-weight: 800;
            color: white;
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo span {
            width: 6px;
            height: 6px;
            background: var(--primary);
            border-radius: 50%;
        }

        .caption h2 {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 15px;
            color: white;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .dots { display: flex; gap: 6px; }
        .dot {
            width: 6px; height: 6px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transition: all 0.3s;
            cursor: pointer;
        }
        .dot.active { width: 20px; background: white; border-radius: 4px; }

        /* --- RIGHT PANEL (FORM) --- */
        .right-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
            width: 100%;
            height: 100%;
        }

        .form-container {
            width: 100%;
            max-width: 420px;
        }

        h1 {
            font-size: 26px;
            color: var(--text-main);
            margin-bottom: 5px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .subtitle a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        /* Inputs */
        .input-group {
            margin-bottom: 12px;
            position: relative;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            background: var(--input-bg);
            border: 2px solid transparent;
            border-radius: var(--radius-input);
            color: white;
            font-size: 13px;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input::placeholder { color: #52525b; }

        input:focus {
            background: #1e1e24;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }

        input.is-invalid {
            border-color: var(--danger-color);
        }
        
        .error-message {
            color: var(--danger-color);
            font-size: 11px;
            margin-top: 3px;
            display: block;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #71717a;
            cursor: pointer;
            font-size: 13px;
            z-index: 10;
        }

        /* Terms */
        .terms {
            display: flex;
            gap: 8px;
            align-items: center;
            color: var(--text-muted);
            font-size: 12px;
            margin-bottom: 18px;
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            margin: 0;
        }

        /* Buttons */
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border: none;
            border-radius: var(--radius-input);
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 8px 25px -8px rgba(139, 92, 246, 0.6);
            margin-bottom: 0;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px -8px rgba(139, 92, 246, 0.8);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 18px 0;
            color: #52525b;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #3f3f46;
        }
        .divider span { padding: 0 10px; }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 10px;
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-input);
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-google:hover {
            background: var(--card-bg);
            border-color: #52525b;
            transform: translateY(-2px);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 10px;
            border-radius: var(--radius-input);
            margin-bottom: 15px;
            color: var(--danger-color);
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* --- DESKTOP SPECIFIC OVERRIDE --- */
        @media (min-width: 1024px) {
            body {
                height: 100vh;
                overflow: hidden; 
                padding: 0;
            }
        }

        /* --- RESPONSIVE TABLET/MOBILE --- */
        @media (max-width: 1024px) {
            body {
                height: auto;
                min-height: 100vh;
                padding: 40px 20px;
                overflow-y: auto;
                align-items: flex-start;
            }

            .main-wrapper {
                display: flex;
                flex-direction: column;
                height: auto;
                max-height: none;
                gap: 0;
                max-width: 450px;
            }

            .left-panel { display: none; }
            .right-panel { padding: 0; }

            .form-container {
                background: var(--card-bg);
                padding: 30px;
                border: 1px solid var(--border-color);
                border-radius: 20px;
                box-shadow: 0 10px 40px -10px rgba(0,0,0,0.5);
            }
        }

        @media (max-width: 640px) {
            body { padding: 20px 10px; }
            .form-container { padding: 20px; }
        }
    </style>
</head>

<body>

    <div class="main-wrapper">
        <div class="left-panel">
            <div class="left-content">
                <div class="logo">PLFIS <span></span></div>

                <div class="bottom-info">
                    <div class="caption">
                        <h2>Kelola Keuangan,<br>Masa Depan Cerah</h2>
                    </div>
                    <div class="dots">
                        <div class="dot active"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="form-container">
                <h1>Daftar Akun</h1>
                <p class="subtitle">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>

                @if ($errors->any())
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>Cek kembali input Anda.</div>
                </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}">
                    @csrf
                    
                    <div class="input-group">
                        <input type="text" name="name" 
                               placeholder="Nama Lengkap" 
                               value="{{ old('name') }}" 
                               class="@error('name') is-invalid @enderror" 
                               required autofocus>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <input type="email" name="email" 
                               placeholder="Email address" 
                               value="{{ old('email') }}"
                               class="@error('email') is-invalid @enderror"
                               required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <input type="password" name="password" id="password"
                               placeholder="Buat Password (Min 8 karakter)"
                               class="@error('password') is-invalid @enderror" 
                               required>
                        <i class="fa-regular fa-eye password-toggle" onclick="togglePassword('password')"></i>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="password_confirm"
                               placeholder="Konfirmasi Password" 
                               required>
                        <i class="fa-regular fa-eye password-toggle" onclick="togglePassword('password_confirm')"></i>
                    </div>

                    <div class="terms">
                        <input type="checkbox" id="terms" required>
                        <label for="terms">Setuju dengan <a href="#">Syarat & Ketentuan</a></label>
                    </div>

                    <button type="submit" class="btn-submit">Daftar Sekarang</button>

                    <div class="divider">
                        <span>Atau dengan</span>
                    </div>

                    <a href="{{ route('auth.google') }}" class="btn-google">
                        <i class="fab fa-google" style="color:#ea4335; font-size: 16px;"></i>
                        Daftar dengan Google
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>