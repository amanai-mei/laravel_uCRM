<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;

class DecileService
{
    public static function decile($subQuery)
    {
        //デジル分析
        // 1.購買ID毎にまとめる
        $subQuery = $subQuery->groupBy('id') //購買id毎に魔まとめる
                    ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPErPurchase'); //まとめたものの金額を合計する

        // 2.会員毎にまとめて購入金額順にソートする
        $subQuery = DB::table($subQuery)
                    ->groupBy('customer_id') //会員id毎にまとめる
                    ->selectRaw('customer_id, customer_name, sum(totalPerPurchase) as total')
                    ->orderBy('total', 'desc'); //金額の高い順にする

        // 3.購入順に連番を振る
        DB::statement('set @row_num = 0;'); //statementで変数の設定ができる
        // set @変数名 = 値(mysqlの書き方)
        $subQuery = DB::table($subQuery)
                    ->selectRaw('@row_num:= @row_num+1 as row_num, customer_id, customer_name, total');
                                //@row_num+1→プラス1したのをrow_numに入れていく
        
        //4.全体の件数を数え、1/10の値や合計金額を取得
        $count = DB::table($subQuery)->count(); //全体の件数を取得
        $total = DB::table($subQuery)->selectRaw('sum(total) as total')->get();
        $total = $total[0]->total; //構成比用

        //$countで全体の件数を取得した10分の1を$decileに代入 → (例)200件取得してたら20件
        $decile = ceil($count / 10); //10分の1の件数を変数に入れる
        // ceil:繰り上げ関数

        $bindValues = []; //配列になった件数(10件分) →同じ数字の場合は同じ件数複数で1件 
        $tempValue = 0;
        for($i=1; $i<=10; $i++)
        {
            array_push($bindValues, 1+$tempValue);
            $tempValue += $decile;
            array_push($bindValues, 1+$tempValue);
        }
        // dd($count, $decile, $bindValues);

        // 5.10分割しグループ毎に数字を振る
        // case:if文みたいなものでrow_num and row_numの部分が条件になり当てはまってたら1,2、、、となる
        DB::statement('set @row_num = 0;'); //ここでrow_numを0にしないと前回の続きからになるから注意
        $subQuery = DB::table($subQuery)
                    ->selectRaw("row_num, customer_id, customer_name, total,
                    case 
                        when ? <= row_num and row_num < ? then 1
                        when ? <= row_num and row_num < ? then 2
                        when ? <= row_num and row_num < ? then 3
                        when ? <= row_num and row_num < ? then 4
                        when ? <= row_num and row_num < ? then 5
                        when ? <= row_num and row_num < ? then 6
                        when ? <= row_num and row_num < ? then 7
                        when ? <= row_num and row_num < ? then 8
                        when ? <= row_num and row_num < ? then 9
                        when ? <= row_num and row_num < ? then 10
                    end as decile
                    ", $bindValues); //selectRaw第二引数にバインドしたい数値(配列)を入れる →第二引数を設定することにより上記コードの？部分に値が表示される
                    // decile:グループ割り振られた番号が入ってる

        // 6.グループ毎の合計・平均
        $subQuery = DB::table($subQuery)
                    ->groupBy('decile') //グループ毎にまとめる
                    // round:四捨五入,avg:平均
                    ->selectRaw('decile, round(avg(total)) as average, sum(total) as totalPerGroup');

        // 7.構成比
        DB::statement("set @total = ${total};"); //${total}:構成比用の$total
        $data = DB::table($subQuery)
                ->selectRaw('decile, average, totalPerGroup, round(100 * totalPerGroup / @total, 1) as totalRadio')->get();
                // totalPerGroup:グループ毎の合計を100倍して全ての合計値を割り算する

                $labels = $data->pluck('decile');
                $totals = $data->pluck('totalPerGroup');

                return[$data, $labels, $totals];

    }
}

?>