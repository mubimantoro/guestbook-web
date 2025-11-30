<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller implements HasMiddleware
{

    public static function middleware()
    {
        return [
            new Middleware(['permission:users'], only: ['index', 'store', 'show', 'update', 'destroy', 'getPicUsers'])
        ];
    }

    public function index()
    {
        $users = User::when(request()->search, function ($users) {
            $users = $users->where('name', 'like', '%' . request()->search . '%');
        })->with('roles')->latest()->paginate(5);

        $users->appends(['search' => request()->search]);

        return new UserResource(true, 'Data Users', $users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap'  => 'required',
            'nomor_hp' => 'required|string|max:15',
            'username'  => 'required|unique:users|alpha_dash|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ], [
            'nama_lengkap.required' => 'Nama Lengkap wajib diisi',
            'nomor_hp.required' => 'Nomor HP wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, dash dan underscore',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nomor_hp' => $request->nomor_hp,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user->assignRole($request->roles);

        if ($user) {
            return new UserResource(true, 'Data User berhasil disimpan', $user);
        }


        return new UserResource(false, 'Data User gagal disimpan', null);
    }


    public function show($id)
    {
        $user = User::with('roles')->whereId($id)->first();

        if ($user) {
            return new UserResource(true, 'Detail Data User', $user);
        }

        return new UserResource(false, 'Detail Data User tidak ditemukan', null);
    }


    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'nomor_hp' => 'nullable|string|max:15',
            'username' => 'nullable|alpha_dash|max:50|unique:users,username,' . $user->id,
            'email' => 'nullable|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->password == "") {
            $user->update([
                'nama_lengkap' => $request->nama_lengkap,
                'nomor_hp' => $request->nomor_hp,
                'email' => $request->email,
                'username' => $request->username,
            ]);
        } else {
            $user->update([
                'nama_lengkap' => $request->nama_lengkap,
                'nomor_hp' => $request->nomor_hp,
                'email' => $request->email,
                'username' => $request->username,
                'password'  => bcrypt($request->password)
            ]);
        }

        $user->syncRoles($request->roles);

        if ($user) {
            return new UserResource(true, 'Data User berhasil diupdate', $user);
        }

        return new UserResource(false, 'Data User gagal diupdate', null);
    }


    public function destroy(User $user)
    {
        if ($user->delete()) {
            return new UserResource(true, 'Data User berhasil dihapus', null);
        }

        return new UserResource(false, 'Data User gagal dihapus', null);
    }

    public function getPicUsers()
    {
        $users = User::role('pic')->get();
        return new UserResource(true, 'Data Users PIC', $users);
    }
}
