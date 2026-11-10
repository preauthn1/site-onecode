<template>
    <div class=" login_page_bg relative min-h-screen px-4">
        <GeneralNavBar :showLogo="false" />
        <div class="pt-[73px] text-center font-bold">
            <img class=" w-auto h-[46px] mx-auto" :src="system.disposition?.site_info?.site_logo" alt="">
        </div>
        <div class=" mt-[35px]">
            <Tabs v-model="activeTab" :tabList="tabList" />
        </div>

        <div class=" mt-[21px]">
            <div>
                <van-form>
                    <van-cell-group inset :border="false">
                        <van-field v-model.trim="registerForm.username" :type="activeTab == 1 ? '' : 'number'"
                            :placeholder="activeTab == 1 ? $t('请输入电子邮箱') : $t('请输入手机号')">
                            <template v-if="activeTab == 0" #label>
                                <div class="area-code" @click="showPicker = true">
                                    {{ selectedCode }}
                                    <van-icon name="arrow-down" />
                                </div>
                            </template>
                        </van-field>
                        <van-field v-model.trim="registerForm.verificationCode" maxlength="6" type="text"
                            :placeholder="$t('请输入验证码')"
                            v-if="activeTab == 1 ? system.disposition?.register_info?.is_verify_email_code == 1 : system.disposition?.register_info?.is_verify_phone_code == 1">
                            <template #button>
                                <div @click="sendVerificationCode"
                                    :class="isCounting ? ' cursor-not-allowed' : 'cursor-pointer'"
                                    class="light_txt underline">{{ snedBtnText }}
                                </div>
                            </template>
                        </van-field>
                        <van-field v-model.trim="registerForm.password" type="password" :placeholder="$t('请输入密码')" />
                        <van-field v-model.trim="registerForm.repassword" type="password" :placeholder="$t('请输入重复密码')"
                            v-if="system.disposition?.register_info?.is_repeat_password == 1" />
                        <van-field v-model.trim="registerForm.secupaypwd" type="password"
                            :placeholder="$t('请输入安全密码')" />
                        <van-field v-model.trim="registerForm.invite_code" :placeholder="$t('请输入邀请码')" />
                    </van-cell-group>
                    <div class=" mt-[16px]">
                        <van-button round block type="primary" @click="onSubmitRegisterForm">
                            {{ $t("注册") }}
                        </van-button>
                    </div>
                    <div class="mt-[8px] text-center">
                        {{ $t("已有账号？") }} <router-link to="/login" class="text-amount_color">{{ $t("去登录")
                            }}</router-link>
                    </div>
                </van-form>
            </div>
        </div>

        <div class=" absolute bottom-[8px] left-0 w-full flex justify-center" v-if="system.disposition?.site_info?.is_show_user_protocol !=0">
            <van-checkbox v-model="isAgree" icon-size="19px" checked-color="#75FA59">
                {{ $t("同意我们的") }}
            </van-checkbox>
            <router-link to="/agreement" class="text-amount_color">{{ $t("《用户协议暨隐私政策》") }}</router-link>
        </div>

        <van-popup v-model.trim:show="showPicker" position="bottom">
            <div class="wallet-type-backview">
                <div
                    class="mx-auto h-[32px] max-w-[720px] w-full flex items-center border border-#86909C rounded-[6px] border-solid px-[10px] text-slate-500">
                    <van-icon name="search" size="16" />
                    <input v-model.trim="searchInput" type="text" class="ml-[10px] flex-1 bg-transparent text-white">
                </div>
                <ul class="mx-auto max-h-[200px] mt-[10px] max-w-[720px] w-full flex-1 overflow-y-auto">
                    <li @click="onConfirm(item)" v-for="(item, index) in areaCodesList" :key="index"
                        class="mb-[8px] flex cursor-pointer items-center justify-between border-b border-dashed border-slate-300 last:border-none p-[8px] ">
                        <span :class="item === selectedCode ? 'light_txt' : 'text-white'">{{ item }}</span>
                        <van-icon v-if="item === selectedCode" name="certificate" color="#fcac6b" size="16" />
                    </li>
                </ul>
            </div>
        </van-popup>
    </div>
</template>

<script setup>
import GeneralNavBar from "@/components/GeneralNavBar.vue"
import Tabs from "@/components/Tabs.vue"
import { ref, reactive, computed, onMounted } from "vue";
import { showToast } from 'vant';
import { AuthService } from '@/api/home'
import { useUserStore } from "@/store/userinfo";
import { useRouter, useRoute } from "vue-router";
import { useI18n } from 'vue-i18n';
import { useSystemStore } from "../store/system";

const { t } = useI18n();
const store = useUserStore();
const system = useSystemStore();
const router = useRouter()
const route = useRoute();
const isRemeberPassword = ref(false)
const isAgree = ref(true)
const registerForm = reactive(
    {
        username: "",
        password: "",
        secupaypwd: "",
        verificationCode: "",
        invite_code: "",
        repassword: "",
        pid: "",
        click_id: "",
    }
)

