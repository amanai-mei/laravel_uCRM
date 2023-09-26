<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Item;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status'
    ];

    //購買テーブルと顧客テーブルのリレーション（多対一）→購買情報(purchase)が顧客1人(customer)に対して複数ある場合はbelongsTo
    public function customer() //顧客は1人なので単数系
    {
        return $this->belongsTo(Customer::class);
    }

    //中間テーブルのリレーション
    public function items()
    {
        return $this->belongsToMany(Item::class)
        ->withPivot('quantity'); //quantity(量)'はpurchaseテーブルのカラム名
    }
}
