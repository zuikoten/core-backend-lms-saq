<?php

namespace App\Dashboard\Controllers;

use App\Dashboard\Actions\GetAcademicSummaryAction;
use App\Dashboard\Actions\GetDashboardAlertsAction;
use App\Dashboard\Actions\GetDashboardSummaryStatsAction;
use App\Dashboard\Actions\GetFinanceChartDataAction;
use App\Dashboard\Actions\GetRecentActivityAction;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        GetDashboardSummaryStatsAction $statsAction,
        GetFinanceChartDataAction $chartAction,
        GetRecentActivityAction $activityAction,
        GetDashboardAlertsAction $alertsAction,
        GetAcademicSummaryAction $academicAction,
    ): View {
        return view('dashboard.index', [
            'stats' => $statsAction->execute(),
            'chart' => $chartAction->execute(),
            'activities' => $activityAction->execute(),
            'alerts' => $alertsAction->execute(),
            'academic' => $academicAction->execute(),
        ]);
    }
}
