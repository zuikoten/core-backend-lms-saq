<?php

namespace Modules\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Actions\ActivateSemesterAction;
use Modules\Core\Actions\CreateSemesterAction;
use Modules\Core\Actions\DeleteSemesterAction;
use Modules\Core\Actions\UpdateSemesterAction;
use Modules\Core\Models\AcademicYear;
use Modules\Core\Models\Semester;
use Modules\Core\Requests\StoreSemesterRequest;
use Modules\Core\Requests\UpdateSemesterRequest;

class SemesterController extends Controller
{
    public function index(): View
    {
        return view('modules.core.semesters.index', [
            'semesters' => Semester::query()->with('academicYear')->orderByDesc('start_date')->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.core.semesters.create', [
            'academicYears' => AcademicYear::query()->orderByDesc('year_name')->get(),
        ]);
    }

    public function store(StoreSemesterRequest $request, CreateSemesterAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('semesters.index')->with('status', 'Semester berhasil ditambahkan.');
    }

    public function edit(Semester $semester): View
    {
        return view('modules.core.semesters.edit', ['semester' => $semester]);
    }

    public function update(UpdateSemesterRequest $request, Semester $semester, UpdateSemesterAction $action): RedirectResponse
    {
        $action->execute($semester, $request->validated());

        return redirect()->route('semesters.index')->with('status', 'Semester berhasil diperbarui.');
    }

    public function activate(Semester $semester, ActivateSemesterAction $action): RedirectResponse
    {
        $action->execute($semester);

        return redirect()->route('semesters.index')->with('status', "Semester {$semester->name} sekarang aktif.");
    }

    public function destroy(Semester $semester, DeleteSemesterAction $action): RedirectResponse
    {
        $action->execute($semester);

        return redirect()->route('semesters.index')->with('status', 'Semester berhasil dihapus.');
    }
}
