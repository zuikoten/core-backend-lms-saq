<?php

namespace Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Academic\Actions\CreateClassGroupAction;
use Modules\Academic\Actions\DeleteClassGroupAction;
use Modules\Academic\Actions\UpdateClassGroupAction;
use Modules\Academic\Models\ClassGroup;
use Modules\Academic\Requests\StoreClassGroupRequest;
use Modules\Academic\Requests\UpdateClassGroupRequest;
use Modules\Core\Models\AcademicYear;
use Modules\Core\Models\Classroom;
use Modules\Core\Models\GradeLevel;

class ClassGroupController extends Controller
{
    public function index(): View
    {
        return view('modules.academic.class-groups.index', [
            'classGroups' => ClassGroup::query()
                ->with(['gradeLevel', 'academicYear', 'classroom'])
                ->withCount(['studentHistory as active_student_count' => fn ($query) => $query->active()])
                ->orderByDesc('academic_year_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.academic.class-groups.create', [
            'gradeLevels' => GradeLevel::query()->with('jenjang')->orderBy('sort_order')->get(),
            'academicYears' => AcademicYear::query()->orderByDesc('year_name')->get(),
            'classrooms' => Classroom::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreClassGroupRequest $request, CreateClassGroupAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('class-groups.index')->with('status', 'Rombel berhasil ditambahkan.');
    }

    public function show(ClassGroup $classGroup): View
    {
        return view('modules.academic.class-groups.show', [
            'classGroup' => $classGroup->load(['gradeLevel', 'academicYear', 'classroom']),
            'activeStudents' => $classGroup->activeStudents()->get(),
        ]);
    }

    public function edit(ClassGroup $classGroup): View
    {
        return view('modules.academic.class-groups.edit', [
            'classGroup' => $classGroup,
            'classrooms' => Classroom::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateClassGroupRequest $request, ClassGroup $classGroup, UpdateClassGroupAction $action): RedirectResponse
    {
        $action->execute($classGroup, $request->validated());

        return redirect()->route('class-groups.index')->with('status', 'Rombel berhasil diperbarui.');
    }

    public function destroy(ClassGroup $classGroup, DeleteClassGroupAction $action): RedirectResponse
    {
        $action->execute($classGroup);

        return redirect()->route('class-groups.index')->with('status', 'Rombel berhasil dihapus.');
    }
}
