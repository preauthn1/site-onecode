<template>
    <div class=" px-[15px]">
        <van-nav-bar fixed left-arrow :title="$t('通知')" @click-left="router.go(-1)" />
        <div class=" pt-[70px] mb-[18px]">
            <Tabs v-model="activeTab" :tabList="tabList" @tabChange="handleTabChange" />
        </div>
        <van-list v-model:loading="loading" :finished="finished" :loading-text="$t('加载中')" :finished-text="$t('没有更多了')" @load="onLoad">
            <div @click="toNoticeDetails(item)" v-for="(item, index) in noticeList" :key="index"
                class=" notice_items mb-[11px]">
                <div class=" text-[14px] mb-[12px]">{{ item?.title }}</div>
                <div class="notice_content " v-html="item?.content"></div>
                <div class=" mt-[15px] text-right text-[#959595]">{{ item?.create_time }}</div>
            </div>
        </van-list>

    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { useSystemStore } from "../store/system";
const store = useSystemStore();
const router = useRouter()
const loading = ref(false)
const finished = ref(false)
const tabList = ['公告', '讯息']
const activeTab = ref(0)
const noticeList = ref([])
const pageInfo = reactive({
    page:1,
    limit:10,
})
const handleTabChange = () =>{
    noticeList.value = []
    finished.value = false
    pageInfo.page = 1
    onLoad()
}
const onLoad = async (key) => {
    loading.value = true;
    store.notice = activeTab.value
    if (activeTab.value == 0) {
        const res = await AuthService.notice({...pageInfo});
        loading.value = false
        noticeList.value = [...noticeList.value,...res.data]
        if(noticeList.value.length < res.total){
            pageInfo.page++;
        }else{
            finished.value = true
        }
    } else {
        const res = await AuthService.message({...pageInfo});
        loading.value = false
        noticeList.value = [...noticeList.value,...res.data]
        if(noticeList.value.length < res.total){
            pageInfo.page++;
        }else{
            finished.value = true
        }
        
    }
}
const toNoticeDetails = (item) => {
    let strItem = JSON.stringify(item)
    const encodedParam = encodeURIComponent(strItem);
    router.push("noticeDetails?content=" + encodedParam)
}
onMounted(async () => {
    if (store.notice) {
        activeTab.value = store.notice
    }
    
})
</script>

<style scoped>
.notice_items {
    background: #15131D;
    padding: 16px 11px;
    border-radius: 5px;
}

.notice_content {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>