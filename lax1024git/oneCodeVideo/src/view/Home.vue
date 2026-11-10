<template>
    <div class="home">
        <GeneralNavBar></GeneralNavBar>
        <div class="mt-[5.6rem]">
            <!-- 顶部轮播 -->
            <van-swipe v-if="indexdata?.slide.length" :autoplay="3000" :show-indicators="false" lazy-render>
                <van-swipe-item v-for="(item, index) in indexdata?.slide" class="w-full h-[162px]" :key="index">
                    <van-image :src="item.pic" class="w-full h-full" alt="Image" fit="cover"/>
                </van-swipe-item>

            </van-swipe>
            <van-skeleton v-else>
                <template #template>
                    <div :style="{ display: 'flex', width: '100%' }">
                        <van-skeleton-image class="w-full h-[162px]" />
                    </div>
                </template>
            </van-skeleton>
            <!-- 通知 -->
            <van-notice-bar class="notice" :text="indexdata?.notice[0]?.content.replace(/<br\s*\/?>/gi, '')" @click="router.push('/notice')">
                <template #left-icon>
                    <van-image :src="$getImageUrl('notice.png')" class="w-[18px] h-[20px]"></van-image>
                </template>
            </van-notice-bar>
            <div class=" p-6 flex w-full justify-between items-center">
                <div class="flex flex-col items-center" @click="router.push('/rechargechannels')">
                    <van-image :src="$getImageUrl('home/recharge.png')" class="w-[30px] h-[36px]" />
                    <span class="text-center pt-2">{{ $t("充值") }}</span>
                </div>
                <div class="flex flex-col items-center" @click="router.push('/withdrawal')">
                    <van-image :src="$getImageUrl('home/extract.png')" class="w-[33px] h-[37px]" />
                    <span class="text-center pt-2">{{ $t("提取") }}</span>
                </div>
                <div class="flex flex-col items-center">
                    <van-image :src="$getImageUrl('home/vip.png')" @click="router.push('/honouredGuest')"
                        class="w-[44px] h-[34px]" />
                    <span class="text-center pt-2">{{ $t("贵宾") }}</span>
                </div>
                <div class="flex flex-col items-center">
                    <van-image :src="$getImageUrl('home/tutorial.png')" @click="router.push('/helpCenter')"
                        class="w-[42px] h-[35px]" />
                    <span class="text-center pt-3">{{ $t("帮助中心") }}</span>
                </div>
            </div>
            <div class="pt-5">
                <div class="pl-[16px] text-[15px] font-bold title">{{ $t("教程介绍") }}</div>
                <div class="p-[16px]">
                    <video controls playsinline :src="indexdata?.system?.index_video_url"
                        class="w-full max-h-[300px]"></video>
                </div>
            </div>
            <div class="pt-5">
                <div class="text-[15px] pl-[16px] font-bold title mb-[10px]">{{ $t("影片分类") }}</div>
                <div class="flex justify-between pl-[16px] pr-[16px] items-center">
                    <div class="movietype" @click=toMoviePage(1)>
                        <van-image :src="$getImageUrl('home/trailer.png')"
                            class="w-[42px] h-[52px] pos-absolute top-4 left-9 "></van-image>
                        <div class="w-full text-center pt-[10px]">{{ $t("预告片") }}</div>
                    </div>
                    <div class="movietype" @click=toMoviePage(2)>
                        <van-image :src="$getImageUrl('home/music.png')"
                            class="w-[47px] h-[40px] pos-absolute top-9 left-9 "></van-image>
                        <div class="w-full text-center pt-[20px]">{{ $t("音乐") }}</div>
                    </div>
                    <div class="movietype" @click=toMoviePage(3)>
                        <van-image :src="$getImageUrl('home/advertising.png')"
                            class="w-[55px] h-[42px] pos-absolute top-9 left-9 "></van-image>
                        <div class="w-full text-center pt-[20px]">{{ $t("商品广告") }}</div>
                    </div>
                </div>
            </div>

            <div class=" mt-[19px] px-[16px]">
                <div @click="toMoviePage(1)" class="text-[15px]  font-bold title mb-[10px]">{{ $t("预告片") }}</div>
                <div v-if="!pageLoading" @click="toMovieDetails(item.id)" v-for="item in indexdata?.video[1]"
                    class=" flex items-stretch mb-[12px] bg-[#15131E] rounded-[5px]">

                    <van-image :src="item.pic" contain class="w-[140px] h-[120px] shrink-0 object-contain"></van-image>
                    <div class="flex-col pt-[6px] px-[10px] text-[12px] leading-[14px]">
                        <div class="">{{ item.name }}</div>
                        <div class="mt-[8px] text-introduce_text notice_content">{{ item.description }}</div>
                        <div class="flex justify-between pt-3 items-center">
                            <van-rate v-model="value" :size="15" :icon="$getImageUrl('home/appraiseA.png')"
                                color="#ffd21e" count="5" :void-icon="$getImageUrl('home/appraise.png')" disabled />
                            
                        </div>
                        <div class="w-full flex justify-end mt-[12px]">
                            <van-button round class="appraise">{{ $t("立即点评") }}</van-button>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <van-skeleton v-for="item in 3" :key="item">
                        <template #template>
                            <div :style="{ display: 'flex', width: '100%' }">
                                <van-skeleton-image class=" w-[140px] h-[120px]" />
                                <div :style="{ flex: 1, marginLeft: '16px', paddingTop: '6px' }">
                                    <van-skeleton-paragraph row-width="60%" />
                                    <van-skeleton-paragraph />
                                    <van-skeleton-paragraph />
                                    <van-skeleton-paragraph />
                                </div>
                            </div>
                        </template>
                    </van-skeleton>
                </div>
            </div>
            <div class=" mt-[19px] px-[16px]">
                <div @click="toMoviePage(1)" class="text-[15px]  font-bold title mb-[10px]">{{ $t("音乐") }}</div>
                <div v-if="!pageLoading" @click="toMovieDetails(item.id)" v-for="item in indexdata?.video[2]"
                    class=" flex items-stretch mb-[12px] bg-[#15131E] rounded-[5px]">

                    <van-image :src="item.pic" contain class="w-[140px] h-[120px] shrink-0 object-contain"></van-image>

                    <div class="flex-col pt-[6px] px-[10px] w-full text-[12px] leading-[14px]">
                        <div class="notice_content " style="-webkit-line-clamp: 1;">{{ item.name }}</div>
                        <div class="mt-[8px] text-introduce_text notice_content">{{ item.description }}</div>
                        <div class="flex justify-between pt-3 items-center">
                            <van-rate v-model="value" :size="15" :icon="$getImageUrl('home/appraiseA.png')"
                                color="#ffd21e" count="5" :void-icon="$getImageUrl('home/appraise.png')" disabled />
                            
                        </div>
                        <div class="w-full flex justify-end mt-[12px]">
                            <van-button round class="appraise">{{ $t("立即点评") }}</van-button>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <van-skeleton v-for="item in 3" :key="item">
                        <template #template>
                            <div :style="{ display: 'flex', width: '100%' }">
                                <van-skeleton-image class=" w-[140px] h-[120px]" />
                                <div :style="{ flex: 1, marginLeft: '16px', paddingTop: '6px' }">
                                    <van-skeleton-paragraph row-width="60%" />
                                    <van-skeleton-paragraph />
                                    <van-skeleton-paragraph />
                                    <van-skeleton-paragraph />
                                </div>
                            </div>
                        </template>
                    </van-skeleton>
                </div>
            </div>
            <div class=" mt-[19px] px-[16px]">
                <div @click="toMoviePage(2)" class="text-[15px]  font-bold title mb-[10px]">{{ $t("商业广告") }}</div>

                <div v-if="!pageLoading" @click="toMovieDetails(item.id)" v-for="item in indexdata?.video[3]"
                    class=" flex items-stretch mb-[12px] bg-[#15131E] rounded-[5px]">

                    <van-image :src="item.pic" contain class="w-[140px] h-[120px] shrink-0 object-contain"></van-image>

                    <div class="flex-col pt-[6px] px-[10px] text-[12px] leading-[14px]">
                        <div class="notice_content " style="-webkit-line-clamp: 1;">{{ item.name }}</div>
                        <div class="mt-[8px] text-introduce_text notice_content">{{ item.description }}</div>
                        <div class="flex justify-between pt-3 items-center">
                            <van-rate v-model="value" :size="15" :icon="$getImageUrl('home/appraiseA.png')"
                                color="#ffd21e" count="5" :void-icon="$getImageUrl('home/appraise.png')" disabled />
                            
                        </div>
                        <div class="w-full flex justify-end mt-[12px]">
                            <van-button round class="appraise">{{ $t("立即点评") }}</van-button>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <van-skeleton v-for="item in 3" :key="item">
                        <template #template>
                            <div :style="{ display: 'flex', width: '100%' }">
                                <van-skeleton-image class=" w-[140px] h-[120px]" />
                                <div :style="{ flex: 1, marginLeft: '16px', paddingTop: '6px' }">
                                    <van-skeleton-paragraph row-width="60%" />
                                    <van-skeleton-paragraph />
                                    <van-skeleton-paragraph />
                                    <van-skeleton-paragraph />
                                </div>
                            </div>
                        </template>
                    </van-skeleton>
                </div>
            </div>
            <div class="px-[16px]">
                <div class="text-[15px]  font-bold text-title_color mb-[10px]">{{ $t("推荐奖励") }}</div>
                <div class="bg-[#15131E] rounded-[5px]">
                    <van-swipe :autoplay="3000" vertical :show-indicators="false" lazy-render class="h-[50px]">
                        <van-swipe-item v-for="(item, index) in recommend_list" class="w-full flex items-center h-[50px]" :key="index">
                            <div class="flex items-center">{{ $t("恭喜获得奖金",{name:item?.user_name,money:item?.earnings}) }}{{ $sysCurrency }}
                                <!-- <span class="text-amount_color">{{item.earnings}}</span>{{ $sysCurrency }} -->
                            </div>
                        </van-swipe-item>
                    </van-swipe>
                </div>
            </div>
        </div>

        <!-- 通告弹窗 -->
        <van-popup v-model:show="showNotice" style=" width: 100%; background-color: #FFFFFF00;">
            <div class=" w-[327px] h-[380px] p-[8px] pt-0 bg-[#15131E] mx-auto rounded-g">
                <div
                    class=" w-[150px] h-[36px] bg-[#0937AD] mx-auto flex items-center justify-center text-[16px] rounded-b-[5px]">
                    {{ $t("站内信") }}</div>
                <div class=" w-full h-[240px]">
                    <van-swipe ref="noticeSwipe">
                        <van-swipe-item v-for="(item,index) in noticeList" :key="index">
                            <div class=" h-[240px]">
                                <div class=" notice_wrap mt-[10px] h-[210px] overflow-y-auto overflow-x-hidden whitespace-pre-line" v-html="item.content"></div>

                            </div>
                        </van-swipe-item>
                     
                        <template #indicator="{ active, total }">
                            <div class=" mt-[16px] custom-indicator">{{ active + 1 }}/{{ total }}</div>
                        </template>
                    </van-swipe>
                </div>
                <div class=" mt-[28px] flex items-center justify-around">
                    <van-button round class="appraise_border !h-[32px] min-w-[108px]" @click="showNotice=false">{{ $t("关闭") }}</van-button>
                    <van-button round class="appraise !h-[32px] min-w-[108px]" @click="noticeNext">{{ $t("下一条") }}</van-button>
                </div>
            </div>

        </van-popup>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import GeneralNavBar from '@/components/GeneralNavBar.vue';
