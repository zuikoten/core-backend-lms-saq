<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Academic\Models\ClassGroup;
use Modules\Finance\Actions\BulkCreateStudentTariffMappingAction;
use Modules\Finance\Actions\FindEligibleStudentsForBulkTariffMappingAction;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Requests\EligibleStudentsRequest;
use Modules\Finance\Requests\StoreBulkStudentTariffMappingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Finance\Actions\CreateStudentTariffMappingAction;
use Modules\Finance\Actions\DeleteStudentTariffMappingAction;
use Modules\Finance\Actions\UpdateStudentTariffMappingAction;
use Modules\Finance\Models\BillingTariff;
use Modules\Finance\Models\StudentTariffMapping;
use Modules\Finance\Requests\StoreStudentTariffMappingRequest;
use Modules\Finance\Requests\UpdateStudentTariffMappingRequest;
use Modules\Student\Models\Student;

class StudentTariffMappingController extends Controller
{
    public function index(): View
    {
        $studentTariffMappings = StudentTariffMapping::query()
            ->with(['student', 'billingTariff.billingType', 'academicYear', 'approvedBy'])
            ->latest('id')
            ->paginate(15);

        return view('modules.finance.student-tariff-mappings.index', compact('studentTariffMappings'));
    }

    public function create(): View
    {
        $students = Student::query()->orderBy('full_name')->get();
        $billingTariffs = BillingTariff::query()->with(['billingType', 'academicYear'])->get();
        $kepalaSekolahOptions = User::role('kepala_sekolah')->get();

        return view('modules.finance.student-tariff-mappings.create', compact('students', 'billingTariffs', 'kepalaSekolahOptions'));
    }

    public function store(StoreStudentTariffMappingRequest $request, CreateStudentTariffMappingAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('finance.student-tariff-mappings.index')
            ->with('status', 'Pemetaan tarif berhasil ditambahkan.');
    }

    public function edit(StudentTariffMapping $studentTariffMapping): View
    {
        $students = Student::query()->orderBy('full_name')->get();
        $billingTariffs = BillingTariff::query()->with(['billingType', 'academicYear'])->get();
        $kepalaSekolahOptions = User::role('kepala_sekolah')->get();

        return view('modules.finance.student-tariff-mappings.edit', compact('studentTariffMapping', 'students', 'billingTariffs', 'kepalaSekolahOptions'));
    }

    public function update(UpdateStudentTariffMappingRequest $request, StudentTariffMapping $studentTariffMapping, UpdateStudentTariffMappingAction $action): RedirectResponse
    {
        $action->execute($studentTariffMapping, $request->validated());

        return redirect()
            ->route('finance.student-tariff-mappings.index')
            ->with('status', 'Pemetaan tarif berhasil diperbarui.');
    }

    public function destroy(StudentTariffMapping $studentTariffMapping, DeleteStudentTariffMappingAction $action): RedirectResponse
    {
        $action->execute($studentTariffMapping);

        return redirect()
            ->route('finance.student-tariff-mappings.index')
            ->with('status', 'Pemetaan tarif berhasil dihapus.');
    }

    public function bulkCreate(): View
    {
        $billingTariffs = BillingTariff::query()->with(['billingType', 'academicYear'])->get();
        $classGroups = ClassGroup::query()->with(['academicYear', 'gradeLevel'])->orderByDesc('academic_year_id')->get();
        $kepalaSekolahOptions = User::role('kepala_sekolah')->get();

        return view('modules.finance.student-tariff-mappings.bulk-create', compact('billingTariffs', 'classGroups', 'kepalaSekolahOptions'));
    }

    public function bulkStore(StoreBulkStudentTariffMappingRequest $request, BulkCreateStudentTariffMappingAction $action): RedirectResponse
    {
        $billingTariff = BillingTariff::findOrFail($request->validated('billing_tariff_id'));

        $result = $action->execute(
            $billingTariff,
            $request->validated('student_ids'),
            $request->validated('note'),
            $request->validated('approved_by'),
        );

        $pesan = "{$result['created']} pemetaan tarif berhasil dibuat";
        $pesan .= $result['skipped'] > 0 ? ", {$result['skipped']} dilewati karena sudah ada pemetaan." : '.';

        return redirect()
            ->route('finance.student-tariff-mappings.index')
            ->with('status', $pesan);
    }

    public function eligibleStudents(EligibleStudentsRequest $request, FindEligibleStudentsForBulkTariffMappingAction $action): JsonResponse
    {
        $billingTariff = BillingTariff::findOrFail($request->validated('billing_tariff_id'));
        $classGroupId = $request->validated('filter_type') === 'class_group' ? $request->validated('class_group_id') : null;

        $students = $action->execute($billingTariff, $classGroupId);

        return response()->json([
            'students' => $students->map(fn ($student) => [
                'id' => $student->id,
                'full_name' => $student->full_name,
            ]),
        ]);
    }
}
