<script setup>
import { onMounted, ref } from 'vue';

//親→子の場合
defineProps(['modelValue']);

//子→親の場合（イベントが実行したタイミングで情報を親のコンポーネントに渡す
defineEmits(['update:modelValue']); //イベントの名前（update:modelValue）


const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <!-- $emit('update:modelValue', $event.target.value)"
 -->
     <!-- @enimt('カスタムイベント名',引数にしたい値) -->
    <input
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
    />
</template>
