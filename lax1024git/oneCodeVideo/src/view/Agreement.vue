<template>
    <div  class="mt-[50.5px]">
        <van-nav-bar fixed left-arrow :title="content?.title" @click-left="router.go(-1)" />
        <div class="p-[15px] rick_content">
            <div v-html="content?.content" class="content"></div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { AuthService } from '@/api/home';
import { onMounted,ref} from 'vue';
const router = useRouter();
const content = ref()
onMounted(async()=>{
    const res = await AuthService.article_details({type:"5"})
    content.value = res
})
</script>

<style lang="less" scoped>
.content{
    text-wrap: wrap !important;
}

</style>