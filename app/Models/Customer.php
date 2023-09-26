<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','kana','tel','email','postcode','address','birthday','gender','memo'
    ];

    public function scopeSearchCustomers($query, $input = null)
    {
        if(!empty($input)){
            if(Customer::where('kana', 'like', $input . '%')
            ->orWhere('tel', 'like', $input . '%')->exists())
            {
                return $query->where('kana', 'like', $input . '%')
                ->orWhere('tel', 'like', $input . '%');
            }
        }
    }

    //リレーション（一対多）→顧客1人(customer)に対して購買情報(purchase)が複数ある場合はhasMany
    public function purchases(){ //purchasesの関数は購買情報が複数あるため複数形
        return $this->hasMany(Purchase::class);
    }
}
