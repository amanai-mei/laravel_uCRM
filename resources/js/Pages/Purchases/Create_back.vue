<script setup>
import { getToday } from '@/common'
import { onMounted, reactive, ref,computed } from 'vue'
import{ Inertia } from '@inertiajs/inertia';


const props = defineProps({
    'customers' : Array,
    'items' : Array //値の取得
})

onMounted(() => { //ページ読み込み後、即座に実行
    form.date = getToday()
    props.items.forEach( item => { //配列を1つずつ処理(forEach)　itemが引数となり、これが1件ずつの情報になる
        itemList.value.push({ //itemListの配列に1つずつ追加(push)
            //4つの情報を持たせる配列を作る
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: 0 //販売中のitemをv-forで全て表示するが初期値は0にする
        })
    })
})


//propsのままだと値の変更ができないので新たに配列を作って追加する
const itemList = ref([]) //リアクティブな配列を準備

const form = reactive ({
    date: null,
    customer_id: null,
    status: true,
    items: []
    })


    //totalの計算
    // computed:変更があり次第再計算してくれるもので使う際はreturnが必須
    const totalPrice = computed(() =>{
        let total = 0 // 初期値0にする
        itemList.value.forEach( item =>{ //配列を1つずつ処理
            total+= item.price * item.quantity // totalに足していく
        })
        return total //最終的な値
    })

    //登録処理
    const storePurchase = () => {
        itemList.value.forEach( item => {
            if( item.quantity > 0){
                form.items.push({
                    id: item.id,
                    quantity: item.quantity
                })
            }
        })
        Inertia.post(route('purchases.store'),form)
    }

    const quantity = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"] //option用

</script>

<template>
    <form @submit.prevent="storePurchase">
    日付<br>
    <input type="date" name="date" v-model="form.date"><br>
    会員名<br>
    <select name="customer" v-model="form.customer_id">
        <option v-for="customer in customers" :value="customer.id" :key="customer.id">
            {{ customer.id }} ： {{ customer.name }}
        </option>
    </select>
    <br><br>
    商品サービス<br>
    <table>
        <thead>
            <tr>
                <th>Id</th>
                <th>商品名</th>
                <th>金額</th>
                <th>数量</th>
                <th>小計</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in itemList">
                <td>{{ item.id }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.price }}</td>
                <td>
                    <select name="quantity" v-model="item.quantity">
                        <option v-for="q in quantity" :value="q">{{ q }}</option>
                    </select>
                </td>
                <td>
                    {{ item.price * item.quantity }}
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    合計： {{ totalPrice }} 円 <br>
    <button>登録する</button>
</form>
</template>