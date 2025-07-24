<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function dashboardData()
    {
        // Dummy data for demonstration
        $data = [
            'kpis' => [
                'total_sales_today' => 12500.50,
                'low_stock_alerts' => [
                    ['product' => 'Aluminum Sheet 21ft', 'available' => 3],
                    ['product' => 'Glass Panel 5x8', 'available' => 2],
                ],
                'top_products' => [
                    ['name' => 'Aluminum Sheet 21ft', 'sold' => 15],
                    ['name' => 'Glass Panel 5x8', 'sold' => 10],
                    ['name' => 'Screws (100pcs)', 'sold' => 8],
                ],
            ],
            'quick_access' => [
                ['label' => 'New Sale', 'route' => '/sales/create'],
                ['label' => 'Inventory', 'route' => '/inventory'],
                ['label' => 'Reports', 'route' => '/reports'],
            ],
        ];
        return response()->json($data);
    }
} 