<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('签到')" @click-left="router.go(-1)"></van-nav-bar>
        <div class="ml-[18px] mr-[18px]">
            <img :src="$getImageUrl('icon_daily_sing.png')" alt="">
        </div>
        <div class="grid grid-cols-3 gap-3 mt-[10px] pb-[10px] ml-[18px] mr-[18px]">
            <div class="flex flex-col" v-for="(item, index) in signData" :key="index">
                <div class="sign-back-borderview" :class="{'sign-back-signed':(item?.status == 1 || item?.status == 2),'sign-back-nosign':item.status==0}">
                    <div class="flex flex-col justify-around items-center bg-[#15131D] rounded-[4px]" :class="{'sign-background-linear':item?.status==1}">
                        <span class="text-[#89898A] text-[13px] mt-[5px]" :class="{'sign-text-ablesign':item?.status==1}">{{ $t("第几天",{day:item?.day}) }}</span>
                        <img :src="$getImageUrl('icon_sign_coin.png')" class="w-[26px] h-[23px] mt-[5px] mb-[8px]">
                        <span class="text-[#6EEB54] text-[14px] font-semibold mb-[10px]">{{item?.award}} {{$sysCurrency}}</span>
                    </div>
                </div>
                <div @click="toSignAction(item)" class="bg-[#15131D] rounded-[5px] ml-[8px] mr-[8px] mt-[10px] text-center flex items-center justify-center" :class="{'sign-button-signed':item?.status==2,'sign-button-ablesign':item?.status==1}">
                    <span class="text-[#89898A] text-[12px] pt-[3px] pb-[3px]" :class="{'sign-text-ablesign':item.status == 1}">{{ $t(signstatus[item?.status]) }}</span>
                </div>
            </div>
        </div>
        <div class="p-[15px]">
            <div v-html="rule?.content"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { showToast } from "vant";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const router = useRouter()
const rule = ref()
//signType 0:待签到 1:可签到 2:已签到

const signData = ref([]);

const signstatus = ref(["待签到","签到","已签到"])
const  toSignAction = async(item) => {
    if(item.status != 1)return;
    const res = await AuthService.sign({"sign_id":item.sign_id})
    showToast(t("签到成功"))
    const resList = await AuthService.signList()
    signData.value = resList
}
onMounted(async ()=>{
    const res = await AuthService.signList()
    signData.value = res
    const res01 = await AuthService.article_details({type:"9"})
    rule.value = res01
})
</script>

<style scoped>
.sign-back-borderview {
    border-radius: 6px;
    padding: 2px;
}
.sign-back-signed {
    background: linear-gradient(0deg, #75F8D2, #6096DB, #2AE3FC); /* 渐变边框 */
}
.sign-back-nosign {
    background:#15131D;
}
.sign-background-linear {
    background: linear-gradient(45deg, #75F8D2, #6096DB, #2AE3FC);
  }
.sign-text-ablesign {
    color: white;
}
.sign-button-signed {
    background: #0937AD;
}
.sign-button-ablesign {
    background: #6EEB54;
}
</style>