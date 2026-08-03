<?php

namespace Modules\Student\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Student\Models\ParentProfile;
use Modules\Student\Resources\StudentResource;

/**
 * Scope query SELALU dibatasi ke parent_id milik user yang login lewat
 * ParentProfile::user_id — orang tua tidak pernah bisa lihat data siswa
 * lain lewat endpoint ini.
 */
class StudentApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $parentProfile = ParentProfile::query()
            ->where('user_id', $request->user()->id)
            ->with('students')
            ->firstOrFail();

        return StudentResource::collection($parentProfile->students);
    }
}