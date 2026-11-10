<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('帮助中心')" @click-left="router.go(-1)" />
        <div v-if="!hasData" class=" pt-[132px] text-center">
            <img class=" w-[158px] mx-auto" :src="$getImageUrl('empty_img.png')" alt="">
            <div class=" mt-[62px] text-[#959595]">{{ $t("暂无数据") }}</div>
        </div>
        <van-list v-model:loading="loading" :finished="finished" :loading-text="$t('加载中')" :finished-text="$t('没有更多了')" @load="onLoad" v-else>
            <div  @click="toHelpDetails(item?.id)" v-for="(item, index) in list" :key="index"
                class="ml-[16px] mr-[16px] bg-[#15131D] rounded-[5px] mt-[16px]">
                <div class="flex justify-between ml-[12px] mr-[12px] pt-[20px] pb-[20px] items-center">
                    <span class="text-[14px] font-semibold">{{item?.title}}</span>
                    <van-icon name="arrow" />
                </div>
            </div>
        </van-list>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
const router = useRouter()
const hasData = ref(true);
const list = ref([])
const loading = ref(false)
const finished = ref(false)
const pageInfo = reactive({
    page: 1,
    limit: 10,
})
const toHelpDetails = (id) => {
    router.push(`helpdetail?id=${id}`)
}
const onLoad = async() =>{
    loading.value = true
    const res = await AuthService.article({...pageInfo,type: 2 })
    list.value = [...list.value,...res.data]
    if(list.value.length < res.total){
        return pageInfo.page++
    }
    finished.value = true
}

</script>