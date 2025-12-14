<?php

namespace App\Http\Controllers;

use App\Models\Nfe;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class DashboardController
 * 
 * Handles the display of the application dashboard.
 * Aggregates and presents statistical data regarding NFe status and volume.
 * 
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard with aggregated NFe statistics.
     * 
     * Retrieves counts for various NFe statuses (authorized, canceled, created),
     * calculates the total monetary volume of authorized notes, and fetches
     * the most recent NFe activities.
     * 
     * @return \Illuminate\View\View The dashboard view instance.
     */
    public function index(): View
    {
        // Calculate statistics
        /** @var int $authorizedCount Total number of authorized NFes */
        $authorizedCount = Nfe::where('status', 'authorized')->count();

        /** @var int $canceledCount Total number of canceled NFes */
        $canceledCount = Nfe::where('status', 'canceled')->count();

        /** @var int $pendingCount Total number of pending (created/rejected) NFes */
        $pendingCount = Nfe::whereIn('status', ['created', 'rejected'])->count();

        /** @var float $monthlyVolume Total value of authorized NFes */
        $monthlyVolume = Nfe::where('status', 'authorized')->sum('valor_total');

        // Get recent activity
        /** @var \Illuminate\Database\Eloquent\Collection $recentNfes Last 5 NFes */
        $recentNfes = Nfe::latest()->take(5)->get();

        return view('dashboard', compact(
            'authorizedCount',
            'canceledCount',
            'pendingCount',
            'monthlyVolume',
            'recentNfes'
        ));
    }
}
