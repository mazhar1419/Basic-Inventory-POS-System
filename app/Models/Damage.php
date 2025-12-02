<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Sale;

class Damage extends Model {
  protected $fillable = ['product_id','qty','reason','sale_id'];
  public function product() { return $this->belongsTo(Product::class); }
  public function sale() { return $this->belongsTo(Sale::class); }
}
