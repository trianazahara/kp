<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PANDU</title>
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
        .login-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="login-card max-w-md w-full p-8">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Dinas Pendidikan Sumbar" class="h-24 mb-2">
            <h2 class="text-2xl font-bold text-primary text-center">PANDU</h2>
            <p class="text-sm text-gray-600 text-center">Silahkan masuk ke akun anda</p>
        </div>
        
        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>{{ $errors->first() }}</p>
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <input type="text" id="username" name="username" placeholder="Username" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-primary focus:border-primary" 
                    required>
            </div>
            
            <div>
                <input type="password" id="password" name="password" placeholder="Password" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-primary focus:border-primary" 
                    required>
                <div class="flex justify-end mt-1">
                <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">Lupa Password?</a>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50">
                MASUK
            </button>
        </form>
    </div>
</body>
</html>