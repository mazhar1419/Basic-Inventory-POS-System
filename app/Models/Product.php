<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\SaleItem;
use App\Models\PurchaseItem;
use App\Models\Damage;

class Product extends Model {
    protected $fillable = ['name','sku','description','cost_price','sell_price','stock','track_stock'];
    public function saleItems() { return $this->hasMany(SaleItem::class); }
    public function purchaseItems() { return $this->hasMany(PurchaseItem::class); }
    public function damages() { return $this->hasMany(Damage::class); }
}
