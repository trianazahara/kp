<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Menampilkan halaman pengaturan user
     */
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }
    
    /**
     * Ambil data profil user
     */
    public function getProfile()
    {
        $user = Auth::user();
        
        // Jika tidak ada user terautentikasi
        if (!$user) {
            return response()->json(['message' => 'Profil tidak ditemukan.'], 404);
        }
        
        // Tambahkan URL lengkap untuk foto profil jika ada
        if ($user->profile_picture) {
            $user->profile_picture_url = asset('storage/' . $user->profile_picture);
        }
        
        // Ambil kolom yang dibutuhkan saja
        $userData = [
            'id_users' => $user->id_users,
            'username' => $user->username,
            'email' => $user->email,
            'nama' => $user->nama,
            'profile_picture' => $user->profile_picture,
            'profile_picture_url' => $user->profile_picture_url ?? null,
            'last_login' => $user->last_login,
            'role' => $user->role
        ];
        
        return response()->json($userData);
    }

    /**
     * Update profil user
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|unique:users,username,'.$user->id_users.',id_users',
            'email' => 'nullable|email|unique:users,email,'.$user->id_users.',id_users',
            'nama' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Pastikan minimal ada satu field yang diisi
        if (!$request->username && !$request->email && !$request->nama) {
            return redirect()->back()
                ->with('error', 'Setidaknya satu kolom harus diisi untuk diperbarui.');
        }

        // Update data yang diisi
        $data = [];
        if ($request->filled('username')) {
            $data['username'] = $request->username;
        }
        if ($request->filled('email')) {
            $data['email'] = $request->email;
        }
        if ($request->filled('nama')) {
            $data['nama'] = $request->nama;
        }

        $user->update($data);

        return redirect()->route('settings.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update profil via API
     */
    public function editProfileApi(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|unique:users,username,'.$user->id_users.',id_users',
            'email' => 'nullable|email|unique:users,email,'.$user->id_users.',id_users',
            'nama' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Pastikan minimal ada satu field yang diisi
        if (!$request->username && !$request->email && !$request->nama) {
            return response()->json([
                'message' => 'Setidaknya satu kolom harus diisi untuk diperbarui.'
            ], 400);
        }

        // Update data yang diisi
        $data = [];
        if ($request->filled('username')) {
            $data['username'] = $request->username;
        }
        if ($request->filled('email')) {
            $data['email'] = $request->email;
        }
        if ($request->filled('nama')) {
            $data['nama'] = $request->nama;
        }

        $user->update($data);

        // Ambil data profil terbaru
        $updatedUser = User::find($user->id_users);
        $profilePictureUrl = $updatedUser->profile_picture ? asset('storage/' . $updatedUser->profile_picture) : null;

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'profile' => [
                'id_users' => $updatedUser->id_users,
                'username' => $updatedUser->username,
                'email' => $updatedUser->email,
                'nama' => $updatedUser->nama,
                'profile_picture' => $updatedUser->profile_picture,
                'profile_picture_url' => $profilePictureUrl
            ]
        ]);
    }

    /**
     * Upload foto profil
     */
    public function uploadProfilePicture(Request $request)
    {
        $user = Auth::user();

        // Validasi file
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,jpg,png|max:5120' // max 5MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Hapus foto lama jika ada
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Upload foto baru
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            
            // Update path di database
            $user->update([
                'profile_picture' => $path
            ]);

            return redirect()->route('settings.index')
                ->with('success', 'Foto profil berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload foto profil via API
     */
    public function uploadProfilePictureApi(Request $request)
    {
        $user = Auth::user();

        // Validasi file
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,jpg,png|max:5120' // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'File tidak valid', 
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Hapus foto lama jika ada
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Upload foto baru
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            
            // Update path di database
            $user->update([
                'profile_picture' => $path
            ]);

            // URL lengkap untuk foto profil
            $fullUrl = asset('storage/' . $path);

            return response()->json([
                'message' => 'Foto profil berhasil diupload',
                'profile_picture' => $path,
                'profile_picture_url' => $fullUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ganti password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verifikasi password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Password lama salah.');
        }

        // Update password baru
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Password berhasil diubah.');
    }

    /**
     * Ganti password via API
     */
    public function changePasswordApi(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Verifikasi password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah.'
            ], 400);
        }

        // Update password baru
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah.'
        ]);
    }

    /**
     * Hapus foto profil
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        // Hapus file foto jika ada
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Reset path foto di database
        $user->update([
            'profile_picture' => null
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Foto profil berhasil dihapus.');
    }

    /**
     * Hapus foto profil via API
     */
    public function deletePhotoApi()
    {
        $user = Auth::user();

        // Hapus file foto jika ada
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Reset path foto di database
        $user->update([
            'profile_picture' => null
        ]);

        return response()->json([
            'message' => 'Foto profil berhasil dihapus',
            'profile_picture' => null
        ]);
    }
}