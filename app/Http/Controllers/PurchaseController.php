<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use Inertia\Inertia;
use App\Models\Customer;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;



class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // modelでデータを取得したもの(subtotal)などをgroupbyしてつなげる
        $orders = Order::groupBy('id')
        ->selectRaw('id, sum(subtotal) as total, customer_name, status, created_at')
        ->paginate(50);

        return Inertia::render('Purchases/Index',[
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $customers = Customer::select('id', 'name', 'kana')->get();
        $items = Item::select('id','name','price')
        ->where('is_selling', true)->get(); //販売中のステータスのものだけを取得する

        return Inertia::render('Purchases/Create',[
            // 'customers' => $customers,
            'items' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePurchaseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseRequest $request)
    {
        DB::beginTransaction(); //トランザクション
        try{ 
            //purchaseテーブルに登録
            $purchase = Purchase::create([
            'customer_id' => $request->customer_id,
            'status' => $request->status
        ]);

        //中間テーブルに登録
        foreach($request->items as $item){
            $purchase->items()->attach( $purchase->id, [ //attach：中間レーブルに情報を渡すことが出来る
                'item_id' => $item['id'],
                'quantity' => $item['quantity']
            ]);
        }
        DB::commit();
        return to_route('dashboard');

        }
        catch(\Exception $e){
            DB::rollback();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {
        //商品ごとの小計（複数あるかも）
        $items = Order::where('id', $purchase->id)->get();

        //合計取得（1件の身の取得）
        $order = Order::groupBy('id')
        ->where('id', $purchase->id) //詳細一件のみ取得（引数を使用して）
        ->selectRaw('id,sum(subtotal) as total, customer_name, status, created_at')
        ->get();
        // dd($items,$order);

        return Inertia::render('Purchases/Show',[
            'items' => $items,
            'order' => $order
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        //中間テーブルの情報を取得
        $purchase = Purchase::find($purchase->id); //引数を使用して取得
        
        //全ての商品情報を取得
        $allItems = Item::select('id', 'name', 'price')
        ->get();

        $items = [];

        foreach($allItems as $allItem){
            $quantity = 0; //quantity:数量
            foreach($purchase->items as $item){ //中間テーブルの情報を1件づつ回す
                if($allItem->id === $item->id){
                    $quantity = $item->pivot->quantity;
                }
            }
            array_push($items, [
                'id' => $allItem->id,
                'name' => $allItem->name,
                'price' => $allItem->price,
                'quantity' => $quantity,
            ]);
        }
       
        // dd($items);
        $order = Order::groupBy('id')
        ->where('id', $purchase->id)
        ->selectRaw('id, customer_id, 
        customer_name, status, created_at')
        ->get();

        return Inertia::render('Purchases/Edit', [
            'items' => $items,
            'order' => $order
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePurchaseRequest  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        try{
         // purchasesに登録(statusのカラムのみ登録)
        $purchase->status = $request->status;
        $purchase->save();

        //中間テーブルの登録
        //中間テーブルの情報を更新するにはsync(シンク)が便利
        //引数に配列が必要なので事前に作成しておく

        $items = [];
        foreach($request->items as $item){ //foreachで$itemsの配列に代入する
            $items = $items + [ //足していく
                $item['id'] => [ //$item['id']:商品のidを一つずつ取得することができる
                    'quantity' => $item['quantity'] //数量を取得
                ]
            ];
        }

        // dd($items);
        $purchase->items()->sync($items); //sync($items):$itemsの部分が引数となり、複数の中間テーブルの情報を更新する

        DB::commit();
        return to_route('purchases.index');
    } catch(\Exception $e){
        DB::rollback();
    }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
