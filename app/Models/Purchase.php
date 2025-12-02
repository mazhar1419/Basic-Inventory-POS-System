<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['supplier', 'supplier_id', 'total', 'reference'];

    // relation to supplier model
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\PurchaseItem::class);
    }
}
