<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isMahasiswa()) {
            abort(403, 'Akses ditolak. Pengumpulan tugas hanya dapat dilakukan oleh Mahasiswa.');
        }

        $user = Auth::user();

        $enrollment = $assignment->course->students()->where('users.id', $user->id)->first();
        if (!$enrollment || $enrollment->pivot->status !== 'approved') {
            abort(403, 'Akses ditolak. Anda belum disetujui oleh Dosen untuk mengikuti mata kuliah tugas ini.');
        }

        if ($assignment->isOverdue()) {
            return back()->with('error', 'Gagal mengumpulkan: Batas waktu pengumpulan (deadline) tugas telah berakhir.');
        }

        $validated = $request->validate([
            'jawaban' => ['required', 'string', 'min:10', 'max:20000'],
        ], [
            'jawaban.required' => 'Teks jawaban tugas wajib diisi.',
            'jawaban.min' => 'Jawaban terlalu pendek. Tuliskan jawaban minimal 10 karakter.',
            'jawaban.max' => 'Jawaban melebihi batas maksimal 20.000 karakter.',
        ]);

        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ],
            [
                'jawaban' => $validated['jawaban'],
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Jawaban tugas Anda berhasil disimpan ke sistem.');
    }

    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        // TODO 5a (grade): Cek apakah dosen adalah pengampu mata kuliah, validasi nilai (0-100) & catatan_dosen, lalu update submission.
        // 1. Pastikan $submission->assignment->course->dosen_id === Auth::id(), jika tidak abort(403).
        // 2. Validasi input 'nilai' (required|integer|min:0|max:100) dan 'catatan_dosen' (nullable|string|max:1000).
        // 3. Simpan perubahan menggunakan $submission->update(...) dan redirect back() dengan pesan sukses.
        // Pastikan dosen merupakan pengampu mata kuliah
    if ($submission->assignment->course->dosen_id !== Auth::id()) {
        abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
    }

    // Validasi nilai dan catatan dosen
    $validated = $request->validate([
        'nilai' => ['required', 'integer', 'min:0', 'max:100'],
        'catatan_dosen' => ['nullable', 'string', 'max:1000'],
    ]);

    // Simpan nilai dan catatan
    $submission->update($validated);

    return back()->with('success', 'Nilai tugas mahasiswa berhasil disimpan.');
    }

    public function exportCsv(Assignment $assignment): StreamedResponse
    {
        // TODO 5b (exportCsv): Cek kepemilikan dosen, lalu gunakan response()->streamDownload() dan fputcsv() untuk mengunduh data nilai mahasiswa.
        // 1. Pastikan $assignment->course->dosen_id === Auth::id(), jika tidak abort(403).
        // 2. Muat relasi course.approvedStudents dan submissions dari $assignment.
        // 3. Kembalikan response()->streamDownload(...) yang menulis header & baris CSV menggunakan fopen('php://output', 'w') dan fputcsv().
        // Pastikan dosen merupakan pengampu mata kuliah
    if ($assignment->course->dosen_id !== Auth::id()) {
        abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
    }

    // Muat relasi mahasiswa yang disetujui dan submission
    $assignment->load('course.approvedStudents', 'submissions.user');

    // Unduh data dalam format CSV
    return response()->streamDownload(function () use ($assignment) {
        $handle = fopen('php://output', 'w');

        // BOM agar karakter UTF-8 terbaca dengan baik di Excel
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header CSV
        fputcsv($handle, [
            'NIM',
            'Nama Mahasiswa',
            'Jawaban',
            'Tanggal Pengumpulan',
            'Nilai',
            'Catatan Dosen',
            'Status Nilai',
        ]);

        // Ambil data mahasiswa yang sudah disetujui
        foreach ($assignment->course->approvedStudents as $student) {
            $submission = $assignment->submissions
                ->firstWhere('user_id', $student->id);

            fputcsv($handle, [
                $student->nomor_induk ?? '-',
                $student->name,
                $submission->jawaban ?? '-',
                $submission?->submitted_at?->format('Y-m-d H:i:s') ?? '-',
                $submission->nilai ?? '',
                $submission->catatan_dosen ?? '',
                $submission?->status_nilai ?? 'Belum Mengumpulkan',
            ]);
        }

        fclose($handle);
    }, 'rekap_nilai_' . $assignment->id . '.csv', [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
        
    }
}