import { useRoute, useRouter } from "vue-router";
import { AuthService } from '@/api/home';
import { showToast } from 'vant';
import { useUserStore } from '../store/userinfo';

const store = useUserStore();
const router = useRouter()
const route = useRoute()
const pageLoading = ref(false)
const indexdata = ref()
const value = ref(5)

const userAgent = navigator.userAgent.toLowerCase();
const isMobile = /mobile/i.test(userAgent);  // 检查是否为移动设备
const isTablet = /tablet/i.test(userAgent);  // 检查是否为平板设备
const isDesktop = !isMobile && !isTablet;    // 如果不是移动设备或平板，就是桌面设备
if (isMobile) {
  console.log('来自移动设备');
  showToast("来自移动设备");
} else if (isTablet) {
  console.log('来自平板设备');
  showToast("来自平板设备");
} else if (isDesktop) {
  console.log('来自桌面设备');
  showToast("来自桌面设备");
}

  if (window.navigator.standalone) {
  console.log("Web Clip 打开的网页");
  showToast("作为 Web Clip 打开");
} else {
    const isWebClipMeta = document.querySelector('meta[name="apple-mobile-web-app-capable"][content="yes"]');
    if (/iPhone|iPad|iPod/.test(navigator.userAgent) && window.innerWidth === screen.width &&
    window.innerHeight === screen.height) {
    console.log("Web Clip1 打开的网页");
    showToast("作为 Web Clip1 打开");
  } else {
    console.log("浏览器打开的网页");
    showToast("通过浏览器打开");
  }
}

