<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index()
    {
        $guests = Guest::when(request()->search, function ($guests) {
            $guests = $guests->where('nama', 'like', '%' . request()->search . '%');
        })->latest()->paginate(5);


        return new GuestResource(true, 'List data Tamu', $guests);
    }
}
