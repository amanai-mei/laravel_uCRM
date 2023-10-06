<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFMService
{
    public static function rfm($subQuery,$rfmPrms)
    {
         //RFM分析
        // 1.購買id毎にまとめる
        $subQuery = $subQuery->groupBy('id')
                    ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase, created_at');

        // 2.会員毎にまとめて最終購入日、回数、合計金額を取得
        $subQuery = DB::table($subQuery)
                    ->groupBy('customer_id') //顧客毎にまとめる
                    //datediff:日付の差分、maxで日付の最新日
                    ->selectRaw('customer_id,customer_name,max(created_at) as recentDate, datediff(now(), max(created_at)) as recency, count(customer_id) as frequency,sum(totalPerPurchase) as monetary');
        // 3.RFMランクを選定、定義する（考える）

        // 4.会員毎のRFMランクを計算
        // r:最新購入日　f:累計購入回数　m:累計購入金額　で1〜5のランクを決める

        //配列を使って?の部分に値を入れる(第二引数で)
        // $rfmPrms = [14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000];
        $subQuery = DB::table($subQuery)
                    ->selectRaw('customer_id, customer_name, recentDate, recency, frequency, monetary,
                    case
                        when recency < ? then 5
                        when recency < ? then 4
                        when recency < ? then 3
                        when recency < ? then 2
                        else 1 end as r,
                    case
                        when ? <= frequency then 5
                        when ? <= frequency then 4
                        when ? <= frequency then 3
                        when ? <= frequency then 2
                        else 1 end as f,
                    case
                        when ? <= monetary then 5
                        when ? <= monetary then 4
                        when ? <= monetary then 3
                        when ? <= monetary then 2
                        else 1 end as m',$rfmPrms);

                        Log::debug($subQuery->get());
        // 5.ランク毎の数を計算する
        $totals = DB::table($subQuery)->count(); //合計の件数

        $rCount = DB::table($subQuery)
                    ->rightJoin('ranks', 'ranks.rank', '=', 'r')
                    ->groupBy('rank')
                    ->selectRaw('rank as r,count(r)')
                    ->orderBy('r','desc')->pluck('count(r)');//pluck('count(r)'):人数だけの情報が取れる

                    // Log::debug($rCount);

        $fCount = DB::table($subQuery)
                    ->rightJoin('ranks', 'ranks.rank', '=', 'f')
                    ->groupBy('rank')
                    ->selectRaw('rank as f,count(f)')
                    ->orderBy('f','desc')->pluck('count(f)');

        $mCount = DB::table($subQuery)
                    ->rightJoin('ranks', 'ranks.rank', '=', 'm')
                    ->groupBy('rank')
                    ->selectRaw('rank as m,count(m)')
                    ->orderBy('m','desc')->pluck('count(m)');

        // 6.RとFで2次元で表示
        //contat:文字列結合 文字列結合することでr_1、r_2などと表示できる
        $data = DB::table($subQuery)
                ->rightJoin('ranks', 'ranks.rank', '=', 'r')
                ->groupBy('rank')
                ->selectRaw('concat("r_", rank) as rRank,
                count(case when f = 5 then 1 end) as f_5,
                count(case when f = 4 then 1 end) as f_4,
                count(case when f = 3 then 1 end) as f_3,
                count(case when f = 2 then 1 end) as f_2,
                count(case when f = 1 then 1 end) as f_1')
                ->orderBy('rRank','desc')->get();

        $eachCount = []; //vue側で渡す用の空の配列
        $rank = 5; //初期値の設定

        for($i=0; $i<5; $i++)
        {
            array_push($eachCount,[ //r,f,mそれぞれの人数を足していく
                'rank' => $rank,
                'r' => $rCount[$i],
                'f' => $fCount[$i],
                'm' => $mCount[$i],
            ]);
            $rank--; //rankを1ずつ減らす
        }
        return [$data, $totals, $eachCount];
    }
}

?>