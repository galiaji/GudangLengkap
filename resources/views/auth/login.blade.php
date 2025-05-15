<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gudang Kayu</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom styles for green underline input fields */
        input[type="text"],
        input[type="password"] {
            border: none;
            border-bottom: 2px solid #10B981; /* Tailwind's green-500 */
            outline: none;
        }
        input:focus {
            border-bottom-color: #059669; /* Darker green on focus */
        }
    </style>
</head>
<body class="bg-green-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <!-- Logo/Affiliate Avatar -->
        <div class="flex justify-center mb-6">
            <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center">
                <!-- You can place an avatar image here -->
                <span class="text-white text-3xl font-bold">A</span>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-center mb-6">Login</h1>

        <!-- Display Validation Errors -->
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <ul>
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
                <label for="username" class="block text-gray-700 mb-1">User Name :</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}"
                       required class="w-full px-3 py-2">
            </div>

            <!-- Password Field -->
            <div class="mb-6">
                <label for="password" class="block text-gray-700 mb-1">Password :</label>
                <input type="password" name="password" id="password" required class="w-full px-3 py-2">
            </div>

            <!-- Login Button -->
            <div>
                <button type="submit"
                        class="w-full py-2 text-white font-semibold rounded transition duration-200"
                        style="background-color: #A52A2A;">Login</button>
            </div>
        </form>
    </div>
</body>
</html>