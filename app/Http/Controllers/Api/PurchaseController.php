<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Purchase; use App\Models\PurchaseItem; use App\Models\Product;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class PurchaseController extends Controller {
  public function index(){ return Purchase::orderBy('created_at','desc')->paginate(15); }

  // payload: supplier, items: [{product_id, qty, unit_cost}]
  public function store(Request $r){
    $data = $r->validate(['supplier'=>'nullable|string','items'=>'required|array']);
    DB::beginTransaction();
    try {
      $total = 0;
      foreach($data['items'] as $it) $total += ($it['qty'] * $it['unit_cost']);
      $purchase = Purchase::create(['supplier'=>$data['supplier'] ?? null,'total'=>$total,'reference'=>$data['reference'] ?? null]);
      foreach($data['items'] as $it){
        $product = Product::findOrFail($it['product_id']);
        $line_total = $it['qty'] * $it['unit_cost'];
        PurchaseItem::create(['purchase_id'=>$purchase->id,'product_id'=>$product->id,'qty'=>$it['qty'],'unit_cost'=>$it['unit_cost'],'line_total'=>$line_total]);
        if($product->track_stock){
          $product->stock += $it['qty'];
          $product->save();
        }
      }
      DB::commit();
      return response()->json($purchase->load('items'), 201);
    } catch(\Throwable $e) { DB::rollBack(); return response()->json(['message'=>$e->getMessage()],500); }
  }
}