const tabList = ref([])
const activeTab = ref(0)
const fetchRgMethon = () => {
    switch (system.disposition.register_info.register_account_status) {
        case "0":
            tabList.value = ['手机', '电子邮箱']
            break;
        case "1":
            tabList.value = []
            activeTab.value = 1
            break;
        case "2":
            tabList.value = []
            
            break
    }
}

/* const allAreaCodes = ["+52", "+55", "+86", "+1", "+44", "+33", "+91"] */
const allAreaCodes = ["+52"]
const selectedCode = ref("+52")
const searchInput = ref("")

const isCounting = ref(false)
const countdown = ref(60)
const snedBtnText = computed(() => {
    return isCounting.value ? `${countdown.value}s${t('后重试')}` : t('发送验证码');
})
const sendVerificationCode = async () => {
    if (!registerForm.username) {
        return showToast(activeTab.value == 1 ? t('请输入电子邮箱') : t('请输入手机号'))
    }
    if (activeTab.value == 1) {
        await AuthService.sendEmail({ email: registerForm.username });
    } else {
        if(!validateNumber(registerForm.username)){
            return showToast(t("请输入8-19位号码"));
        };
        await AuthService.sendSms({ mobile: selectedCode.value + registerForm.username });
    }
    showToast(t("验证码已发送"))
    startCountdown();
}
function validateNumber(input) { 
    const regex = /^\d{8,19}$/; 
    return regex.test(input); 
}
const startCountdown = () => {
    isCounting.value = true
    countdown.value = 60
    const timer = setInterval(() => {
        countdown.value -= 1;
        if (countdown.value <= 0) {
            clearInterval(timer); // 清除定时器
            isCounting.value = false; // 恢复到可点击状态
        }
    }, 1000);
}

const areaCodesList = computed(() => {
    return allAreaCodes.filter(item => item.toLowerCase().includes(searchInput.value.toLowerCase()));
})
const showPicker = ref(false)

const onConfirm = (value) => {
    selectedCode.value = value
    showPicker.value = false
}


const onSubmitRegisterForm = async () => {
    if (!isAgree.value && system.disposition?.site_info?.is_show_user_protocol != 0) {
        return showToast(t("请勾选协议"))
    }
    if (!registerForm.username) {
        return showToast(activeTab.value == 1 ? t('请输入电子邮箱') : t('请输入手机号'))
    }
    if (!registerForm.password) {
        return showToast(t('请输入密码'))
    }
    if (!registerForm.secupaypwd) {
        return showToast(t('请输入安全密码'))
    }
    if (!registerForm.repassword && system.disposition?.register_info?.is_repeat_password == 1) {
        return showToast(t('请输入重复密码'))
    }
    if (!registerForm.verificationCode && (activeTab.value == 1 ? system.disposition?.register_info?.is_verify_email_code == 1 : system.disposition?.register_info?.is_verify_phone_code == 1)) {
        return showToast(t('请输入验证码'))
    }
    if (!registerForm.invite_code && system.disposition?.register_info?.invitation_code_require == 1) {
        return showToast(t('请输入邀请码'))
    }
    let params = {
        register_type: activeTab.value == 0 ? "mobile" : "email",
        password: registerForm.password,
        paypwd: registerForm.secupaypwd,
        verificationCode: registerForm.verificationCode,
        invite_code: registerForm.invite_code,
        repassword: registerForm.repassword,
        pid: registerForm.pid,
        click_id: registerForm.click_id,
    }
    if (activeTab.value == 0) {
        params['phone_code'] = selectedCode.value
        params['mobile'] = registerForm.username
    } else {
        params['email'] = registerForm.username
    }
    const res = await AuthService.register(params);
    showToast(t("注册成功"))
    store.setuserInfo(res);
    if (store.backRoute) {
        return router.push(store.backRoute)
    }
    router.push('/')
}
onMounted(() => {
    const popu = localStorage.getItem("popularizar")
    if (popu) {
        try {
            const pop = JSON.parse(popu);
            registerForm.invite_code = pop.ic
            registerForm.pid = pop.pid
            registerForm.click_id = pop.click_id
        } catch (error) {

        }
    }


    if (route.query.ic) {
        registerForm.invite_code = route.query.ic
    }

    fetchRgMethon()
})

</script>

<style lang="less" scoped>
.login_page_bg {
    /*  background: url("@/assets/images/login_bg.png") no-repeat;
    background-size: cover;
    background-position: center bottom;
    background-color: rgba(0, 0, 0, 0.04); */
    padding-bottom: 50px;
}

::v-deep(.van-cell-group--inset) {
    margin: 0 !important;
}

::v-deep(.van-cell) {
    &::after {
        content: none;
    }
}

.wallet-type-backview {
    padding: 16px;
    background-color: #15131E;
    border-radius: 26px 26px 0 0;
}

.van-popup {
    background: transparent !important;
}

::-webkit-scrollbar {
    display: none;
}

.light_txt {
    color: #0937AD;
}
</style>