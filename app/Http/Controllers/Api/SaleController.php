<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Sale; use App\Models\SaleItem; use App\Models\Product;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;

class SaleController extends Controller {
  public function index(Request $r){
    return Sale::with('customer')->orderBy('created_at','desc')->paginate(15);
  }

  // Expected payload:
  // { customer_id, items: [ { product_id, qty, unit_price } ], paid, reference (optional) }
  public function store(Request $r){
    $data = $r->validate(['customer_id'=>'nullable|exists:customers,id','items'=>'required|array','paid'=>'nullable|numeric|min:0']);
    $items = $data['items'];
    DB::beginTransaction();
    try {
      $total = 0;
      foreach($items as $it) $total += ($it['qty'] * $it['unit_price']);
      $sale = Sale::create([
        'customer_id'=>$data['customer_id'] ?? null,
        'total'=>$total, 'paid'=>$data['paid'] ?? 0, 'reference'=>$data['reference'] ?? null
      ]);
      foreach($items as $it){
        $product = Product::findOrFail($it['product_id']);
        $line_total = $it['qty'] * $it['unit_price'];
        SaleItem::create(['sale_id'=>$sale->id,'product_id'=>$product->id,'qty'=>$it['qty'],'unit_price'=>$it['unit_price'],'line_total'=>$line_total]);
        if($product->track_stock){
          $product->stock = max(0, $product->stock - $it['qty']);
          $product->save();
        }
      }
      DB::commit();
      return response()->json($sale->load('items'), 201);
    } catch(\Throwable $e) {
      DB::rollBack();
      return response()->json(['message'=>$e->getMessage()], 500);
    }
  }

  public function show(Sale $sale){ return response()->json($sale->load('items.product','customer')); }
}
