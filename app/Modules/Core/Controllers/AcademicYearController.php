<?php

namespace Modules\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Actions\ActivateAcademicYearAction;
use Modules\Core\Actions\CreateAcademicYearAction;
use Modules\Core\Actions\DeleteAcademicYearAction;
use Modules\Core\Actions\UpdateAcademicYearAction;
use Modules\Core\Models\AcademicYear;
use Modules\Core\Requests\StoreAcademicYearRequest;
use Modules\Core\Requests\UpdateAcademicYearRequest;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        return view('modules.core.academic-years.index', [
            'academicYears' => AcademicYear::query()->latest('year_name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.core.academic-years.create');
    }

    public function store(StoreAcademicYearRequest $request, CreateAcademicYearAction $action): RedirectResponse
    {
        $action->execute($request->validated('year_name'));

        return redirect()->route('academic-years.index')
            ->with('status', 'Tahun ajaran baru berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('modules.core.academic-years.edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear, UpdateAcademicYearAction $action): RedirectResponse
    {
        $action->execute($academicYear, $request->validated('year_name'));

        return redirect()->route('academic-years.index')
            ->with('status', 'Tahun ajaran berhasil diperbarui.');
    }

    public function activate(AcademicYear $academicYear, ActivateAcademicYearAction $action): RedirectResponse
    {
        $action->execute($academicYear);

        return redirect()->route('academic-years.index')
            ->with('status', "Tahun ajaran {$academicYear->year_name} sekarang aktif.");
    }

    public function destroy(AcademicYear $academicYear, DeleteAcademicYearAction $action): RedirectResponse
    {
        $action->execute($academicYear);

        return redirect()->route('academic-years.index')
            ->with('status', 'Tahun ajaran berhasil dihapus.');
    }
}
