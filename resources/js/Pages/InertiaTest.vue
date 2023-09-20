<script setup>
// setup→compositionAPIを使用する時はscriptタグにsetupを記載するだけでOK
import { Link } from '@inertiajs/inertia-vue3';
import{ ref } from 'vue'; // refをインポート

// 空の変数を作る
const newTitle = ref('')
const newContent = ref('')
</script>

<template>
Inertiaテストです。<br>
<a href="/">aタグ</a><br>
<Link href="/">Link経由</Link><br>
<Link :href="route('inertia.index')">名前付きルート</Link><br>
<!-- :hrefはv-bindを使用する（使用することで設定した値を動的に変えることができる -->
<Link :href="route('inertia.show', {id: 1 })">ルートパラメータ</Link>

<div class="mb-8"></div>

<input type="text" name="newTitle" v-model="newTitle">{{ newTitle }}<br>
<input type="text" name="newContent" v-model="newContent">{{ newContent }}<br>
<!-- v-modelを使用すると連動して入力した値を変数に入れる（上のコードの場合だとnewContentに入力した値が代入される -->
<Link as="button" method="post" :href="route('inertia.store')"
:data="{
    // controller側にはtitleに入力した値が代入される
    title: newTitle,
    content: newContent
}">保存テスト</Link>
<!-- as→ボタンとして使用する / :data→controllerにデータを渡したい時使用する -->
</template>
