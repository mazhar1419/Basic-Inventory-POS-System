<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SaleItem;
use App\Models\Customer;

class Sale extends Model {
  protected $fillable = ['customer_id','total','paid','reference'];
  public function items() { return $this->hasMany(SaleItem::class); }
  public function customer() { return $this->belongsTo(Customer::class); }
}
