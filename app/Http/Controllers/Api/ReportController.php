<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * GET /api/reports/sales-by-date
     * Optional query params: date_from (Y-m-d), date_to (Y-m-d)
     * Returns aggregated totals grouped by sale date.
     */
    public function salesByDate(Request $request): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $q = Sale::query();

        if ($dateFrom) {
            $q->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $q->whereDate('created_at', '<=', $dateTo);
        }

        // group by date and summarize
        $rows = $q->selectRaw('DATE(created_at) as date, COUNT(*) as sales_count, SUM(total) as total_amount, SUM(paid) as total_paid')
                  ->groupBy(DB::raw('DATE(created_at)'))
                  ->orderBy('date', 'desc')
                  ->get();

        return response()->json($rows);
    }

    /**
     * Stream CSV of sales by date
     * GET /api/reports/sales-by-date-export?date_from=...&date_to=...
     */
    public function salesByDateCsv(Request $request): StreamedResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $filename = 'sales_by_date_' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($dateFrom, $dateTo) {
            $handle = fopen('php://output','w');
            fputcsv($handle, ['date','sales_count','total_amount','total_paid']);

            $q = Sale::query();
            if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);

            $q->selectRaw('DATE(created_at) as date, COUNT(*) as sales_count, SUM(total) as total_amount, SUM(paid) as total_paid')
              ->groupBy(DB::raw('DATE(created_at)'))
              ->orderBy('date','desc')
              ->chunk(200, function($rows) use ($handle) {
                  foreach ($rows as $r) {
                      fputcsv($handle, [$r->date, $r->sales_count, $r->total_amount, $r->total_paid]);
                  }
              });

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * GET /api/reports/stock
     * Returns list of products and their current stock levels.
     * Optional query params: low_stock_threshold (integer) to filter low-stock items
     */
    public function stockReport(Request $request): JsonResponse
    {
        $threshold = $request->query('low_stock_threshold');

        $q = Product::query()->select(['id','name','sku','stock','sell_price','cost_price']);

        if (is_numeric($threshold)) {
            $q->where('stock', '<=', (int)$threshold);
        }

        $rows = $q->orderBy('stock','asc')->get();

        return response()->json($rows);
    }

    /**
     * GET /api/reports/stock-export
     * Streams CSV of products + stock
     */
    public function stockCsv(Request $request): StreamedResponse
    {
        $threshold = $request->query('low_stock_threshold');
        $filename = 'stock_report_' . now()->format('Ymd_His') . '.csv';

        $callback = function() use ($threshold) {
            $handle = fopen('php://output','w');
            fputcsv($handle, ['id','name','sku','stock','cost_price','sell_price']);

            $q = Product::query()->select(['id','name','sku','stock','cost_price','sell_price']);
            if (is_numeric($threshold)) {
                $q->where('stock', '<=', (int)$threshold);
            }

            $q->orderBy('id')->chunk(200, function($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->id, $r->name, $r->sku, $r->stock, $r->cost_price, $r->sell_price]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
