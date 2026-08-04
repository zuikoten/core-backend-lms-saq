<?php

namespace Modules\Academic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Academic\Models\ClassGroupStudent;
use Modules\Academic\Resources\ClassGroupResource;
use Modules\Student\Models\ParentProfile;

/**
 * Read-only. Scope SELALU dibatasi ke anak milik user yang login lewat
 * ParentProfile — sama pola dengan StudentApiController.
 */
class ClassGroupApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $parentProfile = ParentProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $studentIds = $parentProfile->students()->pluck('id');

        $activeAssignments = ClassGroupStudent::query()
            ->whereIn('student_id', $studentIds)
            ->active()
            ->with('classGroup.gradeLevel.jenjang', 'classGroup.academicYear', 'classGroup.classroom')
            ->get()
            ->pluck('classGroup');

        return ClassGroupResource::collection($activeAssignments);
    }
}
