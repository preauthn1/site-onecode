<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('银行卡')" @click-left="toBack()" />
        <div class="ml-[16px] mr-[16px]" v-if="!store.userInfo?.user_info?.bank_name">
            <span class="text-[15px] font-semibold">{{ $t("已绑定的银行卡") }}</span>
            <div class="mt-[14px] rounded-[5px] bg-[#15131D]">
                <div class="ml-[10px] mr-[10px] pt-[47px] pb-[73px]">
                    <van-button @click="router.push('/editbankcard')" color="#75FA59" block type="default" icon="plus">
                        <span class="text-[15px] font-bold">{{ $t("添加银行卡") }}</span>
                    </van-button>
                </div>
            </div>
        </div>
        <div class="ml-[16px] mr-[16px]" v-else>
            <span class="text-[15px] font-semibold">{{ $t("已绑定的银行卡") }}</span>
            <div class="mt-[14px] rounded-[5px] bg-[#15131D]">
                <div class="pt-[18px] ml-[13px] mr-[13px]">
                    <div class="back-crad-content flex flex-col">
                        <span class="text-[17px] font-semibold pt-[18px] ml-[10px]">{{store.userInfo?.user_info?.bank_name}}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-[20px] font-medium ml-[10px] mt-[20px]" v-show="!showName">{{(store.userInfo?.user_info?.bank_number).slice(0,4)}} **** **** {{(store.userInfo?.user_info?.bank_number).slice(-4)}}</span>
                            <span class="text-[20px] font-medium ml-[10px] mt-[20px]" v-show="showName">{{(store.userInfo?.user_info?.bank_number)}}</span>
                            <van-icon @click="showName = !showName" class="mt-[20px]" :name="showName?'eye-o':'closed-eye'" size="16"/>
                        </div>
                        
                        <span class="text-[13px] font-medium ml-[10px] mt-[24px] mb-[16px]">{{$t("持卡人")}}：{{store.userInfo?.user_info?.true_name}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useUserStore } from '../store/userinfo';
const store = useUserStore();
const router = useRouter()
const showName = ref(false)
const isHasCard = ref(false);
const route = useRoute()
function toBack() {
    if(route.query.cate == 1){
        router.go(-2);
    } else {
        router.go(-1);
    }
}
</script>

<style  scoped>
    
    .back-crad-content {
        width: 100%;
        height: auto;
        background: url("@/assets/images/icon_back_card.png") no-repeat center;
        background-size: 100% 100%;
    }
</style>