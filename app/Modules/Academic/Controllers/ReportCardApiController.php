<?php

namespace Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Academic\Models\ReportCard;
use Modules\Academic\Resources\ReportCardResource;
use Modules\Student\Models\ParentProfile;

/**
 * Read-only, dan HANYA rapor berstatus 'published' — draft tidak pernah
 * boleh terlihat orang tua lewat endpoint ini, sekalipun mereka tahu ID
 * rapornya (query di-scope dari awal, bukan disaring belakangan).
 */
class ReportCardApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $parentProfile = ParentProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $studentIds = $parentProfile->students()->pluck('id');

        $reportCards = ReportCard::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'published')
            ->with(['classGroup', 'semester.academicYear'])
            ->orderByDesc('published_at')
            ->get();

        return ReportCardResource::collection($reportCards);
    }
}
