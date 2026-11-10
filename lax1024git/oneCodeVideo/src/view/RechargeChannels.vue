<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('储值选择')" @click-left="router.go(-1)" />
        <div v-for="(item, index) in depositType" :key="index" @click="jumpdetail(item)" class="ml-[16px] mr-[16px] bg-[#15131D] rounded-[5px] mt-[16px]">
            <div class="flex justify-between">
                <van-image class="mt-[10px] mb-[10px] ml-[10px]" width="32" height="32" :src="item.image" round fit="cover"/>
                <div class="flex justify-between flex-1 items-center ml-[10px] mr-[10px]">
                    <span class="text-[13px]">{{item.name}}</span>
                    <van-icon name="arrow"/>
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
const depositType = ref([])
const depositMethod = async () => {
    const res = await AuthService.recharge();
    depositType.value = res
}
const jumpdetail = (item) => {
    router.push(`/rechargedetail?content=${encodeURIComponent(JSON.stringify(item))}`)
}
onMounted(()=>{
    depositMethod()
})
</script>

<style scoped>

</style>