<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLFIS - Personal Life & Finance Intelligence</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- 1. SETUP VARIABEL PREMIUM --- */
        :root {
            --primary: #6366f1; /* Indigo modern */
            --primary-dark: #4f46e5;
            --primary-soft: #e0e7ff;
            --secondary: #0f172a; /* Slate dark */
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --glass-blur: 12px;
            
            --radius-xl: 32px;
            --radius-lg: 24px;
            --radius-md: 16px;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-glow: 0 0 40px rgba(99, 102, 241, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif; /* Font lebih modern untuk UI */
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        a { text-decoration: none; color: inherit; transition: 0.3s; }
        ul { list-style: none; }

        /* --- 2. COMPONENTS --- */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 32px;
            border-radius: 99px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            gap: 8px;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px -10px var(--primary);
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 20px 30px -10px var(--primary);
        }

        .btn-outline {
            background: white;
            color: var(--text-main);
            border: 1px solid var(--border);
        }
        .btn-outline:hover {
            border-color: var(--text-main);
            background: var(--text-main);
            color: white;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 16px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            border-radius: 99px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        /* Typography */
        h1 { font-size: 3.5rem; line-height: 1.1; font-weight: 800; letter-spacing: -0.02em; color: var(--secondary); }
        h2 { font-size: 2.5rem; line-height: 1.2; font-weight: 700; letter-spacing: -0.02em; color: var(--secondary); margin-bottom: 1rem;}
        h3 { font-size: 1.5rem; font-weight: 700; color: var(--secondary); }
        p { color: var(--text-muted); font-size: 1.125rem; }

        /* --- 3. NAVBAR GLASS --- */
        .navbar {
            padding: 20px 0;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            background: rgba(248, 250, 252, 0.8); /* Semi transparan */
            backdrop-filter: blur(16px); /* Efek blur premium */
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .nav-content { display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-size: 24px; font-weight: 800; color: var(--secondary); display: flex; align-items: center; gap: 8px; }
        .nav-logo span { width: 10px; height: 10px; background: var(--primary); border-radius: 3px; transform: rotate(45deg); }
        
        .nav-links { display: flex; gap: 40px; background: white; padding: 10px 30px; border-radius: 99px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
        .nav-links a { font-size: 14px; font-weight: 600; color: var(--text-muted); }
        .nav-links a:hover { color: var(--primary); }

        .nav-auth { display: flex; gap: 12px; }

        /* --- 4. HERO SECTION (New Layout) --- */
        .hero {
            padding-top: 180px;
            padding-bottom: 100px;
            position: relative;
            overflow: hidden;
        }
        
        /* Background Abstract Blobs */
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.5;
        }
        .blob-1 { top: -10%; left: -10%; width: 500px; height: 500px; background: #e0e7ff; border-radius: 50%; }
        .blob-2 { bottom: 10%; right: -10%; width: 600px; height: 600px; background: #fce7f3; border-radius: 50%; }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-visual {
            position: relative;
            height: 500px;
        }

        /* CSS-Only Dashboard Mockup */
        .mockup-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.15);
            border: 1px solid rgba(255,255,255,0.8);
            position: absolute;
            transition: transform 0.5s ease;
        }
        
        .card-main {
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 2;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .card-float {
            width: 220px;
            padding: 20px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            z-index: 3;
            border: 1px solid white;
            box-shadow: var(--shadow-lg);
            border-radius: var(--radius-lg);
        }

        /* Skeleton UI for Mockup */
        .sk-line { height: 10px; background: #f1f5f9; border-radius: 4px; margin-bottom: 8px; }
        .sk-circle { width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; }
        .sk-chart { height: 150px; background: linear-gradient(180deg, #eff6ff 0%, white 100%); border-radius: 12px; border: 1px dashed #bfdbfe; display: flex; align-items: end; justify-content: space-around; padding-bottom: 10px; }
        .sk-bar { width: 20px; background: var(--primary); border-radius: 4px; opacity: 0.8; }

        /* --- 5. BENTO GRID FEATURES (Premium Layout) --- */
        .bento-section { padding: 100px 0; }
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 300px);
            gap: 24px;
            margin-top: 50px;
        }

        .bento-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 32px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .bento-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-glow);
            transform: translateY(-4px);
        }

        .bento-large { grid-column: span 2; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border: none; }
        .bento-large h3, .bento-large p { color: white; }
        .bento-large p { opacity: 0.8; }
        .bento-tall { grid-row: span 2; }

        .bento-icon {
            width: 50px; height: 50px;
            background: var(--bg-body);
            color: var(--primary);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .bento-large .bento-icon { background: rgba(255,255,255,0.1); color: white; }

        /* --- 6. AI SECTION (Chat Interface) --- */
        .ai-section {
            background: #000;
            color: white;
            padding: 100px 0;
            border-radius: var(--radius-xl);
            margin: 0 24px;
            position: relative;
            overflow: hidden;
        }
        
        .chat-ui {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-lg);
            padding: 20px;
            max-width: 500px;
            margin: 0 auto;
        }

        .chat-bubble {
            padding: 12px 18px;
            border-radius: 18px;
            margin-bottom: 15px;
            font-size: 15px;
            line-height: 1.5;
        }
        .bubble-user { background: #333; color: white; align-self: flex-end; margin-left: auto; border-bottom-right-radius: 4px; width: fit-content;}
        .bubble-ai { background: var(--primary); color: white; border-bottom-left-radius: 4px; width: fit-content; }

        /* --- 7. FOOTER --- */
        footer { padding: 80px 0 40px; border-top: 1px solid var(--border); background: white; margin-top: 100px;}
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; }
        .footer-title { font-weight: 700; margin-bottom: 20px; color: var(--secondary); font-size: 16px; }
        .footer-link { display: block; margin-bottom: 12px; color: var(--text-muted); font-size: 14px; }
        .footer-link:hover { color: var(--primary); }

        /* --- RESPONSIVE --- */
        @media (max-width: 1024px) {
            h1 { font-size: 2.5rem; }
            .hero-grid { grid-template-columns: 1fr; text-align: center; }
            .hero-visual { display: none; } /* Hide visual on mobile for simplicity */
            .bento-grid { grid-template-columns: 1fr; grid-template-rows: auto; }
            .bento-large, .bento-tall { grid-column: span 1; grid-row: span 1; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <nav class="navbar">
        <div class="container nav-content">
            <a href="#" class="nav-logo"><span></span> PLFIS</a>
            <div class="nav-links">
                <a href="#fitur">Fitur</a>
                <a href="#ai">AI Intelligence</a>
                <a href="#harga">Harga</a>
            </div>
            <div class="nav-auth">
                <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 10px 24px; font-size: 14px; border:0;">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 10px 24px; font-size: 14px;">Daftar</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-text">
                <div class="badge"><i class="fas fa-sparkles"></i> New: AI Finance v2.0</div>
                <h1>Cerdaskan Uang Anda dengan Bantuan AI</h1>
                <p style="margin: 24px 0 40px; max-width: 500px;">
                    Satu platform untuk mengelola cashflow, aset digital, dan keputusan finansial. Bukan sekadar mencatat, tapi memberikan solusi.
                </p>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="{{ route('register') }}" class="btn btn-primary">Mulai Gratis <i class="fas fa-arrow-right"></i></a>
                    <a href="#demo" class="btn btn-outline"><i class="fas fa-play-circle"></i> Lihat Demo</a>
                </div>
                
                <div style="margin-top: 40px; display: flex; align-items: center; gap: 15px; font-size: 14px; color: var(--text-muted);">
                    <div style="display: flex;">
                        <img src="https://ui-avatars.com/api/?name=A&bg=e2e8f0" style="width:30px; border-radius:50%; border:2px solid white;">
                        <img src="https://ui-avatars.com/api/?name=B&bg=e2e8f0" style="width:30px; border-radius:50%; border:2px solid white; margin-left:-10px;">
                        <img src="https://ui-avatars.com/api/?name=C&bg=e2e8f0" style="width:30px; border-radius:50%; border:2px solid white; margin-left:-10px;">
                    </div>
                    <span>Dipercaya 1000+ pengguna aktif</span>
                </div>
            </div>

            <div class="hero-visual">
                <div class="mockup-card card-main" style="transform: rotate(-3deg) translateY(20px);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <div class="sk-line" style="width: 100px; height: 8px;"></div>
                            <div class="sk-line" style="width: 60px; height: 6px;"></div>
                        </div>
                        <div class="sk-circle" style="width: 32px; height: 32px;"></div>
                    </div>
                    <div class="sk-chart">
                        <div class="sk-bar" style="height: 40%"></div>
                        <div class="sk-bar" style="height: 70%"></div>
                        <div class="sk-bar" style="height: 50%"></div>
                        <div class="sk-bar" style="height: 90%"></div>
                        <div class="sk-bar" style="height: 60%"></div>
                    </div>
                    <div style="background: #f8fafc; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 15px;">
                        <div class="sk-circle"></div>
                        <div style="flex: 1;">
                            <div class="sk-line" style="width: 80%"></div>
                            <div class="sk-line" style="width: 40%"></div>
                        </div>
                        <div style="font-weight: bold; color: #ef4444;">-Rp 50k</div>
                    </div>
                </div>

                <div class="mockup-card card-float" style="top: 50%; right: 0; transform: translateY(-50%);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <i class="fas fa-robot" style="color: var(--primary);"></i>
                        <span style="font-size: 12px; font-weight: 700;">AI Insight</span>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); line-height: 1.4;">
                        "Pengeluaran kopi Anda naik 20% minggu ini. Saran: kurangi frekuensi ke kafe."
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="bento-section">
        <div class="container">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 50px;">
                <h2 style="font-size: 2rem;">Semua Kebutuhan Finansial. <br>Dalam Satu Tempat.</h2>
                <p>Desain modular yang menyesuaikan gaya hidup Anda.</p>
            </div>

            <div class="bento-grid">
                <div class="bento-card bento-large">
                    <div>
                        <div class="bento-icon"><i class="fas fa-brain"></i></div>
                        <h3>AI Personal Assistant</h3>
                        <p style="margin-top: 10px; font-size: 16px;">
                            Tanyakan apa saja: "Apakah saya sanggup beli iPhone bulan ini?" atau "Buatkan rencana menabung 5 juta." AI akan menghitung berdasarkan data nyata Anda.
                        </p>
                    </div>
                    <div style="margin-top: 30px; display: flex; gap: 10px;">
                        <span style="background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 99px; font-size: 12px;">Smart Budgeting</span>
                        <span style="background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 99px; font-size: 12px;">Risk Analysis</span>
                    </div>
                </div>

                <div class="bento-card bento-tall">
                    <div class="bento-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Secure Vault</h3>
                    <p style="margin-top: 10px; font-size: 14px;">Simpan akun digital, password e-wallet, dan dokumen aset dengan enkripsi tingkat tinggi.</p>
                    <div style="margin-top: auto; text-align: center; opacity: 0.5;">
                        <i class="fas fa-lock" style="font-size: 80px; color: var(--border);"></i>
                    </div>
                </div>

                <div class="bento-card">
                    <div class="bento-icon"><i class="fas fa-chart-pie"></i></div>
                    <h3>Analitik Visual</h3>
                    <p style="font-size: 14px;">Grafik cashflow yang indah dan mudah dipahami dalam sekali lihat.</p>
                </div>

                <div class="bento-card">
                    <div class="bento-icon"><i class="fas fa-microphone"></i></div>
                    <h3>Voice Command</h3>
                    <p style="font-size: 14px;">"Catat pengeluaran 20 ribu untuk parkir." Cukup bicara, selesai.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="ai" class="ai-section">
        <div class="container" style="position: relative; z-index: 2;">
            <div style="text-align: center; margin-bottom: 50px;">
                <span style="color: #a5b4fc; font-weight: 600; letter-spacing: 1px; font-size: 12px; text-transform: uppercase;">Next Gen Technology</span>
                <h2 style="color: white; margin-top: 10px;">Bicara dengan Data Anda</h2>
            </div>

            <div class="chat-ui">
                <div class="chat-bubble bubble-user">
                    Berapa sisa uang makan saya?
                </div>
                <div class="chat-bubble bubble-ai">
                    <i class="fas fa-sparkles" style="margin-right: 5px;"></i>
                    Sisa budget makan: <strong>Rp 450.000</strong>. Jika Anda ingin berhemat untuk target liburan, saya sarankan batasi Rp 50.000/hari minggu ini.
                </div>
                <div class="chat-bubble bubble-user" style="margin-top: 20px;">
                    Oke, atur pengingat harian ya.
                </div>
                <div style="margin-top: 20px; background: rgba(255,255,255,0.1); border-radius: 99px; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; color: #94a3b8; font-size: 14px;">
                    <span>Ketik pesan finansial Anda...</span>
                    <i class="fas fa-arrow-up" style="background: white; color: black; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"></i>
                </div>
            </div>
        </div>
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 600px; height: 600px; background: var(--primary); filter: blur(150px); opacity: 0.2; pointer-events: none;"></div>
    </section>

    <section style="padding: 100px 0; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; max-width: 700px; margin: 0 auto 20px;">Mulai Perjalanan Kebebasan Finansial Anda.</h2>
            <p style="margin-bottom: 40px;">Tanpa kartu kredit. Batalkan kapan saja.</p>
            <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 16px 48px; font-size: 18px;">Buat Akun Gratis</a>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a href="#" class="nav-logo" style="margin-bottom: 20px;"><span></span> PLFIS</a>
                    <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6;">
                        Membantu generasi muda mengelola keuangan dengan cerdas melalui integrasi AI dan keamanan tingkat tinggi.
                    </p>
                </div>
                <div>
                    <div class="footer-title">Produk</div>
                    <a href="#" class="footer-link">Fitur Utama</a>
                    <a href="#" class="footer-link">Harga</a>
                    <a href="#" class="footer-link">Integrasi</a>
                </div>
                <div>
                    <div class="footer-title">Perusahaan</div>
                    <a href="#" class="footer-link">Tentang Kami</a>
                    <a href="#" class="footer-link">Karir</a>
                    <a href="#" class="footer-link">Blog</a>
                </div>
                <div>
                    <div class="footer-title">Legal</div>
                    <a href="#" class="footer-link">Kebijakan Privasi</a>
                    <a href="#" class="footer-link">Syarat Layanan</a>
                </div>
            </div>
            <div style="margin-top: 60px; padding-top: 30px; border-top: 1px solid var(--border); text-align: center; font-size: 13px; color: var(--text-muted);">
                &copy; {{ date('Y') }} PLFIS Intelligence System. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>