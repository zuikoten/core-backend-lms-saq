<?php

namespace Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Academic\Actions\AssignStudentToClassGroupAction;
use Modules\Academic\Actions\TransferStudentAction;
use Modules\Academic\Models\ClassGroup;
use Modules\Academic\Models\ClassGroupStudent;
use Modules\Academic\Requests\AssignStudentRequest;
use Modules\Academic\Requests\BulkPlotStudentsRequest;
use Modules\Academic\Requests\TransferStudentRequest;
use Modules\Core\Models\AcademicYear;
use Modules\Student\Models\Student;

/**
 * Halaman "Plotting Siswa" — 1 halaman index yang menampilkan status
 * penempatan SEMUA siswa aktif untuk tahun ajaran yang sedang aktif.
 * Tampilan difilter lewat tab di sisi client (Belum Ditempatkan / per
 * rombel / Semua), tapi tetap 1 halaman — staf butuh lihat siapa saja
 * yang BELUM ditempatkan sekaligus, bukan cuma per rombel satu-satu.
 *
 * Aksi utama sekarang lewat bulk(): pilih banyak siswa, pilih 1 rombel
 * tujuan. store() dan transfer() per siswa tetap dipertahankan.
 */
class ClassGroupStudentController extends Controller
{
    public function index(): View
    {
        $academicYear = AcademicYear::query()->where('is_active', true)->first();

        $currentAssignments = $academicYear
            ? ClassGroupStudent::query()
            ->where('academic_year_id', $academicYear->id)
            ->active()
            ->with('classGroup')
            ->get()
            ->keyBy('student_id')
            : collect();

        $classGroups = $academicYear
            ? ClassGroup::query()->where('academic_year_id', $academicYear->id)->orderBy('name')->get()
            : collect();

        return view('modules.academic.class-group-students.index', [
            'academicYear' => $academicYear,
            'students' => Student::query()->where('status', 'aktif')->orderBy('full_name')->get(),
            'currentAssignments' => $currentAssignments,
            'classGroups' => $classGroups,
        ]);
    }

    public function store(AssignStudentRequest $request, AssignStudentToClassGroupAction $action): RedirectResponse
    {
        $student = Student::query()->findOrFail($request->validated('student_id'));
        $classGroup = ClassGroup::query()->findOrFail($request->validated('class_group_id'));

        $action->execute($student, $classGroup, auth()->id(), $request->validated('note'));

        return redirect()->route('class-group-students.index')
            ->with('status', "{$student->full_name} berhasil ditempatkan ke {$classGroup->name}.");
    }

    public function transfer(ClassGroupStudent $classGroupStudent, TransferStudentRequest $request, TransferStudentAction $action): RedirectResponse
    {
        $targetClassGroup = ClassGroup::query()->findOrFail($request->validated('target_class_group_id'));

        $action->execute($classGroupStudent, $targetClassGroup, auth()->id(), $request->validated('note'));

        return redirect()->route('class-group-students.index')
            ->with('status', "{$classGroupStudent->student->full_name} berhasil dipindahkan ke {$targetClassGroup->name}.");
    }

    /**
     * Tempatkan / pindahkan banyak siswa sekaligus ke 1 rombel tujuan.
     * Per siswa dipilih otomatis: belum punya rombel aktif → assign,
     * sudah punya di rombel lain → transfer, sudah di rombel tujuan →
     * dilewati. Semua dalam 1 transaction: kalau 1 siswa gagal, tidak
     * ada yang berubah (menghindari hasil setengah jadi).
     */
    public function bulk(
        BulkPlotStudentsRequest $request,
        AssignStudentToClassGroupAction $assignAction,
        TransferStudentAction $transferAction,
    ): RedirectResponse {
        $classGroup = ClassGroup::query()->findOrFail($request->validated('class_group_id'));
        $activeYear = AcademicYear::query()->where('is_active', true)->first();

        if (! $activeYear || $classGroup->academic_year_id !== $activeYear->id) {
            throw ValidationException::withMessages([
                'class_group_id' => 'Rombel tujuan harus berada di tahun ajaran yang sedang aktif.',
            ]);
        }

        $studentIds = $request->validated('student_ids');
        $note = $request->validated('note');
        $movedBy = auth()->id();

        $students = Student::query()->whereIn('id', $studentIds)->get();

        $currentAssignments = ClassGroupStudent::query()
            ->where('academic_year_id', $classGroup->academic_year_id)
            ->whereIn('student_id', $studentIds)
            ->active()
            ->get()
            ->keyBy('student_id');

        $placed = 0;
        $moved = 0;
        $skipped = 0;

        DB::transaction(function () use ($students, $currentAssignments, $classGroup, $assignAction, $transferAction, $movedBy, $note, &$placed, &$moved, &$skipped) {
            foreach ($students as $student) {
                $current = $currentAssignments->get($student->id);

                if (! $current) {
                    $assignAction->execute($student, $classGroup, $movedBy, $note);
                    $placed++;

                    continue;
                }

                if ($current->class_group_id === $classGroup->id) {
                    $skipped++;

                    continue;
                }

                $transferAction->execute($current, $classGroup, $movedBy, $note);
                $moved++;
            }
        });

        $parts = [];
        if ($placed > 0) {
            $parts[] = "{$placed} siswa ditempatkan";
        }
        if ($moved > 0) {
            $parts[] = "{$moved} siswa dipindahkan";
        }

        $message = $parts
            ? implode(' dan ', $parts) . " ke {$classGroup->name}."
            : "Tidak ada perubahan: semua siswa terpilih sudah berada di {$classGroup->name}.";

        if ($parts && $skipped > 0) {
            $message .= " ({$skipped} siswa yang sudah di rombel tersebut dilewati.)";
        }

        return redirect()->route('class-group-students.index')->with('status', $message);
    }
}
