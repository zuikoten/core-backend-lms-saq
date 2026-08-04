<?php

namespace Modules\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Actions\CreateGradeLevelAction;
use Modules\Core\Actions\DeleteGradeLevelAction;
use Modules\Core\Actions\UpdateGradeLevelAction;
use Modules\Core\Models\GradeLevel;
use Modules\Core\Models\Jenjang;
use Modules\Core\Requests\StoreGradeLevelRequest;
use Modules\Core\Requests\UpdateGradeLevelRequest;

class GradeLevelController extends Controller
{
    public function index(): View
    {
        return view('modules.core.grade-levels.index', [
            'gradeLevels' => GradeLevel::query()->with('jenjang')->orderBy('jenjang_id')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.core.grade-levels.create', [
            'jenjangList' => Jenjang::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreGradeLevelRequest $request, CreateGradeLevelAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('grade-levels.index')->with('status', 'Tingkat/Grade Level berhasil ditambahkan.');
    }

    public function edit(GradeLevel $gradeLevel): View
    {
        return view('modules.core.grade-levels.edit', [
            'gradeLevel' => $gradeLevel,
            'jenjangList' => Jenjang::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateGradeLevelRequest $request, GradeLevel $gradeLevel, UpdateGradeLevelAction $action): RedirectResponse
    {
        $action->execute($gradeLevel, $request->validated());

        return redirect()->route('grade-levels.index')->with('status', 'Tingkat/Grade Level berhasil diperbarui.');
    }

    public function destroy(GradeLevel $gradeLevel, DeleteGradeLevelAction $action): RedirectResponse
    {
        $action->execute($gradeLevel);

        return redirect()->route('grade-levels.index')->with('status', 'Tingkat/Grade Level berhasil dihapus.');
    }
}
