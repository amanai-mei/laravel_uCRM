<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $tel = str_replace('-','',$this->faker->phoneNumber); //ハイフンを消す
        $address = mb_substr($this->faker->address, 9); //最初の郵便番号を消す

        return [
            'name' => $this->faker->name,
            'kana' => $this->faker->KanaName,
            'tel' => $tel,
            'email' => $this->faker->email,
            'postcode' => $this->faker->postcode,
            'address' => $address,
            'birthday' => $this->faker->dateTime,
            'gender' => $this->faker->numberBetween(0,2),
            'memo' => $this->faker->realText(50),
        ];
    }
}
