<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class PurchaseItem extends Model {
  protected $fillable = ['purchase_id','product_id','qty','unit_cost','line_total'];
  public function product() { return $this->belongsTo(Product::class); }
}
