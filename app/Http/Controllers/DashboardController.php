<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalStockValue = Product::sum(DB::raw('price * quantity'));
        $limiteStock = 5;
        $lowStockProducts = Product::where('quantity', '<', $limiteStock)->get();
        $stockByCategory = Category::with(['products' => function($q) {
            $q->select('category_id', DB::raw('SUM(quantity) as total_stock'))
            ->groupBy('category_id');
        }])->get()->map(function ($category) {
            return [
                'category' => $category->name,
                'stock' => $category->products->sum('total_stock')
            ];
        });
        return response()->json([
            'totalProducts' => $totalProducts,
            'totalStockValue' => $totalStockValue,
            'lowStockProductsCount' => $lowStockProducts->count(),
            'lowStockProducts' => $lowStockProducts,
            'stockByCategory' => $stockByCategory,
        ]);
    }
    

}
