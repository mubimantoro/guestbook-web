<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:permissions'], only: ['index', 'all'])
        ];
    }

    public function index()
    {
        $permissions = Permission::latest()->paginate(5);

        return new PermissionResource(true, 'Data Permissions', $permissions);
    }

    public function all()
    {
        $permissions = Permission::latest()->get();
        return new PermissionResource(true, 'Data Permissions', $permissions);
    }
}
