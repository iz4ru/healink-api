<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Healink</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb {
            background: #3A7CF0;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover { background: #21478A; }
    </style>
</head>
<body class="bg-primary-500 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10">

            <!-- Header -->
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo/healink-blue.png') }}" alt="Healink" class="w-32 h-auto mx-auto mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-50 rounded-2xl my-4">
                    <span class="material-symbols-rounded text-primary-500 text-3xl">lock_reset</span>
                </div>
                <h1 class="lg:text-2xl text-xl font-bold text-gray-900">Reset Password</h1>
                <p class="lg:text-lg text-sm text-gray-500 mt-1">Masukkan password baru untuk akun Anda</p>
            </div>

            <!-- Messages -->
            @if(session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex gap-3">
                    <span class="material-symbols-rounded text-success text-xl">check_circle</span>
                    <p class="text-green-800 text-sm">{{ session('status') }}</p>
                </div>
            @endif

            @if($errors->any() || isset($error))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex gap-3">
                        <span class="material-symbols-rounded text-danger text-xl">error</span>
                        <div>
                            <p class="font-semibold text-danger text-sm mb-1">Terjadi kesalahan:</p>
                            <ul class="text-red-700 text-sm space-y-1">
                                @foreach($errors->all() as $err)
                                    <li>• {{ $err }}</li>
                                @endforeach
                                @if(isset($error))<li>• {{ $error }}</li>@endif
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.reset') }}" id="resetForm" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <!-- Password Baru -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Password Baru</label>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
                        <input type="password" id="password" name="password" required minlength="8"
                               placeholder="Minimal 8 karakter"
                               class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-primary-500 focus:bg-white transition-all text-gray-900 placeholder-gray-400"
                               onkeyup="checkStrength(); checkMatch();">
                        <button type="button" onclick="togglePassword('password', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary-600">
                            <span class="material-symbols-rounded text-xl">visibility_off</span>
                        </button>
                    </div>

                    <!-- Strength Indicator -->
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div id="strength-bar" class="h-full w-0 bg-danger transition-all duration-500 rounded-full"></div>
                        </div>
                        <span id="strength-text" class="text-xs font-medium whitespace-nowrap"></span>
                    </div>
                    <p id="strength-hint" class="mt-1 text-xs text-gray-500"></p>
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xl">lock</span>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               placeholder="Ulangi password baru"
                               class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-primary-500 focus:bg-white transition-all text-gray-900 placeholder-gray-400"
                               onkeyup="checkMatch()">
                        <button type="button" onclick="togglePassword('password_confirmation', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary-600">
                            <span class="material-symbols-rounded text-xl">visibility_off</span>
                        </button>
                    </div>
                    <p id="match-message" class="mt-1.5 text-xs font-medium hidden"></p>
                </div>

                <!-- Requirements -->
                <div class="p-4 bg-primary-50 rounded-2xl border border-primary-100">
                    <p class="text-xs font-semibold text-primary-700 mb-3 flex items-center gap-2">
                        <span class="material-symbols-rounded text-sm">info</span>
                        Password setidaknya harus memenuhi salah satu:
                    </p>
                    <ul class="text-xs text-primary-700 space-y-2">
                        <li id="req-length" class="flex items-center gap-2"><span class="material-symbols-rounded text-sm text-gray-400">close</span> Minimal 8 karakter</li>
                        <li id="req-upper" class="flex items-center gap-2"><span class="material-symbols-rounded text-sm text-gray-400">close</span> Huruf kapital (A-Z)</li>
                        <li id="req-number" class="flex items-center gap-2"><span class="material-symbols-rounded text-sm text-gray-400">close</span> Angka (0-9)</li>
                        <li id="req-special" class="flex items-center gap-2"><span class="material-symbols-rounded text-sm text-gray-400">close</span> Karakter spesial (!@#$%^&*)</li>
                    </ul>
                </div>

                <button type="submit" id="submitBtn"
                        class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-4 rounded-2xl transition-all active:scale-95 flex items-center justify-center gap-2 disabled:opacity-70">
                    <span class="material-symbols-rounded">check_circle</span>
                    <span>Reset Password</span>
                </button>
            </form>
        </div>

        <p class="text-center text-white/75 text-sm mt-6">© {{ date('Y') }} Healink. All rights reserved.</p>
    </div>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50:  '#EAF2FF',
                            100: '#D9E6FC',
                            500: '#3A7CF0',
                            600: '#21478A',
                            700: '#1B3A70',
                        },
                        danger:  '#E03C27',
                        warning: '#FFA726',
                        success: '#4CAF50',
                    }
                }
            }
        };

        // Toggle Password Visibility
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('span');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        // Check Strength + Requirements (menggunakan warna danger, success, dll)
        function checkStrength() {
            const pass = document.getElementById('password').value;
            const bar = document.getElementById('strength-bar');
            const text = document.getElementById('strength-text');
            const hint = document.getElementById('strength-hint');

            const hasLen = pass.length >= 8;
            const hasUpper = /[A-Z]/.test(pass);
            const hasNum = /[0-9]/.test(pass);
            const hasSpec = /[!@#$%^&*(),.?":{}|<>]/.test(pass);

            updateReq('req-length', hasLen);
            updateReq('req-upper', hasUpper);
            updateReq('req-number', hasNum);
            updateReq('req-special', hasSpec);

            let score = (hasLen ? 1 : 0) + (hasUpper ? 1 : 0) + (hasNum ? 1 : 0) + (hasSpec ? 1 : 0);

            if (pass.length === 0) {
                bar.style.width = '0%';
                bar.className = 'h-full transition-all duration-500 rounded-full bg-gray-300';
                text.textContent = '';
                hint.textContent = '';
            } else if (score <= 1) {
                bar.style.width = '25%'; bar.className = 'h-full bg-danger transition-all duration-500 rounded-full';
                text.textContent = 'Lemah'; text.className = 'text-xs font-medium text-danger';
                hint.textContent = 'Tambahkan huruf, angka, dan simbol';
            } else if (score === 2) {
                bar.style.width = '50%'; bar.className = 'h-full bg-warning transition-all duration-500 rounded-full';
                text.textContent = 'Sedang'; text.className = 'text-xs font-medium text-warning';
                hint.textContent = 'Tambahkan simbol untuk lebih kuat';
            } else if (score === 3) {
                bar.style.width = '75%'; bar.className = 'h-full bg-blue-500 transition-all duration-500 rounded-full';
                text.textContent = 'Kuat'; text.className = 'text-xs font-medium text-blue-600';
                hint.textContent = 'Cukup aman';
            } else {
                bar.style.width = '100%'; bar.className = 'h-full bg-success transition-all duration-500 rounded-full';
                text.textContent = 'Sangat Kuat'; text.className = 'text-xs font-medium text-success';
                hint.textContent = 'Password sangat aman';
            }
        }

        function updateReq(id, ok) {
            const el = document.getElementById(id);
            const icon = el.querySelector('.material-symbols-rounded');
            if (ok) {
                icon.textContent = 'check';
                icon.className = 'material-symbols-rounded text-sm text-success';
                el.classList.add('text-success');
            } else {
                icon.textContent = 'close';
                icon.className = 'material-symbols-rounded text-sm text-gray-400';
                el.classList.remove('text-success');
            }
        }

        // Check Password Match
        function checkMatch() {
            const p1 = document.getElementById('password').value;
            const p2 = document.getElementById('password_confirmation').value;
            const msg = document.getElementById('match-message');
            const input = document.getElementById('password_confirmation');

            if (p2 === '') {
                msg.classList.add('hidden');
                input.classList.remove('border-danger', 'border-success');
                return;
            }

            if (p1 === p2) {
                msg.textContent = '✓ Password cocok';
                msg.className = 'mt-1.5 text-xs text-success font-medium';
                input.classList.remove('border-danger');
                input.classList.add('border-success');
            } else {
                msg.textContent = '✕ Password tidak cocok';
                msg.className = 'mt-1.5 text-xs text-danger font-medium';
                input.classList.remove('border-success');
                input.classList.add('border-danger');
            }
            msg.classList.remove('hidden');
        }

        // Submit handler
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            if (document.getElementById('password').value !== document.getElementById('password_confirmation').value) {
                e.preventDefault();
                alert('Password dan konfirmasi tidak cocok!');
                return;
            }

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = `<span class="material-symbols-rounded animate-spin">progress_activity</span><span>Memproses...</span>`;
        });

        // Auto hide alerts
        setTimeout(() => {
            document.querySelectorAll('.bg-green-50, .bg-red-50').forEach(el => {
                el.style.transition = 'opacity 0.6s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 600);
            });
        }, 10000);
    </script>
</body>
</html>
