<?php

namespace Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Academic\Actions\CreateReportCardAction;
use Modules\Academic\Actions\DeleteReportCardAction;
use Modules\Academic\Actions\PublishReportCardAction;
use Modules\Academic\Actions\UpdateReportCardAction;
use Modules\Academic\Models\ReportCard;
use Modules\Academic\Requests\StoreReportCardRequest;
use Modules\Academic\Requests\UpdateReportCardRequest;
use Modules\Core\Models\Semester;
use Modules\Student\Models\Student;

class ReportCardController extends Controller
{
    public function index(): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->first();

        return view('modules.academic.report-cards.index', [
            'activeSemester' => $activeSemester,
            'reportCards' => ReportCard::query()
                ->with(['student', 'classGroup', 'semester'])
                ->when($activeSemester, fn ($query) => $query->where('semester_id', $activeSemester->id))
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.academic.report-cards.create', [
            'students' => Student::query()->where('status', 'aktif')->orderBy('full_name')->get(),
            'semesters' => Semester::query()->with('academicYear')->orderByDesc('start_date')->get(),
        ]);
    }

    public function store(StoreReportCardRequest $request, CreateReportCardAction $action): RedirectResponse
    {
        $student = Student::query()->findOrFail($request->validated('student_id'));
        $semester = Semester::query()->findOrFail($request->validated('semester_id'));

        $action->execute($student, $semester);

        return redirect()->route('report-cards.index')->with('status', "Rapor untuk {$student->full_name} berhasil dibuat (draft).");
    }

    public function edit(ReportCard $reportCard): View
    {
        return view('modules.academic.report-cards.edit', [
            'reportCard' => $reportCard->load(['student', 'classGroup', 'semester']),
        ]);
    }

    public function update(UpdateReportCardRequest $request, ReportCard $reportCard, UpdateReportCardAction $action): RedirectResponse
    {
        $action->execute($reportCard, $request->validated());

        return redirect()->route('report-cards.edit', $reportCard)->with('status', 'Catatan rapor berhasil disimpan.');
    }

    public function publish(ReportCard $reportCard, PublishReportCardAction $action): RedirectResponse
    {
        $action->execute($reportCard);

        return redirect()->route('report-cards.index')->with('status', 'Rapor berhasil dipublikasikan — sudah bisa dilihat orang tua.');
    }

    public function destroy(ReportCard $reportCard, DeleteReportCardAction $action): RedirectResponse
    {
        $action->execute($reportCard);

        return redirect()->route('report-cards.index')->with('status', 'Rapor berhasil dihapus.');
    }
}
