<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UD. TPKS</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-tpks.png') }}">
    <!-- Tailwind CSS -->
     @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        input[type="text"], input[type="password"] {
            border: none;
            border-bottom: 2px solid #10B981;
            outline: none;
            background: transparent;
        }
        input:focus {
            border-bottom-color: #059669;
            background: #f0fdf4;
        }
    </style>
</head>
<body class="bg-green-100 flex flex-col min-h-screen">
    <div class="flex flex-1 items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl border border-green-200 p-8 w-full max-w-md relative">
            <!-- Logo -->
            <div class="flex justify-center mb-3">
                <img src="{{ asset('images/logo-tpks.png') }}" alt="Logo UD. TPKS" class="w-36 h-36 object-contain" />
            </div>
            <!-- Judul -->
            <h1 class="text-2xl font-extrabold text-green-800 text-center mb-1 tracking-wide">Sistem Pembukuan Stok Kayu</h1>
            <div class="text-green-700 text-center mb-5 text-sm">UD. Tiga PutraKarya Sentosa</div>

            <!-- Display Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded mb-4 border border-red-200">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf

                <!-- User Name Field -->
                <div class="mb-4">
                    <label for="username" class="block text-green-900 mb-1 font-semibold">User Name :</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}"
                        required class="w-full px-3 py-2 rounded bg-green-50 focus:ring-2 focus:ring-green-300 transition" autocomplete="username">
                </div>

                <!-- Password Field -->
                <div class="mb-6">
                      <label for="password" class="block text-green-900 mb-1 font-semibold">Password :</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                            class="w-full px-3 py-2 rounded bg-green-50 focus:ring-2 focus:ring-green-300 transition"
                            autocomplete="current-password">
                            <button type="button" onclick="togglePassword()" 
                            class="absolute right-3 top-2 text-green-700">
                            <i id="toggleIcon" class="bi bi-eye"></i>
                            </button>
                        </div>
                        <script>
                        function togglePassword() {
                        const input = document.getElementById("password");
                        const icon = document.getElementById("toggleIcon");
                        if (input.type === "password") {
                            input.type = "text";
                            icon.classList.remove("bi-eye");
                            icon.classList.add("bi-eye-slash");
                        } else {
                            input.type = "password";
                            icon.classList.remove("bi-eye-slash");
                            icon.classList.add("bi-eye");
                        }
                        }
                        </script>
                </div>
                

                <!-- Login Button -->
                <div>
                    <button type="submit"
                        class="w-full py-2 bg-green-700 hover:bg-green-800 text-white font-bold rounded shadow transition duration-200">
                        <i class="bi bi-box-arrow-in-right mr-1"></i> Login
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Footer -->
    <footer class="text-center py-3 text-green-900 text-sm opacity-80">
        &copy; {{ date('Y') }} UD. Tiga PutraKarya Sentosa &mdash; Sistem Pembukuan Stok Kayu
    </footer>
    <!-- Bootstrap Icons (for login icon, optional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>