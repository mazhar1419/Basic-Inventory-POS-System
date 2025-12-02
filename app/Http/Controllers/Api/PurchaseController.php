<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Purchase; use App\Models\PurchaseItem; use App\Models\Product;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;
use Log;

class PurchaseController extends Controller {
 public function index(Request $r)
{
    // optionally allow search/filter by supplier name
    $q = Purchase::with(['supplier','items'])->orderBy('created_at','desc');

    if ($r->filled('supplier')) {
        $s = $r->input('supplier');
        $q->whereHas('supplier', function($w) use($s) {
            $w->where('name', 'like', "%{$s}%");
        });
    }

    return $q->paginate(15);
}


  // payload: supplier, items: [{product_id, qty, unit_cost}]
 // app/Http/Controllers/Api/PurchaseController.php

public function store(Request $r){
    $data = $r->validate([
        'supplier_id' => 'nullable|integer|exists:suppliers,id',
        'supplier' => 'nullable|string',
        'items' => 'required|array',
        'items.*.product_id' => 'required|integer|exists:products,id',
        'items.*.qty' => 'required|integer|min:1',
        'items.*.unit_cost' => 'required|numeric|min:0',
        'reference' => 'nullable|string'
    ]);

    DB::beginTransaction();
    try {
      $total = 0;
      foreach($data['items'] as $it) $total += ($it['qty'] * $it['unit_cost']);

      // store both supplier_id (if provided) and supplier text (legacy)
      $purchase = Purchase::create([
        'supplier' => $data['supplier'] ?? null,
        'supplier_id' => $data['supplier_id'] ?? null,
        'total' => $total,
        'reference' => $data['reference'] ?? null
      ]);

      foreach($data['items'] as $it){
        $product = Product::findOrFail($it['product_id']);
        $line_total = $it['qty'] * $it['unit_cost'];

        PurchaseItem::create([
          'purchase_id' => $purchase->id,
          'product_id' => $product->id,
          'qty' => $it['qty'],
          'unit_cost' => $it['unit_cost'],
          'line_total' => $line_total
        ]);

        if($product->track_stock){
          $product->stock += $it['qty'];
          $product->save();
        }
      }

      DB::commit();
      // load relations so response includes supplier object and items
      return response()->json($purchase->load('items','supplier'), 201);
    } catch(\Throwable $e) {
      DB::rollBack();
      \Log::error('Purchase store failed: '.$e->getMessage(), ['exception'=>$e]);
      return response()->json(['message'=>$e->getMessage()],500);
    }
}

}
