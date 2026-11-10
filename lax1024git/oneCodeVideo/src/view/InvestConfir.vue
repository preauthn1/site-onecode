<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('投资确认')" @click-left="router.go(-1)">
        </van-nav-bar>
        <div class="mt-[20px] ml-[16px] mr-[16px]">
            <span class="text-[15px]">{{ $t("投资金额") }}</span>
            <van-field v-model="money" type="number" :placeholder="$t('请输入投资金额')" disabled class="mt-[10px]">
                <template #button>
                    <van-button size="mini" type="default" color="#15131D">
                        <span class="text-[#89898A]"> {{$sysCurrency}}</span>
                    </van-button>
                </template>
            </van-field>
            <div class="text-[#959595] text-[13px]"> {{ $t("可用余额") }}：{{Number(store.userInfo?.user_info?.user_money).toFixed(2)}} {{$sysCurrency}}</div>
            <div class="mt-[24px]">
                <span class="text-[15px]">{{ $t("投资项目清单") }}</span>
            </div>
            <div class="mt-[13px] bg-[#15131D] rounded-[5px]">
                <!-- <div class="pt-[17px] ml-[12px]">
                    <span class="text-[#959595] text-[13px] font-medium">文案：这是一个理财</span>
                </div>
                <div class="h-[1px] mt-[30px] ml-[10px] mr-[10px] bg-[#FFFFFF24]"></div> -->
                <div class="pt-[25px] ml-[12px]">
                    <span class="text-[#959595] text-[13px] font-medium"> {{ $t("日回报率") }}：{{content?.profit}}%</span>
                </div>
                <div class="h-[1px] mt-[21px] ml-[10px] mr-[10px] bg-[#FFFFFF24]"></div>
                <div class="pt-[25px] ml-[12px]">
                    <span class="text-[#959595] text-[13px] font-medium">{{$t("投资期间")}}：{{content?.cycle}}{{ $t("天") }}</span>
                </div>
                <div class="h-[1px] mt-[21px] ml-[10px] mr-[10px] bg-[#FFFFFF24]"></div>
                <div class="pt-[25px] ml-[12px]">
                    <span class="text-[#959595] text-[13px] font-medium">{{$t("每日利息")}}：</span>
                    <span class="text-amount_color text-[13px] font-medium">{{money * content?.profit / 100}} {{$sysCurrency}}</span>
                </div>
                <div class="h-[1px] mt-[19px] ml-[10px] mr-[10px] bg-[#FFFFFF24]"></div>
                <div class="pt-[22px] ml-[12px]">
                    <span class="text-[#959595] text-[13px] font-medium">{{$t("到期本金")}}：</span>
                    <span class="text-amount_color text-[13px] font-medium">{{Number(content?.profit_money).toFixed(2)}} {{$sysCurrency}}</span>
                </div>
                <div class="mt-[50px]">
                    <van-button round block type="primary" @click="buyPeoject(content?.id)">
                        <span class=" font-bold">{{ $t("确认") }}</span>
                    </van-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { ref, reactive, onMounted } from "vue";
import { useRouter,useRoute } from "vue-router";
import { useUserStore } from '../store/userinfo';
import { AuthService } from "../api/home";
 
const store = useUserStore()
const router = useRouter()
const route = useRoute()
const content = ref()
const money = ref()
const buyPeoject =async(id) => {
    const res = await AuthService.buy({id});
    console.log("投资成功")
}
onMounted(()=>{
    content.value = JSON.parse(route.query.item)
    money.value = content.value.money;
})
</script>

<style scoped>


</style>