<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PANDU</title>
    <!-- Gunakan Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-image: url('{{ asset('images/background.png') }}');
            background-size: cover;
            background-position: center;
        }
        .reset-password-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="reset-password-card max-w-md w-full p-8">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-20 mb-2">
            <h2 class="text-xl font-bold text-gray-800 text-center">Reset Password</h2>
            <p class="text-sm text-gray-600 text-center mb-4">Buat password baru untuk akun Anda</p>
        </div>
        
        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>{{ $errors->first() }}</p>
            </div>
        @endif
        
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
            <input type="hidden" name="token" value="{{ $token ?? '' }}">
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password Baru</label>
                <input type="password" id="password" name="password" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-primary focus:border-primary" 
                    required>
            </div>
            
            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 text-sm font-medium mb-2">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-primary focus:border-primary" 
                    required>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50">
                Reset Password
            </button>
            
            <div class="flex justify-center mt-4">
                <a href="{{ route('login') }}" class="text-sm text-primary hover:underline flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke halaman login
                </a>
            </div>
        </form>
    </div>
</body>
</html>