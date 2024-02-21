<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassController extends Controller
{
    /**
     * Menampilkan daftar sumber daya.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $classes = StudentClass::all();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Mengambil Data kelas',
                'data' => $classes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil kelas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menyimpan sumber daya yang baru dibuat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class' => 'required|string|max:13',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan validasi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $class = StudentClass::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil disimpan.',
                'data' => $class,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan kelas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update data kelas berdasarkan id kelas
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'class' => 'required|string|max:13',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan validasi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $studentClass = StudentClass::findOrFail($id);
            $studentClass->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil diperbarui.',
                'data' => $studentClass,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kelas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Menghapus sumber daya yang spesifik dari penyimpanan.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $studentClass = StudentClass::findOrFail($id);
            $studentClass->delete();
            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kelas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengatur ulang data di tabel student_classes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetData()
    {
        try {
            StudentClass::truncate();
            return response()->json([
                'success' => true,
                'message' => 'Data kelas berhasil diatur ulang.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengatur ulang data kelas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
