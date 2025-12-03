<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Damage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    // --- Helper to parse date inputs safely
    protected function parseDate(string $d = null)
    {
        if (!$d) return null;
        try { return date('Y-m-d', strtotime($d)); } catch (\Throwable $e) { return null; }
    }

    // ---------------------------
    // SALES BY DATE (aggregation)
    // ---------------------------
    public function salesByDate(Request $request): JsonResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $q = Sale::query();

        if ($productId) {
            // join sale_items to filter by product
            $q->whereHas('items', function($w) use($productId) { $w->where('product_id', $productId); });
        }

        if ($dateFrom) $q->whereDate('created_at','>=',$dateFrom);
        if ($dateTo)   $q->whereDate('created_at','<=',$dateTo);

        $rows = $q->selectRaw('DATE(created_at) as date, COUNT(*) as sales_count, SUM(total) as total_amount, SUM(paid) as total_paid')
                  ->groupBy(DB::raw('DATE(created_at)'))
                  ->orderBy('date','desc')
                  ->get();

        return response()->json($rows);
    }

    public function salesByDateCsv(Request $request): StreamedResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $filename = 'sales_by_date_' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($dateFrom, $dateTo, $productId) {
            $handle = fopen('php://output','w');
            fputcsv($handle, ['date','sales_count','total_amount','total_paid']);

            $q = Sale::query();
            if ($productId) $q->whereHas('items', function($w) use($productId) { $w->where('product_id',$productId); });
            if ($dateFrom) $q->whereDate('created_at','>=',$dateFrom);
            if ($dateTo) $q->whereDate('created_at','<=',$dateTo);

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
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>"attachment; filename=\"$filename\""
        ]);
    }

    // -----------------------------------------
    // SALES BY PRODUCT (qty sold, revenue per product)
    // -----------------------------------------
    public function salesByProduct(Request $request): JsonResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        // join sale_items -> sum qty and revenue
        $q = DB::table('sale_items')
               ->join('sales','sale_items.sale_id','=','sales.id')
               ->join('products','sale_items.product_id','=','products.id')
               ->selectRaw('sale_items.product_id, products.name, products.sku, SUM(sale_items.qty) as qty_sold, SUM(sale_items.line_total) as revenue')
               ->groupBy('sale_items.product_id','products.name','products.sku');

        if ($productId) $q->where('sale_items.product_id', $productId);
        if ($dateFrom) $q->whereDate('sales.created_at','>=',$dateFrom);
        if ($dateTo)   $q->whereDate('sales.created_at','<=',$dateTo);

        $rows = $q->orderBy('revenue','desc')->get();

        return response()->json($rows);
    }

    public function salesByProductCsv(Request $request): StreamedResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $filename = 'sales_by_product_' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($dateFrom, $dateTo, $productId) {
            $handle = fopen('php://output','w');
            fputcsv($handle, ['product_id','name','sku','qty_sold','revenue']);

            $q = DB::table('sale_items')
                   ->join('sales','sale_items.sale_id','=','sales.id')
                   ->join('products','sale_items.product_id','=','products.id')
                   ->selectRaw('sale_items.product_id, products.name, products.sku, SUM(sale_items.qty) as qty_sold, SUM(sale_items.line_total) as revenue')
                   ->groupBy('sale_items.product_id','products.name','products.sku');

            if ($productId) $q->where('sale_items.product_id', $productId);
            if ($dateFrom) $q->whereDate('sales.created_at','>=',$dateFrom);
            if ($dateTo)   $q->whereDate('sales.created_at','<=',$dateTo);

            $q->orderBy('revenue','desc')->chunk(200, function($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->product_id, $r->name, $r->sku, $r->qty_sold, $r->revenue]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback,200,[
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>"attachment; filename=\"$filename\""
        ]);
    }

    // -----------------------
    // PURCHASES (by date / by product)
    // -----------------------
    public function purchasesByDate(Request $request): JsonResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $q = Purchase::query();
        if ($productId) {
            $q->whereHas('items', function($w) use($productId){ $w->where('product_id',$productId); });
        }
        if ($dateFrom) $q->whereDate('created_at','>=',$dateFrom);
        if ($dateTo) $q->whereDate('created_at','<=',$dateTo);

        $rows = $q->selectRaw('DATE(created_at) as date, COUNT(*) as purchase_count, SUM(total) as total_amount')
                  ->groupBy(DB::raw('DATE(created_at)'))
                  ->orderBy('date','desc')
                  ->get();

        return response()->json($rows);
    }

    public function purchasesByDateCsv(Request $request): StreamedResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $filename = 'purchases_by_date_' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($dateFrom, $dateTo, $productId) {
            $handle = fopen('php://output','w');
            fputcsv($handle, ['date','purchase_count','total_amount']);

            $q = Purchase::query();
            if ($productId) $q->whereHas('items', function($w) use($productId){ $w->where('product_id',$productId); });
            if ($dateFrom) $q->whereDate('created_at','>=',$dateFrom);
            if ($dateTo) $q->whereDate('created_at','<=',$dateTo);

            $q->selectRaw('DATE(created_at) as date, COUNT(*) as purchase_count, SUM(total) as total_amount')
              ->groupBy(DB::raw('DATE(created_at)'))
              ->orderBy('date','desc')
              ->chunk(200, function($rows) use ($handle) {
                  foreach ($rows as $r) {
                      fputcsv($handle, [$r->date, $r->purchase_count, $r->total_amount]);
                  }
              });

            fclose($handle);
        };

        return response()->stream($callback,200,[
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>"attachment; filename=\"$filename\""
        ]);
    }

    public function purchasesByProduct(Request $request): JsonResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $q = DB::table('purchase_items')
               ->join('purchases','purchase_items.purchase_id','=','purchases.id')
               ->join('products','purchase_items.product_id','=','products.id')
               ->selectRaw('purchase_items.product_id, products.name, products.sku, SUM(purchase_items.qty) as qty_purchased, SUM(purchase_items.line_total) as cost_total')
               ->groupBy('purchase_items.product_id','products.name','products.sku');

        if ($productId) $q->where('purchase_items.product_id',$productId);
        if ($dateFrom) $q->whereDate('purchases.created_at','>=',$dateFrom);
        if ($dateTo) $q->whereDate('purchases.created_at','<=',$dateTo);

        $rows = $q->orderBy('qty_purchased','desc')->get();

        return response()->json($rows);
    }

    public function purchasesByProductCsv(Request $request): StreamedResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $filename = 'purchases_by_product_' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($dateFrom, $dateTo, $productId) {
            $handle = fopen('php://output','w');
            fputcsv($handle, ['product_id','name','sku','qty_purchased','cost_total']);

            $q = DB::table('purchase_items')
                   ->join('purchases','purchase_items.purchase_id','=','purchases.id')
                   ->join('products','purchase_items.product_id','=','products.id')
                   ->selectRaw('purchase_items.product_id, products.name, products.sku, SUM(purchase_items.qty) as qty_purchased, SUM(purchase_items.line_total) as cost_total')
                   ->groupBy('purchase_items.product_id','products.name','products.sku');

            if ($productId) $q->where('purchase_items.product_id',$productId);
            if ($dateFrom) $q->whereDate('purchases.created_at','>=',$dateFrom);
            if ($dateTo) $q->whereDate('purchases.created_at','<=',$dateTo);

            $q->orderBy('qty_purchased','desc')->chunk(200, function($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->product_id, $r->name, $r->sku, $r->qty_purchased, $r->cost_total]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback,200,[
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>"attachment; filename=\"$filename\""
        ]);
    }

    // -----------------------
    // DAMAGE / WRITE-OFF REPORT
    // -----------------------
    public function damageReport(Request $request): JsonResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $q = DB::table('damages')
               ->join('products','damages.product_id','=','products.id')
               ->selectRaw('damages.product_id, products.name, products.sku, SUM(damages.qty) as qty_damaged, COUNT(damages.id) as incidents')
               ->groupBy('damages.product_id','products.name','products.sku');

        if ($productId) $q->where('damages.product_id',$productId);
        if ($dateFrom) $q->whereDate('damages.created_at','>=',$dateFrom);
        if ($dateTo) $q->whereDate('damages.created_at','<=',$dateTo);

        $rows = $q->orderBy('qty_damaged','desc')->get();

        return response()->json($rows);
    }

    public function damageCsv(Request $request): StreamedResponse
    {
        $dateFrom = $this->parseDate($request->query('date_from'));
        $dateTo   = $this->parseDate($request->query('date_to'));
        $productId = $request->query('product_id');

        $filename = 'damage_report_' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($dateFrom, $dateTo, $productId) {
            $handle = fopen('php://output','w');
            fputcsv($handle, ['product_id','name','sku','qty_damaged','incidents']);

            $q = DB::table('damages')
                   ->join('products','damages.product_id','=','products.id')
                   ->selectRaw('damages.product_id, products.name, products.sku, SUM(damages.qty) as qty_damaged, COUNT(damages.id) as incidents')
                   ->groupBy('damages.product_id','products.name','products.sku');

            if ($productId) $q->where('damages.product_id',$productId);
            if ($dateFrom) $q->whereDate('damages.created_at','>=',$dateFrom);
            if ($dateTo) $q->whereDate('damages.created_at','<=',$dateTo);

            $q->orderBy('qty_damaged','desc')->chunk(200, function($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->product_id, $r->name, $r->sku, $r->qty_damaged, $r->incidents]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback,200,[
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>"attachment; filename=\"$filename\""
        ]);
    }

    // -----------------------
    // QTY / STOCK SUMMARY (per product)
    // -----------------------
    public function qtyReport(Request $request): JsonResponse
    {
        $productId = $request->query('product_id');
        $q = Product::query()->select(['id','name','sku','stock','cost_price','sell_price']);

        if ($productId) $q->where('id',$productId);

        $rows = $q->orderBy('id')->get();

        return response()->json($rows);
    }

    public function qtyCsv(Request $request): StreamedResponse
    {
        $productId = $request->query('product_id');
        $filename = 'stock_qty_' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($productId){
            $handle = fopen('php://output','w');
            fputcsv($handle, ['id','name','sku','stock','cost_price','sell_price']);

            $q = Product::query()->select(['id','name','sku','stock','cost_price','sell_price']);
            if ($productId) $q->where('id',$productId);

            $q->orderBy('id')->chunk(200, function($rows) use ($handle){
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->id, $r->name, $r->sku, $r->stock, $r->cost_price, $r->sell_price]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback,200,[
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>"attachment; filename=\"$filename\""
        ]);
    }
}
