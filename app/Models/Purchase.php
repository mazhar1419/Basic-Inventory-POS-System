<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseItem;

class Purchase extends Model {
  protected $fillable = ['supplier','total','reference'];
  public function items() { return $this->hasMany(PurchaseItem::class); }
}
