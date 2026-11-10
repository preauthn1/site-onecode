<template>
    <div class=" pb-[60px]">
        <van-nav-bar fixed left-arrow @click-left="router.go(-1)"
            :style="{ 'background-color': 'transparent!important' }" />
        <video controls playsinline autoplay :src="detail?.video?.video_url" @play="onPlay" @pause="onPause"
            @ended="onEnded" class="w-full"></video>
        <div class=" pt-[20px] px-[16px]">
            <div class=" text-[16px]">{{ detail?.video?.name }}</div>
            <div class=" mt-[8px] mb-[30px]">
                <van-button round block type="primary" @click="router.push('/honouredGuest')" v-if="!score">
                    {{ $t("评分获得收入") }}
                </van-button>
                <div class="flex justify-between pt-3 items-center" v-else>
                    <div class="flex gap-1 items-center">
                        <van-rate v-model="value" :size="15" :icon="$getImageUrl('home/appraiseA.png')" color="#ffd21e"
                            count="5" :void-icon="$getImageUrl('home/appraise.png')" @change="onChange"
                            :disabled="scored||countdownShow" />
                        <!-- <span>{{ value }}</span> -->
                    </div>
                    <span>score</span>
                </div>
                <div v-if="countdownShow&&score" class=" mt-[8px] text-[10px]">{{ $t("观看") }} {{ timer }}s {{ $t("后可以参与评分") }}</div>

            </div>
            <div class="mb-[20px]">
                <span class=" text-[#959595]">{{ $t("类型") }}：</span>{{ videoType[detail?.video?.cate] }}
            </div>
            <div class="mb-[20px]">
                <span class=" text-[#959595]">{{ $t("导演") }}：</span>{{ detail?.video?.director }}
            </div>
            <div class="mb-[20px] describe">
                <span class=" text-[#959595]">{{ $t("描述") }}：</span>{{ detail?.video?.description }}
            </div>

            <div class="text-[#0937AD] text-[16px] mb-[10px]">{{ $t("相关推荐") }}</div>
            <div class="flex items-stretch flex-wrap gap-[10px]">
                <div v-for="item in recommend_video_list" class=" relative movie_item rounded-[5px] overflow-hidden"
                    @click="reload(item?.id)">
                    <img :src="item?.pic" alt="">
                    <div
                        class=" absolute top-0 right-[4px] px-[6px] py-[2px] text-[10px] bg-[rgba(0,0,0,0.34)] rounded-[5px]">
                        {{ item?.duration_time }}</div>
                </div>
            </div>

        </div>

    </div>
</template>

<script setup>
import { onMounted, ref ,onUnmounted} from "vue";
import { useRoute, useRouter } from "vue-router";
import { AuthService } from '@/api/home';
import { usevipStore } from "../store/vip";
import { showToast } from "vant";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const router = useRouter()
const route = useRoute()
const store = usevipStore()
const detail = ref()
const value = ref(0)
const recommend_video_list = ref([])
const videoType = ["", t("预告片"), t("音乐"), t("商品广告")]
const score = ref(false)
const scored = ref(false)
const getdetail = async (id) => {
    const res = await AuthService.video_detail({ "id": id })
    detail.value = res
}

const getrecommend_video_list = async (id) => {
    const res = await AuthService.recommend_video_list({ "id": id })
    recommend_video_list.value = res
}
const onChange = async (value) => {
    console.log(store.choose_level_id)
    const res = await AuthService.addVideoTask({ 'level_id': store?.choose_level_id, 'video_id': route.query?.id })
    scored.value = true;
    showToast(t("任务完成"))
}

/* 点击推荐视频时加载当前页面 */
const reload = (id) => {
    if (score.value) {
        window.location.replace(`#/movieDetails?id=${id}&is_score=true`);
    } else {
        window.location.replace(`#/movieDetails?id=${id}`);
    }

    location.reload();
}

const countdownShow = ref(true)
const timer = ref(20)
let countdownInterval = null;

const startCountdown = () => {
    if (!countdownInterval) {
        countdownInterval = setInterval(() => {
            if (timer.value > 0) {
                timer.value--;
            } else {
                clearInterval(countdownInterval);
                countdownShow.value = false; // 隐藏倒计时
            }
        }, 1000);
    }
};

// 暂停倒计时
const pauseCountdown = () => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
};

const onPlay = () => {
    if (!countdownShow) return
    startCountdown()
}

const onPause = () => {
    if (!countdownShow) return
    pauseCountdown();
};

const onEnded = () => {
    pauseCountdown();
    countdownShow.value = false;
};

onMounted(() => {
    if(!route.query.id){
        router.push("/")
    }
    if (route.query?.is_score) {
        score.value = true
    }
    getdetail(route.query.id)
    getrecommend_video_list(route.query.id)
})

onUnmounted(() => {
    pauseCountdown();
});

</script>

<style scoped>
.describe {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 6;
    overflow: hidden;
    text-overflow: ellipsis;
}

.movie_item {
    width: calc((100% - 2rem)/3);
    min-height: 141px;
}

.movie_item>img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.van-rate--disabled{
    filter: grayscale(100%);
}
</style>