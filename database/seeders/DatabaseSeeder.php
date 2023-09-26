<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Purchase;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ItemSeeder::class,
        ]);
        \App\Models\Customer::factory(1000)->create();

        $items = \App\Models\Item::all(); //itemsテーブルの全件取得

        //purchaseを登録時に中間テーブルにも同時に登録する(1件の購入時に1〜3件の商品情報を登録とする)
        //purchase_idのカラムは自動的に登録されるのでそれ以外の処理を記載する
        Purchase::factory(100)->create()
        ->each(function(Purchase $purchase) use  ($items) { //eachは100件分のダミーデータを1件ずつ処理してくれる
            $purchase->items()->attach( //attachで中間テーブルに情報を登録してくれる
                //外部キー以外で中間テーブルに情報追加する場合は第二引数にかく
                //↓第一引数
                $items->random(rand(1,3))->pluck('id')->toArray(), //1〜3個のitemをpurchaseにランダムに紐付け
                //↓第二引数
                [ 'quantity' => rand(1,5) ] //数量を1〜5をランダムに紐付け
            );
        });



        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
