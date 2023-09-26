<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, reactive, ref,computed } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { getToday } from '@/common'
import MicroModal from '@/Components/MicroModal.vue'

// import BreezeValidationErrors  from '@/Components/ValidationErrors.vue'

const props = defineProps({
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

const setCustomerId = id => { //idがMicroModalコンポーネントのemitで記載した値を取得してる部分
    form.customer_id = id //formのcustomer_idの部分にMicroModalコンポーネントで取得したidの値を代入し登録
}

</script>

<template>
    <Head title="購入画面" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">購入画面</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- <BreezeValidationErrors :errors="errors"/> -->
                        <section class="text-gray-600 body-font relative">
                            <form @submit.prevent="storePurchase">
                                <div class="container px-5 py-8 mx-auto">
                                    <div class="lg:w-1/2 md:w-2/3 mx-auto">
                                        <div class="flex flex-wrap -m-2">
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="date" class="leading-7 text-sm text-gray-1000">日付</label>
                                                    <input type="date" id="date" name="date" v-model="form.date" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <!-- <div v-if="errors.name">{{ errors.date }}</div> -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="customer" class="leading-7 text-sm text-gray-1000">会員名</label>
                                                    <!-- @update:customerId="setCustomerId"で引数を持ってくる -->
                                                    <MicroModal @update:customerId="setCustomerId"/>
                                                    <!-- <div v-if="errors.name">{{ errors.date }}</div> -->
                                                </div>
                                            </div>
                                            <div class="mt-8 w-full mx-auto overflow-auto">
                                    <table class="table-auto w-full text-left whitespace-no-wrap">
                                        <thead>
                                        <tr>
                                            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">Id</th>
                                            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">商品名</th>
                                            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">価格</th>
                                            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">数量</th>
                                            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">小計</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="item in itemList" :key="item.id">
                                            <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.id }}</td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.name }}</td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.price }}</td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3">
                                                <select name="quantity" v-model="item.quantity">
                                                    <option v-for="q in quantity" :value="q">{{ q }}</option>
                                                </select>
                                            </td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.price * item.quantity }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                        <div class="p-2 w-full">
                                            <div class="">
                                                <label for="price" class="leading-7 text-sm text-gray-1000">合計金額</label>
                                                <div class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    合計： {{ totalPrice }} 円 <br>
                                                </div>
                                                <!-- <div v-if="errors.price">{{ errors.price }}</div> -->
                                            </div>
                                        </div>
                                        <div class="p-2 w-full">
                                            <button class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
