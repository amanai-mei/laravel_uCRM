<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function index(){

        //期間指定
        $startDate = '2023-08-20';
        $endDate = '2023-08-21';

        //RFM分析
        // 1.購買id毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
                    ->groupBy('id')
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
        $rfmPrms = [14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000];
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

// dd($subQuery->get());
        // 5.ランク毎の数を計算する
        $total = DB::table($subQuery)->count(); //合計の件数

        $rCount = DB::table($subQuery)
                    ->rightJoin('ranks', 'ranks.rank', '=', 'r') //ranksテーブルとjoinすることによって値がないものがnull(0)で返され、エラーが出なくなる
                    ->groupBy('rank')
                    ->selectRaw('rank as r,count(r)')
                    ->orderBy('r','desc')->pluck('count(r)');//pluck('count(r)'):人数だけの情報が取れる

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

        return Inertia::render('Analysis');
    }
    public function decile()
    {
        //期間指定
        $startDate = '2022-08-01';
        $endDate = '2023-09-30';

        //デジル分析
        // 1.購買ID毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
                    ->groupBy('id') //購買id毎に魔まとめる
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

    }
    public function rfm()
    {
        //期間指定
        $startDate = '2022-08-01';
        $endDate = '2023-09-30';

        //RFM分析
        // 1.購買id毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
                    ->groupBy('id')
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
        $rfmPrms = [14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000];
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

        // 5.ランク毎の数を計算する
        $total = DB::table($subQuery)->count(); //合計の件数

        $rCount = DB::table($subQuery)
                    ->groupBy('r')
                    ->selectRaw('r,count(r)')
                    ->orderBy('r','desc')->pluck('count(r)');//pluck('count(r)'):人数だけの情報が取れる
              
        $fCount = DB::table($subQuery)
                    ->groupBy('f')
                    ->selectRaw('f,count(f)')
                    ->orderBy('f','desc')->pluck('count(f)');
        $mCount = DB::table($subQuery)
                    ->groupBy('m')
                    ->selectRaw('m,count(m)')
                    ->orderBy('m','desc')->pluck('count(m)');

        // 6.RとFで2次元で表示
        //contat:文字列結合 文字列結合することでr_1、r_2などと表示できる
        $data = DB::table($subQuery)
                ->groupBy('r')
                ->selectRaw('concat("r_", r) as rRank,
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

    }
}
