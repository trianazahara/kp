<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Template;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;

class SettingsController extends Controller
{
    /**
     * Menampilkan halaman pengaturan user
     */
    public function index()
    {
        $user = Auth::user();
        $templates = Template::where('id_users', $user->id_users)
                             ->where('active', 1)
                             ->get();
        return view('settings.index', compact('user', 'templates'));
    }

    /**
     * Ambil data profil user
     */
    public function getProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Profil tidak ditemukan.'], 404);
        }

        if ($user->profile_picture) {
            $user->profile_picture_url = asset('storage/' . $user->profile_picture);
        }

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

    // Jika tidak ada field yang diisi, kembalikan tanpa error
    if (!$request->filled('username') && !$request->filled('email') && !$request->filled('nama')) {
        return redirect()->route('settings.index')
            ->with('info', 'Tidak ada perubahan yang dilakukan.');
    }

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

        if (!$request->username && !$request->email && !$request->nama) {
            return response()->json([
                'message' => 'Setidaknya satu kolom harus diisi untuk diperbarui.'
            ], 400);
        }

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
     * Upload foto profil (untuk web)
     */
    public function uploadPhoto(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,jpg,png|max:5120' // max 5MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');

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
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');

            $user->update([
                'profile_picture' => $path
            ]);

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

        $validator = Validator::make($request->all(), [
            'oldPassword' => 'required|string',
            'newPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!Hash::check($request->oldPassword, $user->password)) {
            return redirect()->back()
                ->with('error', 'Password lama salah.');
        }

        $user->update([
            'password' => Hash::make($request->newPassword)
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

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah.'
            ], 400);
        }

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

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

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

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->update([
            'profile_picture' => null
        ]);

        return response()->json([
            'message' => 'Foto profil berhasil dihapus',
            'profile_picture' => null
        ]);
    }    
    public function uploadTemplate(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:doc,docx|max:51200',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Nonaktifkan template sebelumnya
            Template::where('id_users', $user->id_users)
                    ->update(['active' => 0]);

            $file = $request->file('file');
            $uuid = Str::uuid();
            $originalName = $file->getClientOriginalName();
            $storedName = $uuid . '---' . $originalName;
            $filePath = 'templates/' . $storedName;

            // Simpan file
            $path = $file->storeAs('templates', $storedName, 'public');

            // Simpan ke database
            Template::create([
                'id_dokumen' => $uuid,
                'id_users' => $user->id_users,
                'file_path' => $filePath,
                'size' => $file->getSize(),
                'active' => 1,
                'created_by' => $user->id_users
            ]);

            return redirect()->back()
                ->with('success', 'Template berhasil diupload!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal upload template: ' . $e->getMessage());
        }
    }

    public function deleteTemplate($id)
    {
        try {
            $user = Auth::user();
            $template = Template::where('id_dokumen', $id)
                              ->where('id_users', $user->id_users)
                              ->firstOrFail();

            // Hapus file fisik
            if (Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }

            // Hapus dari database
            $template->delete();

            return redirect()->back()
                ->with('success', 'Template berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }

// Controller
public function previewTemplate($id)
{
    try {
        $user = Auth::user();
        $template = Template::where('id_dokumen', $id)
                          ->where('id_users', $user->id_users)
                          ->firstOrFail();

        $filePath = storage_path('app/public/' . $template->file_path);

        // Validasi file
        if (!Storage::disk('public')->exists($template->file_path)) {
            throw new \Exception("File tidak ditemukan");
        }

        // Konversi DOCX ke PDF
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
        
        // Simpan sementara sebagai HTML
        $tempHtml = tempnam(sys_get_temp_dir(), 'tpl') . '.html';
        $phpWord->save($tempHtml, 'HTML');

        // Konversi HTML ke PDF
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
        
        $dompdf->loadHtml(file_get_contents($tempHtml));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Hapus file temporary
        unlink($tempHtml);

        // Tampilkan PDF di browser
        return response()->make(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview.pdf"'
            ]
        );

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Gagal menampilkan preview: ' . $e->getMessage());
    }
}
}