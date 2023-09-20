<script setup>
// @/はresourcesのjsフォルダからスタート出来るのでそれ以降のパスを書く
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Label from '@/Components/InputLabel.vue'
import Input from '@/Components/TextInput.vue'

const emitTest = e => console.log(e)
// eの部分は引数
</script>

<template>
<GuestLayout>
    <Label value="件名">タイトル</Label>
    <Input
     modelValue="初期値"
     @update:modelValue="emitTest"
     ></Input>
</GuestLayout>

</template>