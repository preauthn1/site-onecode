<template>
    <div class=" px-[15px]">
        <van-nav-bar fixed left-arrow :title="$t('公告详情')"  @click-left="router.go(-1)" />
        <div class=" pt-[72px]">
            <div class="notice_rich" v-html="noticeDetail"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import {useRoute, useRouter } from "vue-router";
const router = useRouter()
const route = useRoute()
const noticeDetail = ref("")

onMounted(()=>{
    noticeDetail.value = JSON.parse(route.query.content).content
})
</script>

<style scoped>
.notice_rich > img{
    max-width: 100%;
    height: auto;
    object-fit: cover;
}
</style>