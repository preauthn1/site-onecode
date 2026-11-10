<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('提取')" @click-left="router.go(-1)"
            @click-right="router.push('/eWallet')">
            <template #right>
                <van-icon name="todo-list-o" size="20" />
            </template>
        </van-nav-bar>
        <div class="wallet-top-borderview mt-[12px] ml-[13px] mr-[13px] mb-[30px]">
            <div class="bg-[#0C0914] rounded-[5px]">
                <div class="ml-[9px] mr-[9px] pt-[15px] pb-[15px] flex justify-between">
                    <span class="text-[15px] font-semibold">{{ $t("账户余额") }}</span>
                    <span class="text-[14px] font-semibold text-amount_color">{{ store?.userInfo?.user_info?.user_money
                        }}
                        {{ $sysCurrency }}</span>
                </div>
            </div>
        </div>
        <div class="ml-[19px] mt-[10px] text-[15px]">
            {{ $t("银行卡") }}
        </div>
        <div class="rounded-[5px]">
            <div class="pt-[8px] ml-[13px] mr-[13px]">
                <div class="back-crad-content flex flex-col">

                    <span class="text-[17px] font-semibold pt-[18px] ml-[10px]">{{ store.userInfo?.user_info?.bank_name
                        }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-[20px] font-medium ml-[10px] mt-[20px]" v-show="!showName">{{
                            store.userInfo?.user_info?.bank_number ?
                                (store.userInfo?.user_info?.bank_number).slice(0,4):"****"}} **** ****
                            {{ store.userInfo?.user_info?.bank_number ?
                                (store.userInfo?.user_info?.bank_number).slice(-4) :
                            "****" }}</span>
                        <span class="text-[20px] font-medium ml-[10px] mt-[20px]" v-show="showName">{{
                            (store.userInfo?.user_info?.bank_number) }}</span>
                        <van-icon @click="showName = !showName" class="mt-[20px]"
                            :name="showName ? 'eye-o' : 'closed-eye'" size="16" />
                    </div>

                    <span class="text-[13px] font-medium ml-[10px] mt-[24px] mb-[16px]">{{ $t("持卡人") }}：{{
                        store.userInfo?.user_info?.true_name }}</span>

                </div>
            </div>
        </div>
        <div class="ml-[19px] mr-[19px] mt-[10px] mb-[30px]">
            <!--  <div>
                <span class="text-[15px]">{{$t("提现")}}</span>
                <van-field v-model="params.withdraw_address" type="texts" :placeholder="$t('请输入或长按粘贴提币地址')" class="mt-[10px]" />
            </div> -->
            <div>
                <span class="text-[15px]">{{ $t("提款金额") }}</span>
                <van-field v-model="params.money" type="number" @input="onInput" :placeholder="$t('请输入转账金额')" class="mt-[10px]">
                    <template #button>
                        <van-button size="mini" type="default" color="#15131D"
                            @click="params.money = Number(store?.userInfo?.user_info?.user_money)">
                            <span class="text-amount_color">{{ $t("全部") }}</span>
                        </van-button>
                    </template>
                </van-field>
            </div>

            <div class="flex items-center mt-[5px] mb-[12px]">
                <van-icon name="warning-o" size="19" />
                <div class="flex flex-col">
                    <span class="text-[10px] text-left ml-[10px]">
                        {{ $t("最低提款金额") }}：{{ tabList?.platforms[active]?.min_withdraw_money }} {{ $sysCurrency }}
                    </span>
                    <span class="text-[10px] text-left ml-[10px]">
                        {{ $t("最高提款金额") }}：{{ tabList?.platforms[active]?.max_withdraw_money }} {{ $sysCurrency }}
                    </span>
                </div>
            </div>
            <div>
                <span class="text-[15px]">{{ $t("安全密码") }}</span>
                <van-field v-model="params.password" type="password" :placeholder="$t('请输入安全密码')" class="mt-[10px]" />
            </div>
            <div class="mt-[10px] flex justify-between">
                <div>
                    <span class="text-[13px] text-[#89898A]">{{ $t("手续费率") }}：</span>
                </div>
                <div class="flex flex-col text-right">
                    <span class="text-[13px] text-[#89898A]">{{ (Number(tabList?.withdraw_rate) * 100) }}%</span>
                </div>
            </div>
            <div class="mt-[10px] flex justify-between">

                <div>
                    <span class="text-[13px] text-[#89898A]">{{ $t("手续费") }}：</span>
                </div>
                <div class="flex flex-col text-right">
                    <span class="text-[13px] text-[#89898A]">{{ premium }} {{ $sysCurrency }}</span>
                    <span class="text-amount_color text-[13px]">{{ $t('免费次数')
                        }}{{ tabList?.free_withdraw_fee_num }}</span>
                </div>
            </div>
            <van-button round block type="primary" class="mt-[30px]" @click="Confirm">
                <span class=" font-bold">{{ $t("确认") }}</span>
            </van-button>
            <div class="mt-[10px]">
                <van-icon name="warning-o" size="19" />
                <span class="font-semibold ml-[10px]">{{ $t("温馨提醒") }}</span>
            </div>
            <div class="pt-[15px]">
                <div v-html="rule?.content"></div>
            </div>
            <!-- <div class="flex flex-col">
                <span class="text-[10px] mt-[10px]">1、为了维持平台稳定运行，每次提现收取1U固定费用作为维护费用</span>
                <span class="text-[10px] mt-[10px]">2、每次提款将根据提款金额收取不同的税费。费用如下10-100U收8%稅 100-500U收5%稅</span>
                <span class="text-[10px] mt-[10px]">3、提现将经过多个节点确认审核，到账时间为系统时间T+5天</span>
            </div> -->
        </div>

        <van-popup v-model:show="showCenter" :close-on-click-overlay="false" round>
            <div class="bg-[#0B0913]" style="max-width: 324px;">
                <div class="flex flex-col items-center ml-[24px] mr-[24px]">
                    <span class="mt-[24px] text-[16px] font-semibold">{{ $t('温馨提示') }}</span>
                    <span class="mt-[16px] text-[14px] font-medium text-center">{{ $t('您当前还没有绑定银行卡，请前往绑定!') }}</span>
                    <van-button round block type="primary" class="mt-[32px] mb-[24px]" @click="jumpbank">
                        <span class=" font-bold">{{ $t("确认") }}</span>
                    </van-button>
                </div>
            </div>
        </van-popup>
        <van-popup v-model:show="showToase"  round>
            <div class="bg-[#0B0913]" style="max-width: 324px;">
                <div class="flex flex-col items-center ml-[24px] mr-[24px]">
                    <span class="mt-[24px] text-[16px] font-semibold">{{ $t('温馨提示') }}</span>
                    <span class="mt-[16px] text-[14px] font-medium text-center">{{ messagecontent }}</span>
                    <van-button round block type="primary" class="mt-[32px] mb-[24px]" @click="showToase = false">
                        <span class=" font-bold">{{ $t("确认") }}</span>
                    </van-button>
                </div>
            </div>
        </van-popup>
    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { ref, reactive, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { useUserStore } from "@/store/userinfo";
import { showToast } from "vant";
import { useI18n } from 'vue-i18n';
import { number } from "echarts";

const { t } = useI18n();
const store = useUserStore();
const router = useRouter()
const tabList = ref()
const active = ref(0)
const rule = ref()
const showName = ref(false)
const showCenter = ref(false);
const showToase = ref(false)
const messagecontent = ref("")
/* const showkyc_status = ref(false) */
const params = reactive({
    money: "",
    password: "",
})
const getWidType = async () => {
    const res = await AuthService.withdrawals({})
    tabList.value = res
}

const premium = computed(() => {
    return Number((params.money * tabList.value?.withdraw_rate)).toFixed(2)
})
const Confirm = async () => {
    if (!params.money) {
        return showToast(t("请输入提现金额"))
    } else {
        if (Number(params.money) < Number(tabList.value?.platforms[active.value]?.min_withdraw_money)) {
            return showToast(`${t("最低提现金额")}${tabList.value?.platforms[active.value]?.min_withdraw_money}`)
        }
        if (Number(params.money) > Number(tabList.value?.platforms[active.value]?.max_withdraw_money)) {

            return showToast(`${t("最高提现金额")}${tabList.value?.platforms[active.value]?.max_withdraw_money}`)
        }
    }
    if (!params.password) {
        return showToast(t("请输入安全密码"))
    }

    const res = await AuthService.applicationwithdrawals({ ...params, "platform_id": tabList.value?.platforms[active.value]?.id })
    if(res.code == -1){
        messagecontent.value = res.msg;
        showToase.value = true;
        return
    }
    
    await store.getUserInfo()
    params.money = ""
    params.password = ""
    showToast(t("申请成功"))
    getWidType();
}
const jumpbank = () => {
    // router.push("/bankcard")
    router.push({ path: '/bankcard', query: { cate: 1 } })
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
      params.money = value;
    }

onMounted(async () => {
    await store.getUserInfo()
    getWidType()

    if (!store.userInfo?.user_info?.bank_name) {
        showCenter.value = true
    }

    const res = await AuthService.article_details({ type: "12" });
    rule.value = res


})
</script>

<style scoped>
.wallet-top-borderview {
    background: #15131D;
    border-radius: 5px;
    border: 2px solid #0937AD;
}

::v-deep .van-tabs__line {
    height: 3px;
    background: #fff;
}

.title_active {
    color: #0937AD !important;
}

.back-crad-content {
    width: 100%;
    height: auto;
    background: url("@/assets/images/icon_back_card.png") no-repeat center;
    background-size: 100% 100%;
}
</style>