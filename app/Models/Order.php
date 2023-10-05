<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\Subtotal;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected static function booted()
    {
        //グローバルスコープ
        //4つのテーブルをjoinし小計を出してる
        static::addGlobalScope(new Subtotal);
    }

    //データ分析(Analysis) ローカルスコープ
    // startDate = null, $endDate = nullは空の場合でデータ分析を行うことがあるため初期値をnullにする
    public function scopeBetweenDate($query, $startDate = null, $endDate = null)
    {
        //両方がnullだったら
        if(is_null($startDate) && is_null($endDate))
        {
            return $query;
        }

        //開始日がnullじゃなくて終了日がnullだったら
        if(!is_null($startDate) && is_null($endDate))
        {
            return $query->where('created_at', ">=", $startDate);
        }

        //開始日がnullで終了日がnullだったら
        if(is_null($startDate) && !is_null($endDate))
        {
            $endDate1 = Carbon::parse($endDate)->addDays(1); //addDays(1):日付を1日足す
            return $query->where('created_at','<=', $endDate1);
        }

        //開始日も終了日もnullじゃなかったら
        if(!is_null($startDate) && !is_null($endDate))
        {
            $endDate1 = Carbon::parse($endDate)->addDays(1); //addDays(1):日付を1日足す
            return $query->where('created_at', ">=", $startDate)
            ->where('created_at', '<=', $endDate1);
        }
    }
}
