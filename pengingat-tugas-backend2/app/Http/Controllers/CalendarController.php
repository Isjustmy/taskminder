<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\CalendarResource;
use App\Models\Calendar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    public function index()
    {
        try {
            $currentUser = Auth::user();

            if (!$currentUser || !$currentUser->hasRole('siswa')) {
                return new CalendarResource(false, 'Anda tidak memiliki izin untuk mengakses kalender', null);
            }

            $calendars = Calendar::where('student_id', $currentUser->id)->get();

            if ($calendars->isEmpty()) {
                return new CalendarResource(false, 'Tidak ada data kalender', null);
            }

            return new CalendarResource(true, 'Data kalender berhasil diambil', $calendars);
        } catch (\Exception $e) {
            return new CalendarResource(false, 'Terjadi kesalahan saat mengambil data kalender', null);
        }
    }

    public function show($id)
    {
        try {
            $currentUser = Auth::user();
            $calendar = Calendar::findOrFail($id);

            if (!$currentUser || !$currentUser->hasRole('siswa') || !$this->isCurrentUser($currentUser, $calendar->student_id)) {
                return new CalendarResource(false, 'Anda tidak memiliki izin untuk mengakses kalender ini', null);
            }

            return new CalendarResource(true, 'Detail kalender berhasil diambil', $calendar);
        } catch (\Exception $e) {
            return new CalendarResource(false, 'Terjadi kesalahan saat mengambil detail kalender', null);
        }
    }

    public function store(Request $request)
    {
        try {
            $currentUser = Auth::user();

            if (!$currentUser || !$currentUser->hasRole('siswa')) {
                return new CalendarResource(false, 'Anda tidak memiliki izin untuk membuat kalender', null);
            }

            $validator = Validator::make($request->all(), [
                'date_marker' => 'required|date',
                'title_marker' => 'required',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $calendar = new Calendar([
                'date_marker' => $request->input('date_marker'),
                'title_marker' => $request->input('title_marker'),
                'description' => $request->input('description'),
                'student_id' => $currentUser->id,
            ]);

            $calendar->save();

            return new CalendarResource(true, 'Kalender berhasil dibuat', $calendar);
        } catch (\Exception $e) {
            return new CalendarResource(false, 'Terjadi kesalahan saat membuat kalender', null);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $currentUser = Auth::user();
            $calendar = Calendar::findOrFail($id);

            if (!$currentUser || !$currentUser->hasRole('siswa') || !$this->isCurrentUser($currentUser, $calendar->student_id)) {
                return new CalendarResource(false, 'Anda tidak memiliki izin untuk mengupdate kalender', null);
            }

            $validator = Validator::make($request->all(), [
                'date_marker' => 'required|date',
                'title_marker' => 'required',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $calendar->date_marker = $request->input('date_marker');
            $calendar->description = $request->input('title_marker');
            $calendar->description = $request->input('description');
            $calendar->save();

            return new CalendarResource(true, 'Kalender berhasil diupdate', $calendar);
        } catch (\Exception $e) {
            return new CalendarResource(false, 'Terjadi kesalahan saat mengupdate kalender', null);
        }
    }

    public function destroy($id)
    {
        try {
            $currentUser = Auth::user();
            $calendar = Calendar::findOrFail($id);

            if (!$currentUser || !$currentUser->hasRole('siswa') || !$this->isCurrentUser($currentUser, $calendar->student_id)) {
                return new CalendarResource(false, 'Anda tidak memiliki izin untuk menghapus kalender', null);
            }

            $calendar->delete();

            return new CalendarResource(true, 'Kalender berhasil dihapus', null);
        } catch (\Exception $e) {
            return new CalendarResource(false, 'Terjadi kesalahan saat menghapus kalender', null);
        }
    }

    private function isCurrentUser($currentUser, $studentId)
    {
        return $currentUser->id == $studentId;
    }
}

