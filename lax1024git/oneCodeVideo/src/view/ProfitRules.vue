<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('收益规则')" @click-left="router.go(-1)" />
        <div class="mt-[12px] ml-[13px] mr-[13px] mb-[30px]">
            <div class="bg-[#15131D] rounded-[5px]">
                <div class="pt-[15px] ml-[14px] mr-[14px] pb-[20px] flex flex-col text-start">
                    <span class="text-[14px] font-semibold">{{detail?.title}}</span>
                    <span v-html="detail?.content"></span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
const router = useRouter()
const detail = ref();
onMounted(async()=>{
    const res = await AuthService.article_details({type:"4"});
    detail.value = res
    
})
</script>

<style scoped>

</style>