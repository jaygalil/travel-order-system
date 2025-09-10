<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelOrder;
use App\Models\TravelOrderApproval;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard home page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's travel order statistics
        $stats = [
            'total' => TravelOrder::where('user_id', $user->id)
                ->orWhere('prepared_by_user_id', $user->id)
                ->count(),
            'draft' => TravelOrder::where('user_id', $user->id)
                ->orWhere('prepared_by_user_id', $user->id)
                ->where('status', 'draft')
                ->count(),
            'pending' => TravelOrder::where('user_id', $user->id)
                ->orWhere('prepared_by_user_id', $user->id)
                ->where('status', 'pending_approval')
                ->count(),
            'approved' => TravelOrder::where('user_id', $user->id)
                ->orWhere('prepared_by_user_id', $user->id)
                ->where('status', 'approved')
                ->count(),
        ];
        
        // Get recent travel orders
        $recentTravelOrders = TravelOrder::where('user_id', $user->id)
            ->orWhere('prepared_by_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get pending approvals for this user (if they are an approver)
        $pendingApprovals = TravelOrderApproval::where('approver_user_id', $user->id)
            ->where('status', 'pending')
            ->with('travelOrder')
            ->limit(5)
            ->get();
        
        return view('dashboard', compact('stats', 'recentTravelOrders', 'pendingApprovals'));
    }
    
    /**
     * Admin users management
     */
    public function users()
    {
        $this->authorize('viewAny', User::class);
        
        $users = User::with('roles')
                     ->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }
    
    /**
     * Show reports and analytics page.
     */
    public function reports()
    {
        $this->authorize('viewAny', TravelOrder::class);
        
        // Status distribution
        $statusStats = TravelOrder::select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        
        // Top destinations
        $topDestinations = TravelOrder::select('farthest_destination')
            ->selectRaw('COUNT(*) as count')
            ->where('farthest_destination', '!=', '')
            ->whereNotNull('farthest_destination')
            ->groupBy('farthest_destination')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
        
        // Monthly statistics for the last 12 months
        $monthlyStats = TravelOrder::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN status = "pending_approval" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        // Recent activity (last 20 travel orders)
        $recentActivity = TravelOrder::with('preparedBy')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        
        return view('admin.reports', compact(
            'statusStats',
            'topDestinations', 
            'monthlyStats',
            'recentActivity'
        ));
    }
}
