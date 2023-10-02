<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\models\Customer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $decade = $this->faker->dateTimeThisDecade; //dateTimeThisDecade:過去10年分
        $created_at = $decade->modify('+2 years'); //modify('+2 years')で過去8年分
        return [
            // rand：1からcustomerテーブルに登録してる分のダミーデータを作成してくれる
            'customer_id' => rand(1,Customer::count()),
            'status' => $this->faker->boolean,
            'created_at' => $created_at
        ];
    }
}
