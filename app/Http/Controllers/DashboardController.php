<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cargo;
use App\Models\Client;
use App\Models\Payment;

class DashboardController extends Controller {
    public function index(Request $request) {
        try 
        {
            // Example metrics — adjust based on your actual schema
            $totalCargoShipped = Cargo::count();
            $airShipments = Cargo::where('transport_mode', 'Air')->count();
            $seaShipments = Cargo::where('transport_mode', 'Sea')->count();

            $totalClients = Client::count();
            $reportingClients = Client::where('is_active', true)->count(); // Optional logic

            $totalRevenue = Payment::sum('amount');
            $monthlyRevenue = Payment::whereMonth('created_at', now()->month)->sum('amount');
            $pendingPayments = Payment::where('status', 'pending')->sum('amount');

            return response()->json([
                'totalCargoShipped' => $totalCargoShipped,
                'airShipments' => $airShipments,
                'seaShipments' => $seaShipments,
                'totalClients' => $totalClients,
                'reportingClients' => $reportingClients,
                'totalRevenue' => $totalRevenue,
                'monthlyRevenue' => $monthlyRevenue,
                'pendingPayments' => $pendingPayments,
            ]);
        } 
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    
    }
}
