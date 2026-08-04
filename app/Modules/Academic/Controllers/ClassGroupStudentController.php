<?php

namespace Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Academic\Actions\AssignStudentToClassGroupAction;
use Modules\Academic\Actions\TransferStudentAction;
use Modules\Academic\Models\ClassGroup;
use Modules\Academic\Models\ClassGroupStudent;
use Modules\Academic\Requests\AssignStudentRequest;
use Modules\Academic\Requests\TransferStudentRequest;
use Modules\Core\Models\AcademicYear;
use Modules\Student\Models\Student;

/**
 * Halaman "Plotting Siswa" — 1 halaman index yang menampilkan status
 * penempatan SEMUA siswa aktif untuk tahun ajaran yang sedang aktif,
 * dengan aksi tempatkan (assign) atau pindahkan (transfer) per baris.
 * SENGAJA tidak dipecah per-rombel — staf butuh lihat siapa saja yang
 * BELUM ditempatkan sekaligus, bukan cuma per rombel satu-satu.
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
}
