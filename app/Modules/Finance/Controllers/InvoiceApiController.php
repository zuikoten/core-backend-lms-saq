<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Finance\Actions\FindInvoicesForStudentAction;
use Modules\Finance\Actions\GetInvoiceDetailAction;
use Modules\Finance\Actions\GetInvoiceSummaryForStudentAction;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Requests\InvoiceIndexRequest;
use Modules\Finance\Requests\InvoiceSummaryRequest;
use Modules\Finance\Resources\InvoiceDetailResource;
use Modules\Finance\Resources\InvoiceResource;
use Modules\Student\Models\ParentProfile;

/**
 * Scope query SELALU dibatasi ke parent_id milik user yang login lewat
 * ParentProfile::user_id — pola sama dengan StudentApiController.
 */
class InvoiceApiController extends Controller
{
    public function index(InvoiceIndexRequest $request, FindInvoicesForStudentAction $action): AnonymousResourceCollection
    {
        $this->authorizeStudentOwnership($request, $request->validated('student_id'));

        $invoices = $action->execute(
            $request->validated('student_id'),
            $request->validated('status'),
            $request->validated('academic_year_id'),
        );

        return InvoiceResource::collection($invoices);
    }

    public function show(Request $request, Invoice $invoice, GetInvoiceDetailAction $action): InvoiceDetailResource
    {
        $this->authorizeStudentOwnership($request, $invoice->student_id);

        $result = $action->execute($invoice);

        return (new InvoiceDetailResource($result['invoice']))->additional([
            'meta' => [
                'grade_level_name' => $result['grade_level_name'],
                'class_group_name' => $result['class_group_name'],
            ],
        ]);
    }

    public function summary(InvoiceSummaryRequest $request, GetInvoiceSummaryForStudentAction $action): JsonResponse
    {
        $this->authorizeStudentOwnership($request, $request->validated('student_id'));

        return response()->json($action->execute($request->validated('student_id')));
    }

    private function authorizeStudentOwnership(Request $request, int $studentId): void
    {
        $parentProfile = ParentProfile::query()
            ->where('user_id', $request->user()->id)
            ->with('students')
            ->firstOrFail();

        if (! $parentProfile->students->contains('id', $studentId)) {
            abort(403, 'Anda tidak punya akses ke data siswa ini.');
        }
    }
}