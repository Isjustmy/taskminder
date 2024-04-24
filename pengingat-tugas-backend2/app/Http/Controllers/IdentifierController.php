<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TeacherIdentifierImport;
use App\Models\StudentIdentifier;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class IdentifierController extends Controller
{
    public function findNisnRow($filePath)
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestDataRow(); // Mendapatkan nomor baris tertinggi dengan data
        $highestColumn = $worksheet->getHighestDataColumn(); // Mendapatkan kolom terkanan dengan data

        // Menghasilkan daftar kolom dari A hingga kolom terkanan
        $columns = range('A', $highestColumn);

        foreach ($columns as $column) {
            // Iterasi mulai dari baris 1 sampai dengan baris tertinggi
            for ($rowIndex = 1; $rowIndex <= $highestRow; $rowIndex++) {
                // Membaca nilai di sel
                $cellValue = $worksheet->getCell($column . $rowIndex)->getValue();

                // Jika nilai sel adalah "NISN", simpan nilai NISN dan kembalikan nomor kolom
                if ($cellValue === 'NISN') {
                    return $column;
                }
            }
        }

        return null; // Jika tidak ditemukan kata kunci "NISN"
    }

    /**
     * Import data NISN dari file Excel.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importNISN(Request $request)
    {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }

        try {
            // Baca file Excel
            $filePath = $request->file('file')->getPathname();
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Cari kolom NISN
            $nisnColumn = $this->findNisnRow($filePath);

            if (!$nisnColumn) {
                // Jika kolom NISN tidak ditemukan
                return response()->json(['error' => "NISN tidak ditemukan pada file excel. Pastikan tabel memiliki header bertuliskan 'NISN'"]);
            }

            // Array untuk menyimpan NISN yang telah ditemukan
            $seenNISNs = [];

            // Array untuk menyimpan NISN yang terdeteksi duplikasi
            $duplicateNISNs = [];

            // Impor data NISN dari kolom yang ditemukan
            $highestRow = $worksheet->getHighestDataRow();
            for ($row = 2; $row <= $highestRow; $row++) {
                $nisnCellValue = $worksheet->getCell($nisnColumn . $row)->getValue();

                // Periksa apakah nilai NISN adalah angka
                if (is_numeric($nisnCellValue)) {
                    // Periksa apakah NISN sudah ada dalam database sebelumnya atau sudah ditemukan sebelumnya
                    if (!in_array($nisnCellValue, $seenNISNs)) {
                        // Periksa apakah NISN sudah ada di database
                        $existingNisn = StudentIdentifier::where('nisn', $nisnCellValue)->exists();
                        if (!$existingNisn) {
                            // Jika NISN belum ada, masukkan ke dalam database
                            StudentIdentifier::create([
                                'nisn' => $nisnCellValue,
                            ]);
                        } else {
                            // Jika NISN sudah ada di database, tambahkan ke daftar NISN yang terduplikasi
                            $duplicateNISNs[] = $nisnCellValue;
                        }
                        // Tambahkan NISN ke dalam array sementara
                        $seenNISNs[] = $nisnCellValue;
                    }
                }
                // Jika nilai NISN bukan angka, maka dilewati (tidak dimasukkan ke dalam database)
            }

            if (!empty($duplicateNISNs)) {
                return response()->json(['success' => 'Import berhasil, namun terdapat duplikasi data NISN atau terdapat data NISN yang sudah ada pada database. Data NISN yang sudah ada pada database tidak akan dimasukan, data NISN lainnya akan tetap dimasukan.']);
            } else {
                return response()->json(['success' => 'Data NISN berhasil di-import!']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi Kesalahan dalam mengimpor data NISN']);
        }
    }


    public function findNipRow($filePath)
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestDataRow(); // Mendapatkan nomor baris tertinggi dengan data
        $highestColumn = $worksheet->getHighestDataColumn(); // Mendapatkan kolom terkanan dengan data

        // Menghasilkan daftar kolom dari A hingga kolom terkanan
        $columns = range('A', $highestColumn);

        foreach ($columns as $column) {
            // Iterasi mulai dari baris 1 sampai dengan baris tertinggi
            for ($rowIndex = 1; $rowIndex <= $highestRow; $rowIndex++) {
                // Membaca nilai di sel
                $cellValue = $worksheet->getCell($column . $rowIndex)->getValue();

                // Jika nilai sel adalah "NISN", simpan nilai NISN dan kembalikan nomor kolom
                if ($cellValue === 'NIP') {
                    return $column;
                }
            }
        }

        return null; // Jika tidak ditemukan kata kunci "NISN"
    }

    /**
     * Import data NIP dari file Excel.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importNIP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }

        try {
            Excel::import(new TeacherIdentifierImport, $request->file('file'));

            return response()->json(['success' => 'Data NIP berhasil di-import!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan dalam mengimpor data NIP: ' . $e->getMessage()]);
        }
    }

    


}
