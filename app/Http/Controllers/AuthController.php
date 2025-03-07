<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Update last_login
            $user = Auth::user();
            $user->last_login = Carbon::now();
            $user->save();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ]);
    }

    // Proses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }

    // Menampilkan halaman lupa password
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // Proses untuk mengirim OTP
public function sendOTP(Request $request)
{
    $request->validate([
        'username' => 'required',
    ]);

    $user = User::where('username', $request->username)->first();

    if (!$user) {
        return back()->withErrors([
            'username' => 'Username tidak ditemukan dalam sistem kami.',
        ]);
    }

    // Check if user has email
    if (!$user->email) {
        return back()->withErrors([
            'username' => 'Akun ini tidak memiliki email terdaftar. Silakan hubungi administrator.',
        ]);
    }

    // Generate OTP 6 digit
    $otp = sprintf("%06d", mt_rand(1, 999999));
    $user->otp = $otp;
    $user->otp_expires_at = Carbon::now()->addMinutes(15);
    $user->save();

    // Kirim email dengan OTP
    $this->sendOTPEmail($user->email, $otp);
    
    // Masking email untuk ditampilkan ke pengguna
    $maskedEmail = $this->maskEmail($user->email);
    
    // Simpan data di session dan redirect ke halaman OTP
    session([
        'status' => 'otp_sent',
        'email' => $user->email,
        'username' => $user->username,
        'maskedEmail' => $maskedEmail
    ]);
    
    return redirect()->route('password.otp');
}
    
    // Menampilkan form input OTP
    public function showOTPForm()
{
    // Debug session data
    \Log::info('Session data in showOTPForm:', [
        'email' => session('email'),
        'status' => session('status')
    ]);
    
    if (!session('email') || !session('status')) {
        return redirect()->route('password.request')
            ->withErrors(['email' => 'Sesi telah berakhir. Silakan coba lagi.']);
    }
    
    return view('auth.verify-otp');
}

    // Proses verifikasi OTP
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        // Ambil email dari session
        $email = session('email');
        
        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi telah berakhir. Silakan coba lagi.']);
        }

        $user = User::where('email', $email)
                    ->where('otp', $request->otp)
                    ->where('otp_expires_at', '>', Carbon::now())
                    ->first();

        if (!$user) {
            return back()->withErrors([
                'otp' => 'OTP tidak valid atau telah kedaluwarsa.'
            ]);
        }

        // Reset OTP setelah digunakan
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        // Langsung alihkan ke halaman reset password dengan menyimpan email di session
        return redirect()->route('password.reset')
            ->with('email', $user->email)
            ->with('verified', true);
    }

    // Menampilkan halaman reset password
    public function showResetPasswordForm()
    {
        // Verifikasi bahwa user sudah melewati proses OTP
        if (!session('email') || !session('verified')) {
            return redirect()->route('password.request');
        }
        
        return view('auth.reset-password', ['email' => session('email')]);
    }

    // Proses reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        // Logging untuk debugging
        \Log::info('Session data in resetPassword:', [
            'verified' => session('verified'),
            'email' => session('email'),
            'request_email' => $request->email
        ]);

        // Verifikasi session data exists
        if (!session('verified') || session('email') != $request->email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Silakan mulai proses reset password dari awal.']);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'User tidak ditemukan.']);
        }

        // Update password with error handling
        try {
            // Pastikan password baru yang disimpan berbeda dengan yang lama
            $user->forceFill([
                'password' => Hash::make($request->password)
            ]);
            
            $success = $user->save();
            
            \Log::info('Password reset operation:', [
                'success' => $success,
                'user_id' => $user->id,
                'password_changed' => true
            ]);
            
            if (!$success) {
                throw new \Exception('Failed to save user password');
            }
        } catch (\Exception $e) {
            \Log::error('Error saving new password:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('password.reset')
                ->withErrors(['password' => 'Gagal menyimpan password baru. Silakan coba lagi.']);
        }

        // Clear session data
        session()->forget(['email', 'verified']);

        return redirect()->route('login')
            ->with('status', 'password-reset-success')
            ->with('message', 'Password Anda berhasil direset. Silakan login dengan password baru.');
    }
    
    // Function untuk mengirim email OTP
    private function sendOTPEmail($email, $otp)
    {
        $data = [
            'otp' => $otp,
            'appName' => 'PANDU'
        ];
        
        Mail::send('emails.otp', $data, function($message) use ($email) {
            $message->to($email)
                    ->subject('Kode OTP Reset Password PANDU');
            $message->from('disdikpandu@gmail.com', 'PANDU - Dinas Pendidikan');
        });
    }
    
    // Function untuk masking email
    private function maskEmail($email)
    {
        $arr = explode('@', $email);
        $name = $arr[0];
        $domain = $arr[1];
        
        if (strlen($name) <= 3) {
            $maskedName = substr($name, 0, 1) . '***';
        } else {
            $maskedName = substr($name, 0, 3) . '***';
        }
        
        return $maskedName . '@' . $domain;
    }
}