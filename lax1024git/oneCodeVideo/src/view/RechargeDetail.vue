<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('充值')" @click-left="router.go(-1)"
            @click-right="router.push('/eWallet')">
            <template #right>
                <van-icon name="todo-list-o" size="20" />
            </template>
        </van-nav-bar>
        <div class="mt-[12px] ml-[13px] mr-[13px]" v-if="routecontent?.currency_type == 1">
            <div class="flex flex-col items-center bg-[#15131E] rounded-[5px]">
                <div class="flex items-center pt-[12px]">
                    <van-image width="32" height="32" :src="routecontent?.image" round fit="cover" />
                    <span class="pl-[10px] font-semibold">{{ routecontent?.name }}</span>
                </div>
                <div class="pt-[12px] flex items-center flex-wrap">
                    <span>{{ address?.recharge_address }}</span>
                    <van-icon :name="$getImageUrl('icon_copy_white.png')" @click="$copy(address.recharge_address,t)"
                        class="left-[7px]"></van-icon>
                </div>
                <div class="w-[90px] h-[90px] rounded-[5px] bg-[#FFFFFF]  mb-[24px] mt-[12px]">
                    <qcord :url="address?.recharge_address" class="p-[2px]"></qcord>
                </div>
            </div>
        </div>
        <van-cell-group inset v-else>
            <van-field v-model="amount" type="number" :placeholder="$t('请输入充值金额')" />
        </van-cell-group>

        <div class=" mt-[32px] mx-[15px]">
            <van-button round block type="primary" @click="router.push('/')" v-if="routecontent?.currency_type == 1">
                <span class="font-bold">{{ $t("储值完成") }}</span>
            </van-button>
            <van-button round block type="primary" @click="rechargeBtn" v-else>
                <span class="font-bold">{{ $t("立即充值") }}</span>
            </van-button>
        </div>
        <div class="flex flex-col mt-[24px] ml-[13px] mr-[13px]">
            <span>{{ $t("换算比例") }}</span>
            <span class="text-[10px] mt-[10px]">1 {{routecontent?.unit}} = {{ routecontent?.exchange_rate }} {{ $sysCurrency }}</span>
            <span class="text-[10px] mt-[10px]">10 {{routecontent?.unit}} = {{ routecontent?.exchange_rate * 10 }} {{ $sysCurrency }}</span>
            <span class="text-[10px] mt-[10px]">100 {{routecontent?.unit}} = {{ routecontent?.exchange_rate * 100 }} {{ $sysCurrency }}</span>
        </div>
        <div class="mt-[24px] ml-[13px] mr-[13px]">
            <div class="flex items-center">
                <van-icon name="warning-o" size="19" />
                <span class="font-semibold ml-[10px]">{{ $t("温馨提醒") }}</span>
            </div>
            <div class="pt-[15px]">
                <div v-html="rule?.content"></div>
            </div>

        </div>
    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { ref, reactive, onMounted } from "vue";
import qcord from "@/components/qcord.vue";
import { useRoute, useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { showToast } from "vant";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const router = useRouter()
const route = useRoute()
const routecontent = ref()
const address = ref()
const amount = ref();
const rule = ref();
const getdetail = async (id) => {
    const res = await AuthService.getRechargeAddress({ "platform_id": id })
    address.value = res
}

const rechargeBtn = async () => {
    if (!amount.value) {
        return showToast(t("请输入充值金额"))
    }
    const res = await AuthService.getRechargeAddress({ "platform_id": routecontent.value?.id, "money": amount.value })
    window.location.href = res.url
}

onMounted(async () => {
    if (!route.query.content) {
        router.push('/')
    }
    routecontent.value = JSON.parse(decodeURIComponent(route.query.content))
    if (routecontent.value.currency_type != 2) {
        getdetail(routecontent.value.id)
    }
    const res = await AuthService.article_details({ type: '11' })
    rule.value = res
})

</script>

<style scoped></style>