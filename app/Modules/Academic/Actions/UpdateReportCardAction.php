<?php

namespace Modules\Academic\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\ReportCard;

class UpdateReportCardAction
{
    /**
     * Rapor yang sudah published tidak boleh diedit lagi lewat sini —
     * kalau ada koreksi, harus "unpublish" dulu (belum dibangun, sengaja,
     * supaya perubahan pasca-publish selalu sadar/eksplisit, bukan
     * ke-edit diam-diam padahal orang tua sudah bisa lihat).
     */
    public function execute(ReportCard $reportCard, array $data): ReportCard
    {
        if ($reportCard->status === 'published') {
            throw ValidationException::withMessages([
                'report_card' => 'Rapor yang sudah dipublikasikan tidak bisa diedit langsung.',
            ]);
        }

        $reportCard->update([
            'summary_notes' => $data['summary_notes'] ?? null,
        ]);

        return $reportCard;
    }
}