const toMovieDetails = (id) => {
    router.push(`movieDetails?id=${id}&is_score=true`)
}

const toMoviePage = (type) => {
    router.push({ name: "MovieLibrary", query: { cate: type } })
}
const getdata = async () => {
    pageLoading.value = true
    const res = await AuthService.index(() => { pageLoading.value = false });
    indexdata.value = res
    pageLoading.value = false
    if(res.index.new_pop_message_count > 0){
        fetchNoticeList()
        showNotice.value = true;
    }
}
const recommend_list = ref([])

const showNotice = ref(false)
const noticeList = ref([])
const noticeSwipe = ref(null)
const fetchNoticeList = async ()=>{
    const response = await AuthService.pop_list();
    noticeList.value = response
}
const recommend = async() => {
    const res = await AuthService.vip_list()
    recommend_list.value = res

}
const noticeNext = ()=>{
    if(noticeSwipe.value){
        noticeSwipe.value.next()
    }
}

onMounted(async () => {
    if(route.query.ic || route.query.pid || route.query.click_id){
        localStorage.setItem("popularizar",JSON.stringify({"ic":route.query.ic,"pid":route.query.pid,"click_id":route.query.click_id}))
    }
    getdata()
    recommend()
})



</script>
<style lang="less" scoped>
.notice_wrap > img{
    max-width: 100%;
    height: auto;
}
 .custom-indicator {
    position: absolute;
    right: 5px;
    bottom: 5px;
    padding: 2px 5px;
    font-size: 12px;
    background: rgba(0, 0, 0, 0.1);
  }
.home {
    height: 100%;
    padding-bottom: 80px;
}

.notice {
    background: #000;
    border: 1px solid;
    /* opacity: 0.3; */
    border-image: linear-gradient(180deg, #75F8D2, #6096DB, #2AE3FC) 1 1;
}

.movietype {
    position: relative;
    width: 90px;
    height: 89px;
    background: #1E1F3E;
    border-radius: 5px;
    border: 1px solid #0937AD;
    background-size: contain;
}

.item {
    background: #15131E;
    border-radius: 5px;
}

.appraise {
    background: #1555FA;
    border-radius: 6px;
    height: 24px;
    border: none;

}
.appraise_border{
    border-radius: 6px;
    height: 24px;
    background: transparent;
    border: 1px solid #1555FA;
}
.notice_content {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: break;
}

.sector {
    width: 100%;
    height: 40%;
    transform-origin: 50% 100%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.wheel {
    transform-origin: center;
    transition: transform 3s cubic-bezier(0.25, 0.1, 0.25, 1);
}

::-webkit-scrollbar {
    display: none;
}

</style>