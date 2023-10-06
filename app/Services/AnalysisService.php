<?php
// controller上でコードの量が増えたからファットコントローラー防止のためサービスを切り離す
// 基本的にcontrollerはなるべくコード量が少ない方がいい

//フォルダの場所を書く
namespace App\Services;
use Illuminate\Support\Facades\DB;

class AnalysisService
{
    // staticをつけるとcontroller上に読み込む時にAnalysisService::perDayのようにclassクラス名:メソッド名の書き方で値の取得ができる
    public static function perDay($subQuery)
    {
        //->where('status', true):キャンセルしてないものだけ
        //->groupBy('id'):購買id毎の売上をまとめる
        $query = $subQuery->where('status',true)->groupBy('id')
            // SUM(subtotal) as totalPerPurchase:購買毎の合計
            ->selectRaw('SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date')->groupBy('date');

        $data = DB::table($query) //この書き方だと上のクエリにプラスで更にgroupByなどが出来るようになる
        ->groupBy('date') //日毎にまとめる
        ->selectRaw('date, sum(totalPerPurchase) as total') //日毎の合計を出す
        ->get();

        //日付の情報を取得
        $labels = $data->pluck('date');
        //totalの情報を取得
        $totals = $data->pluck('total');

        //複数の変数を渡すので一旦配列に入れる
        return [$data, $labels, $totals];
    }

    public static function perMonth($subQuery)
    {
        //->where('status', true):キャンセルしてないものだけ
        //->groupBy('id'):購買id毎の売上をまとめる
        $query = $subQuery->where('status',true)->groupBy('id')
            // SUM(subtotal) as totalPerPurchase:購買毎の合計
            ->selectRaw('SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m") as date')->groupBy('date');

        $data = DB::table($query) //この書き方だと上のクエリにプラスで更にgroupByなどが出来るようになる
        ->groupBy('date') //日毎にまとめる
        ->selectRaw('date, sum(totalPerPurchase) as total') //日毎の合計を出す
        ->get();

        //日付の情報を取得
        $labels = $data->pluck('date');
        //totalの情報を取得
        $totals = $data->pluck('total');

        //複数の変数を渡すので一旦配列に入れる
        return [$data, $labels, $totals];
    }

    public static function perYear($subQuery)
    {
        //->where('status', true):キャンセルしてないものだけ
        //->groupBy('id'):購買id毎の売上をまとめる
        $query = $subQuery->where('status',true)->groupBy('id')
            // SUM(subtotal) as totalPerPurchase:購買毎の合計
            ->selectRaw('SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y") as date')->groupBy('date');

        $data = DB::table($query) //この書き方だと上のクエリにプラスで更にgroupByなどが出来るようになる
        ->groupBy('date') //日毎にまとめる
        ->selectRaw('date, sum(totalPerPurchase) as total') //日毎の合計を出す
        ->get();

        //日付の情報を取得
        $labels = $data->pluck('date');
        //totalの情報を取得
        $totals = $data->pluck('total');

        //複数の変数を渡すので一旦配列に入れる
        return [$data, $labels, $totals];
    }
}

?>