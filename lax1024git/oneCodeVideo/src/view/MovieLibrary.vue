<template>
    <div class=" px-[16px] pb-[60px] ">
        <van-nav-bar fixed left-arrow :title="$t(pageTitle)" @click-left="router.go(-1)" />
        <div class=" pt-[74px]">
            <van-list v-model:loading="loading" :finished="finished" :loading-text="$t('加载中')" :finished-text="$t('没有更多了')" @load="onLoad" >
               <div class=" flex items-stretch flex-wrap gap-[10px]" >
                <div v-for="item in list" class=" relative movie_item rounded-[5px] overflow-hidden" @click="jumpDetail(item.id)">
                    <img :src="item?.pic" alt="">
                    <div class=" absolute top-0 right-[4px] px-[6px] py-[2px] text-[10px] bg-[rgba(0,0,0,0.34)] rounded-[5px]">{{item?.duration_time}}</div>
                </div>
               </div>
            </van-list>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import {useRoute, useRouter } from "vue-router";
import { AuthService } from "@/api/home";
const router = useRouter()
const route = useRoute()

const pageTitle = ref("预告片")

const list = ref([]);
const loading = ref(false);
const finished = ref(false);
const pageInfo = reactive({
    cate:1,
    page:1,
    limit:10,
})
const onLoad = async() => {

    const res = await AuthService.video_list({...pageInfo})
     // 加载状态结束
     loading.value = false;
    // 数据全部加载完成
    if (res.length < 10) {
        list.value = [...list.value,...res]
        finished.value = true;
    }else{
        pageInfo.page++;
        list.value = [...list.value,...res]
    }
};
const jumpDetail = (id) => {
    router.push(`movieDetails?id=${id}`)
}
onMounted(()=>{
    if(!route.query.cate){
        pageInfo.cate = 1
    }
    if(route.query.cate==2){
        pageInfo.cate = 2
        pageTitle.value = "音乐"
    }
    if(route.query.cate==3){
        pageInfo.cate = 3
        pageTitle.value ="商业广告"
    }
})

</script>

<style scoped>
.movie_item{
    width: calc((100% - 2rem)/3);
    min-height: 141px;
}

.movie_item>img{
    width: 100%;
    height: 100%;
    object-fit: contain;
}
</style>