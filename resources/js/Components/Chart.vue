<script setup>
import { Chart, registerables } from "chart.js";
import { BarChart } from "vue-chart-3";
import { reactive, computed } from "vue";
import dayjs from 'dayjs';

const props = defineProps({ //Analysis.vueからの値の取得
    "data" : Object
})

// computed:変更があり次第描画
const labels = computed(() => props.data.labels)
const totals = computed(() =>props.data.totals)

Chart.register(...registerables);

const barData = reactive({
    labels: labels,
    datasets: [
        {
            label: '売上',
            data: totals,
            backgroundColor: "rgb(75, 192, 192)",
            tension: 0.1,
        }
    ]
})
</script>
<template>
    <!-- v-show="props.data" → props.dataがあったら表示 -->
    <div v-show="props.data">
       <BarChart :chartData="barData"/>
    </div>
</template>