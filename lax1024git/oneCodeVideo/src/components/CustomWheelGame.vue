<template>
    <div class=" px-[16px] max-w-full overflow-hidden">
        <div @click="colse" class="flex justify-end mr-[20px] cursor-pointer">
            <van-icon name="close" size="24" />
        </div>
        <div class="relative flex items-center justify-center py-[40px]">
            <div class=" absolute w-[375px] h-[375px]  aspect-square"></div>
            <!-- <img class=" absolute w-full h-full top-0 left-0  aspect-square" :src="$getImageUrl('icon_wheel_bg.png')" alt=""> -->
            <div class="" :class="{ 'wheel': spinning }" :style="{ transform: `rotate(${rotate}deg)` }">
                <img class=" w-[300px] h-[300px] object-cover" :src="$getImageUrl('icon_wheel_pan.png')" alt="">
                <div class="sector absolute flex flex-col items-center" style="top: 10%;"
                    v-for="(prize, index) in turntableInfo.turntable_prize"
                    :style="{ transform: `rotate(${index * (360 / turntableInfo.turntable_prize.length) + 22.5}deg)` }">
                    <span class="text-[15px] font-bold">
                        <span v-if="prize.type === 0">{{ prize.value }} {{ $sysCurrency }}</span>
                        <span v-if="prize.type === 3">{{ $t("谢谢参与") }}</span>
                    </span>
                    <img v-if="prize.type === 0" :src="$getImageUrl('icon_wheel_coin.png')" alt=""
                        class="mt-[10px] w-[25] h-[23px]">
                </div>
            </div>
            <div class="absolute w-[66px] h-[100px] -translate-y-8 z-10" :class="spinning?' cursor-not-allowed':' cursor-pointer'" @click="spin()">
                <img :src="$getImageUrl('icon_wheel_point.png')">
            </div>

            <div class="circular-box" v-for="index in 20" :key="index"
                :style="`transform: rotate(${360 / 20 * index}deg);`">
                <div class="circular"></div>
            </div>

        </div>
        <div class="mt-[20px] text-center">
            <span class="text-[15px] font-bold">{{ $t("可用抽奖次數") }}：{{turntableInfo.turntable_count?.count||0}}</span>
        </div>
        <div class="mt-[20px]">
            <van-button round block type="primary" @click="spin()">
                <span class=" font-bold">{{ $t("抽奖") }}</span>
            </van-button>
        </div>
        <router-link to="/invitation" class=" block mt-[16px] text-center text-[14px]">
            {{ $t("立即邀请") }}>>
        </router-link>
        
    </div>
</template>

<script setup>
import { onMounted, ref,getCurrentInstance } from 'vue';
import { showToast } from 'vant';
import { TurntableService } from '@/api/turntable'
import { useI18n } from 'vue-i18n';
const { proxy } = getCurrentInstance();
const { t } = useI18n();
const emit = defineEmits(["close"]);

const turntableInfo = ref({ turntable_prize: [] })

const spinning = ref(false);
const rotate = ref(0);
// 基础旋转圈数
const runNum = 10;

const spin = async () => {
    if(turntableInfo.value.turntable_count.count==0){
      return  showToast(t("暂无抽奖次数"));
    }
    if (spinning.value) return; // 防止重复点击
    spinning.value = true;
    turntableInfo.value.turntable_count.count--
    const response = await TurntableService.onTurntable({ id: turntableInfo.value.turntable?.id },()=>{spinning.value = false})
    const deg = (360 / turntableInfo.value.turntable_prize.length) * turntableInfo.value.turntable_prize.findIndex((item) => item.id == response.id) + (360 / turntableInfo.value.turntable_prize.length/2);
    rotate.value = runNum * 360 + (rotate.value - (rotate.value % 360)) - deg;
    // 等动画结束后显示结果
    setTimeout(() => {
        showToast(`${t("恭喜中奖")}：${proxy.$sysCurrency.value} ${response.value}`);
        spinning.value = false;
    }, 5000); // 动画时间为3秒
}

function colse() {
    emit("close");
}

const fetchInfo = async () => {
    const response = await TurntableService.getTurntable()
    turntableInfo.value = response
}

onMounted(() => {
    fetchInfo()
})
</script>

<style lang="less" scoped>
.aspect-square {
    background-image: url(@/assets/images/icon_wheel_bg.png);
    background-size: 120% auto;
    background-position: center;
    animation: 20s t-border infinite linear;
    pointer-events: none;
}

@keyframes t-border {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(-360deg);
    }
}


.sector {
    width: 100%;
    height: 40%;
    transform-origin: 50% 100%;
    // display: flex;
    // justify-content: center;
    // align-items: flex-start;
}

.wheel {
    transform-origin: center;
    transition: transform 5s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.circular-box {
    position: absolute;

    &:nth-child(2n-1) {
        .circular {
            animation-delay: 0.5s;
        }
    }
}

.circular {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #e5e27f;
    box-shadow: 0 0 2px #e5e27f, 0 4px 8px rgba(201, 223, 122, 0.6);
    transform: translateY(-142px);
    animation: circular 1s infinite;
}

@keyframes circular {
    0% {
        background: #e5e27f;
        box-shadow: 0 0 2px #e5e27f, 0 4px 8px rgba(201, 223, 122, 0.6);

    }

    100% {
        background: #FFF;
        box-shadow: 0 0 2px #FFF inset, 0 4px 8px rgba(240, 234, 234, 0.6);
    }
}
</style>