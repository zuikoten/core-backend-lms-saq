<?php

namespace Modules\Academic\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\ReportCard;

class DeleteReportCardAction
{
    /**
     * Rapor yang sudah published tidak boleh dihapus — sudah jadi
     * dokumen resmi yang mungkin sudah dilihat orang tua.
     */
    public function execute(ReportCard $reportCard): void
    {
        if ($reportCard->status === 'published') {
            throw ValidationException::withMessages([
                'report_card' => 'Rapor yang sudah dipublikasikan tidak bisa dihapus.',
            ]);
        }

        $reportCard->delete();
    }
}
