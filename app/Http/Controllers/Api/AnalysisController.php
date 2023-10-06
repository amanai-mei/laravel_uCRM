<?php
    //Api用のコントローラーを作成
    //route/api.phpにも記載が必要

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\AnalysisService;
use App\Services\DecileService;


class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        //日別で取得
        // 1 購買id毎の売上をまとめ、dateをフォーマットした状態のサブクエリを作る
        // 2 サブクエリをgroupByで日毎にまとめる
        $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if($request->type === 'perDay') //Analysisの方で定義してるタイプだったら
        {
            //配列を受け取ってそれぞれの変数に格納するためlist()を使用する
            list($data, $labels, $totals) = AnalysisService::perDay($subQuery);
            
            // //->where('status', true):キャンセルしてないものだけ
            // //->groupBy('id'):購買id毎の売上をまとめる
            // $subQuery->where('status',true)->groupBy('id')
            // // SUM(subtotal) as totalPerPurchase:購買毎の合計
            // ->selectRaw('SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date')->groupBy('date');

            // $data = DB::table($subQuery) //この書き方だと上のクエリにプラスで更にgroupByなどが出来るようになる
            // ->groupBy('date') //日毎にまとめる
            // ->selectRaw('date, sum(totalPerPurchase) as total') //日毎の合計を出す
            // ->get();

            // //日付の情報を取得
            // $labels = $data->pluck('date');
            // //totalの情報を取得
            // $totals = $data->pluck('total');

        }
        if($request->type === 'perMonth') //Analysisの方で定義してるタイプだったら
        {
            //配列を受け取ってそれぞれの変数に格納するためlist()を使用する
            list($data, $labels, $totals) = AnalysisService::perMonth($subQuery);
        }

        if($request->type === 'perYear') //Analysisの方で定義してるタイプだったら
        {
            //配列を受け取ってそれぞれの変数に格納するためlist()を使用する
            list($data, $labels, $totals) = AnalysisService::perYear($subQuery);
        }

        if($request->type === 'decile') //Analysisの方で定義してるタイプだったら
        {
            //配列を受け取ってそれぞれの変数に格納するためlist()を使用する
            list($data, $labels, $totals) = DecileService::decile($subQuery);
        }

        // Ajaxなのでjson形式で返す必要がある(連想配列で必要な情報を渡す)
        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals
        ], Response::HTTP_OK); //Response::HTTP_OKの定数を使用する
    }
}
