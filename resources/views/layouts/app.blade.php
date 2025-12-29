<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PLFIS - Personal Life & Finance Intelligence System')</title>
    
    <!-- Dynamic Favicon -->
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
    
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    @vite(['resources/css/lite.css', 'resources/js/lite.js'])

    @yield('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            @include('layouts.header')

            <!-- Page Content -->
            <div class="content-container">
                @yield('content')
            </div>
        </div>
    </div>

    @yield('scripts')

    <!-- Private Access Modal -->
    <div id="privateAccessModal" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">Akses Privat</h3>
                <button class="modal-close" onclick="closePrivateModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Masukkan Kata Sandi</label>
                    <input type="password" id="privatePassword" class="form-input" placeholder="Masukkan kode akses...">
                </div>
                <div id="privateError" style="color: var(--danger-color); font-size: 13px; margin-top: 10px; display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closePrivateModal()">Batal</button>
                <button type="button" class="btn-primary" onclick="submitPrivateAccess()">Buka Akses</button>
            </div>
        </div>
    </div>

    <script>
        // Shortcut Listener: Alt + Ctrl + Shift + P
        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.altKey && event.shiftKey && (event.key === 'p' || event.key === 'P')) {
                event.preventDefault();
                openPrivateModal();
            }
        });

        function openPrivateModal() {
            const modal = document.getElementById('privateAccessModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
            document.getElementById('privatePassword').value = '';
            document.getElementById('privatePassword').focus();
            document.getElementById('privateError').style.display = 'none';
        }

        function closePrivateModal() {
            const modal = document.getElementById('privateAccessModal');
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        function submitPrivateAccess() {
            const password = document.getElementById('privatePassword').value;
            const errorDiv = document.getElementById('privateError');

            if (!password) {
                errorDiv.textContent = 'Kata sandi harus diisi.';
                errorDiv.style.display = 'block';
                return;
            }

            // AJAX Check
            fetch("{{ route('private-links.check-password') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ password: password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "{{ route('private-links.index') }}";
                } else {
                    errorDiv.textContent = data.message || 'Akses ditolak.';
                    errorDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.textContent = 'Terjadi kesalahan sistem.';
                errorDiv.style.display = 'block';
            });
        }

        // Enter key in password field
        document.getElementById('privatePassword').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                submitPrivateAccess();
            }
        });
    </script>
</body>
</html>
