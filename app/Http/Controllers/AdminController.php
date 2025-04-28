<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bidang;
use App\Models\PesertaMagang;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display a listing of the admins.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admins = User::where('role', 'admin')
        ->leftJoin('bidang', 'users.id_bidang', '=', 'bidang.id_bidang')
        ->select('users.*', 'bidang.nama_bidang')
        ->orderBy('users.created_at', 'desc')
        ->get();
    
    $bidangList = \App\Models\Bidang::all();

    return view('admin.index', compact('admins', 'bidangList'));
    }

    /**
     * Show the form for creating a new admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bidang = Bidang::all();
        return view('admin.create', compact('bidang'));
    }

    /**
 * Store a newly created admin in storage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
public function store(Request $request)
{
    \Log::info('Admin store method called with data:', $request->all());
    
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|unique:users',
        'password' => 'required|string|min:6',
        'email' => 'required|string|email|unique:users',
        'nama' => 'required|string',
        'nip' => 'nullable|string',
        'id_bidang' => 'required|exists:bidang,id_bidang',
        'role' => 'required|in:admin,superadmin',
    ]);

    if ($validator->fails()) {
        \Log::warning('Validation failed: ', $validator->errors()->toArray());
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    // Create new admin user
    try {
        \Log::info('Creating new admin user');
        User::create([
            'id_users' => Str::uuid(),
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'nama' => $request->nama,
            'nip' => $request->nip,
            'id_bidang' => $request->id_bidang,
            'role' => $request->role,
            'is_active' => true,
            'created_at' => Carbon::now()
        ]);
        \Log::info('Admin created successfully');

        return redirect()->route('admin.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    } catch (\Exception $e) {
        // Log error
        \Log::error('Error adding admin: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        // Return with error message
        return redirect()->back()
            ->with('error', 'Terjadi kesalahan saat menambahkan admin: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Show the form for editing the specified admin.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = User::where('id_users', $id)->where('role', 'admin')->firstOrFail();
        $bidang = Bidang::all();
        
        return view('admin.edit', compact('admin', 'bidang'));
    }

    /**
     * Update the specified admin in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('id_users', $id)->where('role', 'admin')->firstOrFail();

        $rules = [
            'nama' => 'required|string',
            'email' => 'required|string|email|unique:users,email,' . $id . ',id_users',
            'username' => 'required|string|unique:users,username,' . $id . ',id_users',
            'nip' => 'nullable|string',
            'id_bidang' => 'required|exists:bidang,id_bidang',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update admin data
        $adminData = [
            'nama' => $request->nama,
            'email' => $request->email,
            'username' => $request->username,
            'nip' => $request->nip,
            'id_bidang' => $request->id_bidang,
            'updated_at' => Carbon::now()
        ];

        if ($request->filled('password')) {
            $adminData['password'] = Hash::make($request->password);
        }

        $admin->update($adminData);

        return redirect()->route('admin.index')
            ->with('success', 'Profil admin berhasil diperbarui.');
    }

    /**
     * Remove the specified admin from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            // Get the admin to verify it exists
            $admin = User::where('id_users', $id)->where('role', 'admin')->first();
            
            if (!$admin) {
                return redirect()->route('admin.index')
                    ->with('error', 'Admin tidak ditemukan atau tidak dapat dihapus.');
            }
            
            // Reset mentor_id for related interns
            PesertaMagang::where('mentor_id', $id)->update(['mentor_id' => null]);
            
            // Delete admin's notifications
            Notification::where('user_id', $id)->delete();
            
            // Reset created_by for related interns
            PesertaMagang::where('created_by', $id)->update(['created_by' => null]);
            
            // Delete the admin user
            $admin->delete();
            
            DB::commit();
            
            return redirect()->route('admin.index')
                ->with('success', 'Admin berhasil dihapus. Semua peserta magang yang dibimbing oleh admin ini akan diset tanpa mentor.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('admin.index')
                ->with('error', 'Terjadi kesalahan saat menghapus admin: ' . $e->getMessage());
        }
    }

    /**
     * Return JSON response for API requests
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminApi()
    {
        $admins = User::where('role', 'admin')
            ->leftJoin('bidang', 'users.id_bidang', '=', 'bidang.id_bidang')
            ->select(
                'users.id_users', 
                'users.username', 
                'users.email', 
                'users.nama', 
                'users.nip',
                'users.id_bidang', 
                'bidang.nama_bidang', 
                'users.role', 
                'users.last_login',
                'users.is_active'
            )
            ->orderBy('users.created_at', 'desc')
            ->get();

        return response()->json($admins);
    }

    /**
     * API endpoint for creating a new admin
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAdminApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'email' => 'required|string|email|unique:users',
            'nama' => 'required|string',
            'nip' => 'nullable|string',
            'id_bidang' => 'required|exists:bidang,id_bidang',
            'role' => 'required|in:admin,superadmin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $id_users = Str::uuid();

        // Create new admin user
        User::create([
            'id_users' => $id_users,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'nama' => $request->nama,
            'nip' => $request->nip,
            'id_bidang' => $request->id_bidang,
            'role' => $request->role,
            'is_active' => true,
            'created_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Admin berhasil ditambahkan.',
            'data' => [
                'id_users' => $id_users,
                'username' => $request->username,
                'email' => $request->email,
                'nama' => $request->nama,
                'nip' => $request->nip,
                'id_bidang' => $request->id_bidang,
                'role' => $request->role
            ]
        ], 201);
    }

    /**
     * API endpoint for updating an admin
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function editAdminApi(Request $request, $id)
    {
        $admin = User::where('id_users', $id)->where('role', 'admin')->first();
        
        if (!$admin) {
            return response()->json([
                'message' => 'Admin tidak ditemukan atau tidak dapat diupdate.'
            ], 404);
        }

        // Check if at least one field is provided
        if (!$request->has('username') && !$request->has('email') && !$request->has('nama') && 
            !$request->has('password') && !$request->has('nip') && !$request->has('id_bidang')) {
            return response()->json([
                'message' => 'Setidaknya satu kolom harus diisi untuk diperbarui.'
            ], 400);
        }

        $updates = [];
        
        if ($request->has('username')) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:users,username,' . $id . ',id_users',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Username sudah digunakan.'
                ], 400);
            }
            
            $updates['username'] = $request->username;
        }
        
        if ($request->has('email')) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|unique:users,email,' . $id . ',id_users',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Email sudah digunakan atau format tidak valid.'
                ], 400);
            }
            
            $updates['email'] = $request->email;
        }
        
        if ($request->has('nama')) {
            $updates['nama'] = $request->nama;
        }
        
        if ($request->has('nip')) {
            $updates['nip'] = $request->nip;
        }
        
        if ($request->has('id_bidang')) {
            $bidangExists = Bidang::where('id_bidang', $request->id_bidang)->exists();
            
            if (!$bidangExists) {
                return response()->json([
                    'message' => 'Bidang tidak ditemukan.'
                ], 400);
            }
            
            $updates['id_bidang'] = $request->id_bidang;
        }
        
        if ($request->has('password')) {
            $updates['password'] = Hash::make($request->password);
        }
        
        $admin->update($updates);
        
        return response()->json([
            'message' => 'Profil admin berhasil diperbarui.'
        ]);
    }

    /**
     * API endpoint for deleting an admin
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAdminApi($id)
    {
        DB::beginTransaction();
        
        try {
            // Get the admin to verify it exists
            $admin = User::where('id_users', $id)->where('role', 'admin')->first();
            
            if (!$admin) {
                return response()->json([
                    'message' => 'Admin tidak ditemukan atau tidak dapat dihapus.'
                ], 404);
            }
            
            // Reset mentor_id for related interns
            PesertaMagang::where('mentor_id', $id)->update(['mentor_id' => null]);
            
            // Delete admin's notifications
            Notification::where('user_id', $id)->delete();
            
            // Reset created_by for related interns
            PesertaMagang::where('created_by', $id)->update(['created_by' => null]);
            
            // Delete the admin user
            $admin->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Admin berhasil dihapus. Semua peserta magang yang dibimbing oleh admin ini akan diset tanpa mentor.'
            ]);
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'detail' => $e->getMessage()
            ], 500);
        }
    }
}