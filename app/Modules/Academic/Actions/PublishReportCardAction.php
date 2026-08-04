<?php

namespace Modules\Academic\Actions;

use Modules\Academic\Models\ReportCard;

class PublishReportCardAction
{
    /**
     * Begitu published, rapor ini langsung bisa dilihat orang tua lewat
     * ReportCardApiController (yang cuma nampilin status=published).
     */
    public function execute(ReportCard $reportCard): ReportCard
    {
        $reportCard->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $reportCard;
    }
}
