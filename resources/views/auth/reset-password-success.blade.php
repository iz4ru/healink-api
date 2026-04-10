<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Berhasil Diubah | Healink</title>

    <!-- Google Fonts: Figtree -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Google Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
    </style>
</head>
<body class="bg-primary-500 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 text-center">

            <!-- Success Icon -->
            <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-2xl my-4">
                <span class="material-symbols-rounded text-success text-3xl">check_circle</span>
            </div>

            <!-- Title -->
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
                Password Berhasil Diubah!
            </h1>

            <p class="text-gray-500 text-sm md:text-base mb-8">
                Password akun Anda telah berhasil diubah. Silakan login kembali menggunakan password baru.
            </p>

            <!-- Buttons -->
            <div class="space-y-3">
                <a
                    href="healink://login"
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-4 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl"
                >
                    <span class="material-symbols-rounded">login</span>
                    <span>Buka Aplikasi Healink</span>
                </a>
            </div>

            <!-- Info -->
            <div class="mt-6 p-4 bg-primary-50 rounded-xl border border-primary-100">
                <p class="text-xs text-primary-700 flex items-start gap-2">
                    <span class="material-symbols-rounded text-sm flex-shrink-0">info</span>
                    <span>
                        Jika aplikasi tidak terbuka otomatis, silakan buka aplikasi Healink secara manual dan login dengan password baru Anda.
                    </span>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-white/80 text-sm">
                © {{ date('Y') }} Healink. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>
