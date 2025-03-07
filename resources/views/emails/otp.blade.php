<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode OTP Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .otp-box {
            background-color: #f5f5f5;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            letter-spacing: 2px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $appName }}</h2>
        </div>
        
        <p>Halo,</p>
        
        <p>Kami menerima permintaan untuk mengatur ulang password akun {{ $appName }} Anda. Gunakan kode OTP berikut untuk melanjutkan proses reset password:</p>
        
        <div class="otp-box">
            <strong>{{ $otp }}</strong>
        </div>
        
        <p>Kode OTP ini berlaku selama 15 menit. Jika Anda tidak meminta reset password, abaikan email ini.</p>
        
        <p>Terima kasih,<br>Tim {{ $appName }}</p>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ $appName }}. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>