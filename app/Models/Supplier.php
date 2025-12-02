<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name','email','phone','address','notes'];

    // relationships (optional) - e.g., purchases
    public function purchases() {
        return $this->hasMany(\App\Models\Purchase::class);
    }
}
