<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Menampilkan notifikasi untuk pengguna yang saat ini diautentikasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Mendapatkan notifikasi untuk pengguna yang saat ini diautentikasi
        $notifications = $request->user()->notifications;

        return response()->json($notifications);
    }

    /**
     * Menandai notifikasi sebagai sudah dibaca.
     *
     * @param  \Illuminate\Notifications\DatabaseNotification  $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($notification)
    {
        $notification->markAsRead();

        return response()->json(['message' => 'Notifikasi berhasil ditandai sebagai sudah dibaca']);
    }
}
