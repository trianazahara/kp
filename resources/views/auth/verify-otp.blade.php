<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - PANDU</title>
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
        .otp-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="otp-card max-w-md w-full p-8">
        <div class="flex flex-col items-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-20 mb-2">
            <h2 class="text-xl font-bold text-gray-800 text-center">Lupa Password</h2>
            <p class="text-sm text-gray-600 text-center mb-4">Verifikasi kode OTP</p>
        </div>
        
        @if(session('status') == 'otp_sent')
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <div class="flex items-start">
                    <svg class="h-5 w-5 mr-2 mt-0.5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p>Kode OTP telah dikirim ke</p>
                        <p class="font-medium">{{ session('maskedEmail') }}</p>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="{{ route('password.verify') }}">
                @csrf
                <div class="mb-4">
                    <input type="text" id="otp" name="otp" placeholder="Masukkan kode OTP" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-primary focus:border-primary" 
                        required>
                </div>
                
                @if($errors->has('otp'))
                    <div class="text-red-500 text-sm mb-4">
                        {{ $errors->first('otp') }}
                    </div>
                @endif
                
                <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50">
                    Verifikasi OTP
                </button>
            </form>
        @else
            <div class="text-center py-4">
                <p class="text-red-500">Sesi Anda telah kedaluwarsa. Silakan memulai proses lupa password kembali.</p>
                <a href="{{ route('password.request') }}" class="mt-4 inline-block text-primary hover:underline">
                    Kembali ke halaman lupa password
                </a>
            </div>
        @endif
        
        <div class="flex justify-center mt-4">
            <a href="{{ route('login') }}" class="text-sm text-primary hover:underline flex items-center">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke halaman login
            </a>
        </div>
    </div>
</body>
</html>