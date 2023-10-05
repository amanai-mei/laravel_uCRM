<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function index(){

        //帰還指定
        $startDate = '2022-08-01';
        $endDate = '2022-08-31';

        // Order::betweenDateはOrder.phpのbetweenDateのメソッドを使用してる(ローカルスコープ)
        // $period = Order::betweenDate($startDate, $endDate)
        //         ->groupBy('id')
        //         ->selectRaw('id, sum(subtotal) as total, customer_name, status, created_at')
        //         ->orderBy('created_at')
        //         ->paginate(50);

        //日別で取得
        // 1 購買id毎の売上をまとめ、dateをフォーマットした状態のサブクエリを作る
        // 2 サブクエリをgroupByで日毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
                //->where('status', true):キャンセルしてないものだけ
                //->groupBy('id'):購買id毎の売上をまとめる
                ->where('status', true)->groupBy('id')
                // SUM(subtotal) as totalPerPurchase:購買毎の合計
                ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');

        $data = DB::table($subQuery) //この書き方だと上のクエリにプラスで更にgroupByなどが出来るようになる
                ->groupBy('date') //日毎にまとめる
                ->selectRaw('date, sum(totalPerPurchase)as total') //日毎の合計を出す
                ->get();
                // dd($data);

        return Inertia::render('Analysis');
    }
}
