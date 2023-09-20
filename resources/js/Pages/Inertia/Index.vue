<script setup>
import { Link } from '@inertiajs/inertia-vue3';
defineProps({
    blogs:Array //型指定（blogsは何で値の取得をするか）
})
</script>

<template>
    <div v-if="$page.props.flash.message" class="bg-blue-300">
    {{ $page.props.flash.message }}
    </div>
    <ul>
        <li v-for="blog in blogs" :key="blog.id">
            <!-- v-for→for文で表示 単数形 in 複数形 -->
            <!-- ソートや削除などで順番が変わっても状態を保持するのでfor文使ったら:keyは必須 -->
            件名：<Link class="text-blue-400" :href="route('inertia.show', {id: blog.id })">{{ blog.title }}</Link>
            本文：{{ blog.content }}
        </li>
    </ul>
</template>