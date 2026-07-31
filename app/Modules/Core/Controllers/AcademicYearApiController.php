<?php

namespace Modules\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Core\Models\AcademicYear;
use Modules\Core\Resources\AcademicYearResource;

/**
 * Read-only, dikonsumsi aplikasi React orang tua. Tidak ada
 * store/update/destroy di sini — manajemen tahun ajaran murni
 * tugas staf lewat AcademicYearController (panel Blade).
 */
class AcademicYearApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AcademicYearResource::collection(
            AcademicYear::query()->latest('year_name')->get()
        );
    }

    public function active(): AcademicYearResource
    {
        $academicYear = AcademicYear::query()->where('is_active', true)->firstOrFail();

        return new AcademicYearResource($academicYear);
    }
}
