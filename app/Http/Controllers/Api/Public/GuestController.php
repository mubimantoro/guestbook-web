<?php

namespace App\Http\Controllers\Api\Public;

use App\GuestStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Guest;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuestController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string',
                'nomor_hp' => 'required|string|max:15',
                'institusi' => 'required|string',
                'tujuan' => 'required|string',
                'catatan' => 'nullable|string',
                'tanggal_kunjungan' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $guest = Guest::create([
                'nama' => $request->nama,
                'nomor_hp' => $request->nomor_hp,
                'institusi' => $request->institusi,
                'tujuan' => $request->tujuan,
                'catatan' => $request->catatan,
                'tanggal_kunjungan' => $request->tanggal_kunjungan,
                'status' => $request->status ?? GuestStatus::Pending->value
            ]);

            $adminPhone = config('services.fonnte.admin_phone');
            $adminMessage = $this->whatsappService->notifyAdminNewGuest($guest->toArray());
            $guestMessage = $this->whatsappService->sendGuestConfirmation($guest->toArray());

            SendWhatsAppNotification::dispatch($adminPhone, $adminMessage);
            SendWhatsAppNotification::dispatch($guest->nomor_hp, $guestMessage);

            Log::info('Guest created, WhatsApp notifications queued', [
                'guest_id' => $guest->id
            ]);

            return new GuestResource(true, 'Data Tamu berhasil ditambahkan', $guest);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data Tamu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
