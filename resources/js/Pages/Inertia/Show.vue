<script setup>
import{ Inertia } from '@inertiajs/inertia';

// definePropsでcontrollerからの値を受け取る
defineProps({
    id : String,
    blog: Object
})
// (id)で引数を持ってこれる
const deleteConfirm = (id) => {
    Inertia.delete(`/inertia/${id}`, {
        onBefore: () => confirm('本当に削除しますか')
    })
}

</script>

<template>
    {{ id }}<br>
    {{ blog.title }}<br>
    <button @click="deleteConfirm(blog.id)">削除</button>    
</template>
