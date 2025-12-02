<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller {
  public function index(Request $r) {
    $q = Product::query();
    if ($r->filled('search')) $q->where('name','like', "%{$r->search}%")->orWhere('sku','like', "%{$r->search}%");
    return $q->orderBy('name')->paginate(15);
  }

  public function store(Request $r) {
    $data = $r->validate([
      'name'=>'required|string|max:255','sku'=>'nullable|string|max:100|unique:products,sku',
      'cost_price'=>'nullable|numeric|min:0','sell_price'=>'nullable|numeric|min:0',
      'stock'=>'integer|min:0','track_stock'=>'boolean'
    ]);
    $product = Product::create($data);
    return response()->json($product,201);
  }

  public function show(Product $product){ return response()->json($product); }

  public function update(Request $r, Product $product){
    $data = $r->validate([
      'name'=>'required|string|max:255',
      'sku'=>['nullable','string','max:100',\Illuminate\Validation\Rule::unique('products','sku')->ignore($product->id)],
      'cost_price'=>'nullable|numeric|min:0','sell_price'=>'nullable|numeric|min:0',
      'stock'=>'integer|min:0','track_stock'=>'boolean'
    ]);
    $product->update($data);
    return response()->json($product);
  }

  public function destroy(Product $product){ $product->delete(); return response()->json(['message'=>'Deleted']); }

  // CSV export
  public function exportCsv() : StreamedResponse {
    $columns = ['id','name','sku','stock','cost_price','sell_price','created_at'];
    $callback = function() use($columns) {
      $fh = fopen('php://output','w');
      fputcsv($fh,$columns);
      Product::orderBy('id')->chunk(200, function($rows) use($fh){
        foreach($rows as $r) fputcsv($fh, [$r->id,$r->name,$r->sku,$r->stock,$r->cost_price,$r->sell_price,$r->created_at]);
      });
      fclose($fh);
    };
    return response()->stream($callback, 200, [
      "Content-Type" => "text/csv",
      "Content-Disposition" => "attachment; filename=products.csv",
    ]);
  }
}
