<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase;

class Item extends Model
{
    use HasFactory;
    // controller側でItem::create()で保存できるようにするためにモデル側に記載する
    protected $fillable = [
        'name',
        'memo',
        'price',
        'is_selling'
    ];


    //中間テーブルのリレーション
    // 多対多
    // 中間テーブル：1つの商品に対して複数購入する時に使用
    public function purchases()
    {
        return $this->belongsToMany(Purchase::class)
        ->withPivot('quantity'); //中間テーブルの情報を取得するにはwithPivotのメソッドが必須
        //quantity(量)はpurchaseテーブルのカラム名
    }

}
