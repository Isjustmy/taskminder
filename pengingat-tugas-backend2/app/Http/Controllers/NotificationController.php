<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        // Transformasi data notifikasi sesuai kebutuhan
        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

        // Return response JSON standar
        return response()->json([
            'success' => true,
            'message' => 'List of notifications',
            'data' => $formattedNotifications,
        ]);
    }


    /**
     * Mengambil data lengkap notifikasi berdasarkan ID yang diberikan dalam request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $notificationId = $request->input('notification_id');

        // Mencari notifikasi berdasarkan ID
        $notification = Auth::user()->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
        }

        // Return data lengkap notifikasi
        return response()->json([
            'success' => true,
            'notification' => $notification,
        ]);
    }


    /**
     * Menandai notifikasi dengan ID tertentu sebagai sudah dibaca.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $notificationId = $request->input('notification_id');

        // Mengambil notifikasi berdasarkan ID
        $notification = Auth::user()->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
        }

        // Menandai notifikasi sebagai sudah dibaca
        $notification->markAsRead();

        return response()->json(['message' => 'Notifikasi berhasil ditandai sebagai sudah dibaca']);
    }


    /**
     * Menghapus notifikasi tertentu berdasarkan ID notifikasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNotificationPerId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $notificationId = $request->input('notification_id');

        // Mengambil notifikasi berdasarkan ID
        $notification = Auth::user()->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
        }

        // Menandai notifikasi sebagai sudah dibaca
        $notification->delete();

        return response()->json(['message' => 'Notifikasi berhasil dihapus!']);
    }
    

     /**
     * Menandai semua notifikasi sebagai sudah dibaca.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        // Menandai semua notifikasi sebagai sudah dibaca
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Semua notifikasi berhasil ditandai sebagai sudah dibaca']);
    }


    /**
     * Menghapus semua notifikasi untuk pengguna yang saat ini diautentikasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAllNotifications(Request $request)
    {
        // Menghapus semua notifikasi untuk pengguna yang saat ini diautentikasi
        $request->user()->notifications()->delete();

        return response()->json(['message' => 'Semua notifikasi berhasil dihapus']);
    }


    /**
     * Kode function cadangan untuk menghapus notifikasi berdasarkan ID notifikasi.
     */
    //  public function deleteNotification($notificationId)
    // {
    //     // Mencari notifikasi berdasarkan ID
    //     $notification = DB::table('notifications')->where('id', $notificationId)->first();

    //     if (!$notification) {
    //         return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
    //     }

    //     // Menghapus notifikasi
    //     DB::table('notifications')->where('id', $notificationId)->delete();

    //     return response()->json(['message' => 'Notifikasi berhasil dihapus']);
    // }
}
