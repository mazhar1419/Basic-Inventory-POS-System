<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Damage; use App\Models\Product;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;

class DamageController extends Controller {
  public function index(){ return Damage::with('product')->orderBy('created_at','desc')->paginate(20); }

  public function store(Request $r){
    $data = $r->validate(['product_id'=>'required|exists:products,id','qty'=>'required|integer|min:1','reason'=>'nullable|string']);
    DB::beginTransaction();
    try {
      $product = Product::findOrFail($data['product_id']);
      // reduce stock
      if($product->track_stock){
        $product->stock = max(0, $product->stock - $data['qty']);
        $product->save();
      }
      $damage = Damage::create($data);
      DB::commit();
      return response()->json($damage, 201);
    } catch(\Throwable $e) { DB::rollBack(); return response()->json(['message'=>$e->getMessage()], 500); }
  }
}
