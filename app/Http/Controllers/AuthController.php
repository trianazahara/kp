<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

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

        // Cek apakah username ada di database
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Username tidak ditemukan.',
            ]);
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Update last_login
            $user = Auth::user();
            $user->last_login = Carbon::now();
            $user->save();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'password' => 'Password yang Anda masukkan salah.',
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
        
        try {
            // Simpan OTP ke database
            $user->save();
            
            // Coba kirim email OTP
            try {
                $this->sendOTPEmail($user->email, $otp);
                Log::info("OTP email sent to {$user->email} with code {$otp}");
            } catch (Exception $e) {
                Log::error("Failed to send OTP email: " . $e->getMessage());
                // Tetap lanjutkan meskipun email gagal (untuk keperluan testing)
            }
            
            // Masking email untuk ditampilkan ke pengguna
            $maskedEmail = $this->maskEmail($user->email);
            
            // Simpan data di session dan redirect ke halaman OTP
            session([
                'status' => 'otp_sent',
                'email' => $user->email,
                'username' => $user->username,
                'maskedEmail' => $maskedEmail,
                'otp_for_testing' => config('app.debug') ? $otp : null // OTP hanya untuk testing di mode debug
            ]);
            
            return redirect()->route('password.otp');
        } catch (Exception $e) {
            Log::error("Error in sendOTP: " . $e->getMessage());
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat mengirim OTP. Silakan coba lagi.'
            ]);
        }
    }

    // Menampilkan form input OTP
    public function showOTPForm()
    {
        // Debug session data
        Log::info('Session data in showOTPForm:', [
            'email' => session('email'),
            'status' => session('status'),
            'otp_for_testing' => session('otp_for_testing')
        ]);
        
        if (!session('email') || !session('status')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi telah berakhir. Silakan coba lagi.']);
        }
        
        return view('auth.verify-otp', [
            'email' => session('email'),
            'maskedEmail' => session('maskedEmail'),
            'otp_for_testing' => session('otp_for_testing')
        ]);
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
            Log::warning('Invalid OTP attempt', [
                'email' => $email,
                'entered_otp' => $request->otp,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'otp' => 'OTP tidak valid atau telah kedaluwarsa.'
            ]);
        }

        Log::info('OTP verified successfully', ['user_id' => $user->id_users]);

        // Reset OTP setelah digunakan
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        // Simpan token verifikasi di session
        $token = hash('sha256', time() . $user->email);
        
        // Simpan token di session
        session([
            'email' => $user->email,
            'reset_token' => $token,
            'token_expires_at' => Carbon::now()->addMinutes(30)->timestamp,
            'verified' => true
        ]);
        
        Log::info('Reset password token generated', [
            'email' => $user->email, 
            'token' => substr($token, 0, 10) . '...'
        ]);

        // Alihkan ke halaman reset password
        return redirect()->route('password.reset');
    }

    // Menampilkan halaman reset password
    public function showResetPasswordForm()
    {
        // Verifikasi bahwa user sudah melewati proses OTP
        if (!session('email') || !session('verified') || 
            !session('reset_token') || !session('token_expires_at')) {
            Log::warning('Unauthorized reset password attempt without proper session');
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Silakan mulai proses reset password dari awal.']);
        }
        
        // Periksa apakah token masih berlaku
        if (session('token_expires_at') < time()) {
            session()->forget(['email', 'verified', 'reset_token', 'token_expires_at']);
            Log::warning('Reset password token expired');
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Sesi reset password telah kedaluwarsa. Silakan coba lagi.']);
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
        Log::info('Reset password attempt:', [
            'verified' => session('verified'),
            'email' => session('email'),
            'request_email' => $request->email,
            'has_token' => session()->has('reset_token')
        ]);

        // Verifikasi session data exists
        if (!session('verified') || session('email') != $request->email || 
            !session('reset_token') || session('token_expires_at') < time()) {
            
            session()->forget(['email', 'verified', 'reset_token', 'token_expires_at']);
            Log::warning('Invalid reset password attempt', ['ip' => $request->ip()]);
            
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Silakan mulai proses reset password dari awal.']);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            Log::error('User not found during password reset', ['email' => $request->email]);
            return redirect()->route('password.request')
                ->withErrors(['email' => 'User tidak ditemukan.']);
        }

        // Update password with error handling
        try {
            // Pastikan password baru yang disimpan berbeda dengan yang lama
            $user->password = Hash::make($request->password);
            
            $success = $user->save();
            
            Log::info('Password reset operation:', [
                'success' => $success,
                'user_id' => $user->id_users,
                'password_changed' => true
            ]);
            
            if (!$success) {
                throw new Exception('Failed to save user password');
            }
        } catch (Exception $e) {
            Log::error('Error saving new password:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('password.reset')
                ->withErrors(['password' => 'Gagal menyimpan password baru. Silakan coba lagi.']);
        }

        // Clear session data
        session()->forget(['email', 'verified', 'reset_token', 'token_expires_at', 'otp_for_testing']);

        return redirect()->route('login')
            ->with('status', 'password-reset-success')
            ->with('message', 'Password Anda berhasil direset. Silakan login dengan password baru.');
    }
    
    // Function untuk mengirim email OTP
    private function sendOTPEmail($email, $otp)
    {
        $data = [
            'otp' => $otp,
            'appName' => config('app.name', 'PANDU')
        ];
        
        try {
            Mail::send('emails.otp', $data, function($message) use ($email) {
                $message->to($email)
                        ->subject('Kode OTP Reset Password PANDU');
                $message->from(config('mail.from.address', 'disdikpandu@gmail.com'), 
                               config('mail.from.name', 'PANDU - Dinas Pendidikan'));
            });
            
            // Cek jika ada kegagalan saat mengirim email
            if (Mail::failures()) {
                Log::error('Mail send failures', ['failures' => Mail::failures()]);
                throw new Exception('Failed to send mail: ' . implode(', ', Mail::failures()));
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Error sending OTP email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            // Re-throw the exception for the caller to handle
            throw $e;
        }
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