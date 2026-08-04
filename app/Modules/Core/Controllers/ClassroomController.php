<?php

namespace Modules\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Actions\CreateClassroomAction;
use Modules\Core\Actions\DeleteClassroomAction;
use Modules\Core\Actions\UpdateClassroomAction;
use Modules\Core\Models\Classroom;
use Modules\Core\Requests\StoreClassroomRequest;
use Modules\Core\Requests\UpdateClassroomRequest;

class ClassroomController extends Controller
{
    public function index(): View
    {
        return view('modules.core.classrooms.index', [
            'classrooms' => Classroom::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.core.classrooms.create');
    }

    public function store(StoreClassroomRequest $request, CreateClassroomAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('classrooms.index')->with('status', 'Ruang kelas berhasil ditambahkan.');
    }

    public function edit(Classroom $classroom): View
    {
        return view('modules.core.classrooms.edit', ['classroom' => $classroom]);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom, UpdateClassroomAction $action): RedirectResponse
    {
        $action->execute($classroom, $request->validated());

        return redirect()->route('classrooms.index')->with('status', 'Ruang kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom, DeleteClassroomAction $action): RedirectResponse
    {
        $action->execute($classroom);

        return redirect()->route('classrooms.index')->with('status', 'Ruang kelas berhasil dihapus.');
    }
}
