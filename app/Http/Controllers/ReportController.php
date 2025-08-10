<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\Cargo;
use App\Models\Payment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Generate a report for cargo shipments by month and transport mode
    public function cargoReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $cargoData = Cargo::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('transport_mode, sum(measure_value) as total_measure, sum(total_amount) as total_amount')
            ->groupBy('transport_mode')
            ->get();

        return response()->json($cargoData);
    }

    // Generate a report for payments (paid and pending amounts)
    public function paymentReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $paymentData = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('sum(paid_amount) as total_paid, sum(pending_amount) as total_pending')
            ->first();

        return response()->json($paymentData);
    }

    // Generate a report for clients' monthly and overall data
    public function clientReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $clientData = Client::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('count(*) as total_clients, sum(is_repeating) as total_repeating_clients')
            ->first();

        return response()->json($clientData);
    }

    // Generate a summary report with various metrics
    public function summaryReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Get total revenue
        $revenue = Cargo::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // Get total clients and repeating clients
        $clients = Client::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('count(*) as total_clients, sum(is_repeating) as total_repeating_clients')
            ->first();

        // Get total payments
        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('sum(paid_amount) as total_paid, sum(pending_amount) as total_pending')
            ->first();

        return response()->json([
            'revenue' => $revenue,
            'clients' => $clients,
            'payments' => $payments,
        ]);
    }
}
