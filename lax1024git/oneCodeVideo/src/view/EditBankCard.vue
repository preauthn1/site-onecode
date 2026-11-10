<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('绑定银行卡')" @click-left="router.go(-1)" />
        <div class="ml-[16px] mr-[16px]">
            <span class="text-[15px] font-semibold">{{ $t("选择银行卡类型") }}</span>
            <div class="mt-[16px]">
                <span class="text-[13px] font-medium">{{ $t("银行卡名称") }}</span>
                <div @click="show=true" class="bg-[#15131D] mt-[10px] rounded-[5px] flex justify-between h-[50px] items-center">
                    <span class="text-[#89898A] text-[13px] ml-[12px]" :class="{'selector-text-white':typeData.filter(item => item?.id ==typeSelected )[0]?.name}">{{ typeData.filter(item => item?.id ==typeSelected )[0]?.name ? typeData.filter(item => item?.id ==typeSelected )[0]?.name : $t('请选择') }}</span>
                    <van-icon name="arrow" size="15" class="mr-[12px]"/>
                </div>
                <div class="mt-[20px] text-left">
                    <span class="text-[13px] font-medium">{{ $t("姓名") }}</span>
                    <van-field class="mt-[10px]" v-model="pageInfo.true_name" label="" :placeholder="$t('请输入您的真实姓名')" maxlength="50"></van-field>
                </div>
                <div class="mt-[20px] text-left">
                    <span class="text-[13px] font-medium">{{ $t("银行卡号") }}</span>
                    <van-field class="mt-[10px]" v-model="pageInfo.bank_number" label="" :placeholder="$t('请输入您的银行卡号')"/>
                </div>
            </div>

            <div class="mt-[40px] pb-[42px]">
                <van-button round block type="primary" @click="bankcardBtn">
                    <span class=" font-bold">{{ $t("确定") }}</span>
                </van-button>
            </div>
        </div>
        <van-popup v-model:show="show" class="flex justify-center" style="z-index: 3000; width: 100%; height: 60%;"
            position="bottom">
            <div class="wallet-type-backview w-full" style="max-width: var(--maxWidth);">
                <div class="wallet-type-backview flex justify-between fixed w-full" style="max-width: var(--maxWidth);">
                    <div @click="cancelSelectedType" class="mt-[24px] text-[13px] text-[#FFFFFF] w-[134px] text-center">{{ $t("取消") }}</div>
                    <span class="mb-[10px] pt-[49px] text-[15pxp] font-semibold">{{ $t("选择银行卡") }}</span>
                    <div @click="didSelectedType(typeString)" class="pt-[24px] text-[13px] text-white w-[134px] text-center">
                        {{ $t("确认") }}</div>
                </div>
                <div class="text-center mt-[90px]">
                    <div v-for="(item, index) in typeData" @click="changeType(item.id)" class=" pb-[10px]">
                        <span :class="{ 'wallet-type-linear': item.id == typeString }"
                            class="text-[14px] text-[#78787A]">{{ item.name }}</span>
                    </div>
                </div>
            </div>
        </van-popup>
        <van-popup v-model:show="showCenter"  round>
            <div class="bg-[#0B0913]" style="max-width: 324px;">
                <div class="flex flex-col items-center ml-[24px] mr-[24px]">
                    <span class="mt-[24px] text-[16px] font-semibold">{{ $t('温馨提示') }}</span>
                    <span class="mt-[16px] text-[14px] font-medium text-center">{{ messagecontent }}</span>
                    <van-button round block type="primary" class="mt-[32px] mb-[24px]" @click="showCenter = false">
                        <span class=" font-bold">{{ $t("确认") }}</span>
                    </van-button>
                </div>
            </div>
        </van-popup>

    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { showToast } from "vant";
import { useUserStore } from '../store/userinfo';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const showCenter = ref(false)
const messagecontent = ref("")
const router = useRouter()
const show = ref(false);
const store = useUserStore();
const typeString = ref(0);
const typeSelected = ref();
const pageInfo = reactive({
    true_name:"",
    bank_number:"",
    bank_id:""
})
function changeType(id) {
    typeString.value = id;
}
const didSelectedType = async (id) => {
    typeSelected.value = id;
    show.value = false;
}
function cancelSelectedType() {
    typeString.value = typeSelected.value;
    show.value = false;
}
const typeData = ref([]);
const bankcardBtn = async () => {
    if(!typeSelected.value){
        return showToast(t("请选择银行卡名称"))
    }
    if(!pageInfo.true_name){
        return showToast(t("请输入您的真实姓名"))
    }
    if(!pageInfo.bank_number){
        return showToast(t("请输入银行卡卡号"))
    }
    pageInfo.bank_id = typeData.value.filter(item => item.id== typeSelected.value)[0]?.id
    const res = await AuthService.bind_bank({...pageInfo})
    console.log(res)
    if(res.code == -1){
        messagecontent.value = res.msg;
        showCenter.value = true;
        return
    }
    showToast(t("绑定成功"))
    store.getUserInfo();
    router.go(-1)

}

onMounted(async()=>{
    const res = await AuthService.bank_list();
    console.log(res,"银行卡类型")
    typeData.value = res
})
</script>

<style  scoped>

.selector-text-gray {
    color: #89898A;
}
.selector-text-white {
    color: #FFFFFF;
}

.wallet-type-backview {
    background-color: #15131E;
    border-radius: 26px 26px 0 0;
}

.wallet-type-linear {
    font-weight: normal;
    font-size: 14px;
    color: #0937AD;
    /* background: linear-gradient(180deg, #FDC2D8 0%, #FCA65E 35.009765625%, #FF7983 64.990234375%, #041282 100%); */
    /* -webkit-background-clip: text;
    -webkit-text-fill-color: transparent; */
}
</style>