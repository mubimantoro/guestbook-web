<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware(['permission:roles'], only: ['index', 'store', 'show', 'update', 'destroy', 'all'])
        ];
    }

    public function index()
    {
        $roles = Role::when(request()->search, function ($roles) {
            $roles = $roles->where('name', 'like', '%' . request()->search . '%');
        })->with('permissions')->latest()->paginate(5);

        $roles->appends(['search' => request()->search]);

        return new RoleResource(true, 'Data Roles', $roles);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'permissions' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::create(['name' => $request->name]);
        $role->givePermissionTo($request->permissions);

        if ($role) {
            return new RoleResource(true, 'Data Role berhasil disimpan', $role);
        }

        return new RoleResource(false, 'Data Role gagal disimpan', null);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        if ($role) {
            return new RoleResource(true, 'Detail Data Role', $role);
        }

        return new RoleResource(false, 'Detail Data Role tidak ditemukan', null);
    }

    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'permissions' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        if ($role) {
            return new RoleResource(true, 'Data Role berhasil diupdate', $role);
        }

        return new RoleResource(false, 'Data Role gagal diupdate', null);
    }


    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->delete()) {
            return new RoleResource(true, 'Data Role berhasil dihapus', null);
        }

        return new RoleResource(false, 'Data Role gagal dihapus', null);
    }


    public function all()
    {
        $roles = Role::latest()->get();
        return new RoleResource(true, 'Data Roles', $roles);
    }
}
