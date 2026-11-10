<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t(titleStr)" @click-left="router.go(-1)"></van-nav-bar>
        <div class="rules-top-borderview mt-[12px] ml-[13px] mr-[13px] mb-[30px]">
            <div class="bg-[#0C0914] rounded-[5px]">
                <div class="ml-[9px] mr-[9px] pt-[22px] pb-[22px] flex justify-between">
                    <span class="text-[15px] font-semibold">{{ isTransferOut?$t("可转出余额"):$t("可转入余额" )}}</span>
                    <span class="text-[14px] font-semibold text-amount_color">{{ isTransferOut?store.userInfo?.user_info?.balance_money:store.userInfo?.user_info?.user_money }}{{ $sysCurrency }}</span>
                </div>
            </div>
        </div>
        <div class="ml-[16px] mr-[16px]">
            <span class="text-[15px] font-semibold">{{ isTransferOut?$t("转出金额"):$t("转入金额") }}</span>
            <van-field v-model="amount" type="number" :placeholder="$t('请输入金额') + $sysCurrency.value" class="mt-[10px]" @input="onInput"></van-field>
            
        </div>
        <div class="mt-[100px] pb-[42px] ml-[16px] mr-[16px]">
            <van-button round block type="primary" @click="handleYue">
                <span class=" font-bold">{{ isTransferOut?$t("确认转出"):$t("确认转入") }}</span>
            </van-button>
        </div>
    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { onMounted,ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useUserStore } from "@/store/userinfo";
import { AuthService } from "@/api/home";
import { showToast } from "vant";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const router = useRouter()
const route = useRoute()
const amount = ref()
const store = useUserStore()
const titleStr = ref('');
const isTransferOut = ref(false);
const handleYue = async() => {
    if(!amount.value){
            return showToast(t("请输入金额"))
        }
    if(isTransferOut.value){
        const res = await AuthService.subBalance({money:amount.value})
        showToast(t("转出成功"))
    }else{
        const res = await AuthService.addBalance({money:amount.value})
        showToast(t("转入成功"))
        console.log(res,"这是余额转入")
    }
    await store.getUserInfo();
}
const onInput = (event) => {
      // 获取当前输入的值
      let value = event.target.value;
      // 使用正则表达式只保留数字和小数点，并限制小数点后两位
      value = value.replace(/[^0-9.]/g, ''); // 过滤掉非数字和非小数点的字符
      
      // 若出现多个小数点，保留第一个
      const parts = value.split('.'); 
      if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('').substring(0, 2);
      } else if (parts.length === 2) {
        // 限制小数点后最多两位
        value = parts[0] + '.' + parts[1].substring(0, 2);
      }
      // 更新金额值
      amount.value = value;
    }
onMounted(()=>{
    if(route.query.cate == 1){
        titleStr.value = "余额转出";
        isTransferOut.value = true;
    } else {
        titleStr.value = "余额转入";
        isTransferOut.value = false;
    }
})
</script>

<style scoped>
.rules-top-borderview {
    background: #0937AD; /* 渐变边框 */
    border-radius: 5px;
    padding: 2px;
}
</style>