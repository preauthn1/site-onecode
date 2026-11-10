<template>
    <div class=" login_page_bg relative min-h-screen px-4">
        <GeneralNavBar :showLogo="false" />
        <div class="pt-[73px] text-center font-bold">
            <img class=" w-auto h-[46px] mx-auto" :src="system.disposition?.site_info?.site_logo" alt="">
        </div>
        <div class=" mt-[35px]">
            <Tabs v-model="loginFrom.activeTab" :tabList="tabList" />
        </div>

        <div class=" mt-[21px]">
            <div>
                <van-form ref="Rform" @submit="onSubmit">
                    <van-cell-group inset :border="false">
                        <van-field v-model="loginFrom.username"
                            :placeholder="loginFrom.activeTab == 0 ? $t('请输入手机号') : $t('请输入电子邮箱')">
                            <template v-if="loginFrom.activeTab == 0" #label>
                                <div class="area-code" @click="showPicker = true">
                                    {{selectedCode}}
                                    <van-icon name="arrow-down" />
                                </div>
                            </template>
                        </van-field>
                        <van-field v-model.trim="loginFrom.password" type="password" 
                            :placeholder="$t('请输入密码')"/>
                    </van-cell-group>
                    <div class=" pt-[6px]">
                        <van-checkbox v-model.trim="isRemeberPassword" icon-size="19px"
                            checked-color="#75FA59">{{ $t('记住密码') }}</van-checkbox>
                    </div>
                    <div class=" mt-[71px]">
                        <van-button round block type="primary" native-type="submit">
                            {{ $t("登录") }}
                        </van-button>
                    </div>
                    <div class="mt-[19px] text-center">
                        {{ $t("没有账号？") }} <router-link to="/register" class="text-amount_color">{{ $t("注册") }}</router-link>
                    </div>
                </van-form>
            </div>
        </div>

        <div class=" absolute bottom-[14px] left-0 w-full flex justify-center" v-if="system.disposition?.site_info?.is_show_user_protocol !=0">
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
import { ref, reactive,computed, onMounted } from "vue";
import { showToast } from 'vant';
import { useRouter } from "vue-router";
import {AuthService} from '@/api/home'
import { useUserStore } from "@/store/userinfo";
import { useI18n } from 'vue-i18n';
import { useSystemStore } from "../store/system";
const { t } = useI18n();

const store = useUserStore();
const router = useRouter()
const system = useSystemStore();
const userinfo = JSON.parse(localStorage.getItem('userinfo'))
const isRemeberPassword = ref(false)
const isAgree = ref(true)
const showPicker = ref(false)

const loginFrom = reactive(
    {
        username: "",
        password: "",
        activeTab:0
    }
)
if(userinfo){
    loginFrom.activeTab = userinfo.activeTab
    loginFrom.password = userinfo.password
    loginFrom.username = userinfo.username
}
const tabList = ref([]) //标签组件已进行转换，不需要多语言转换

const fetchRgMethon = () => {
    switch (system.disposition.register_info.register_account_status) {
        case "0":
            tabList.value = ['手机', '电子邮箱']
            break;
        case "1":
            tabList.value = []
            loginFrom.activeTab = 1
            break;
        case "2":
            tabList.value = []
            break
    }
}

const allAreaCodes = ["+52"]
const selectedCode = ref("+52")
const searchInput = ref("")

const onConfirm = (value) => {
    selectedCode.value = value
    showPicker.value = false
}
const areaCodesList = computed(() => {
    return allAreaCodes.filter(item => item.toLowerCase().includes(searchInput.value.toLowerCase()));
})
const onSubmit = async () => {
    if(!isAgree.value && system.disposition?.site_info?.is_show_user_protocol !=0){
        return  showToast(t("请勾选协议"))
    }
    if(!loginFrom.username){
       return  showToast(loginFrom.activeTab ==0?t('请输入手机号'):t('请输入电子邮箱'))
    }
    if(!loginFrom.password){
       return  showToast(t('请输入密码'))
    }
    let params = {
        email:loginFrom.activeTab !== 0 ? loginFrom.username : '',
        mobile:loginFrom.activeTab == 0 ? loginFrom.username : '',
        login_type:loginFrom.activeTab == 0 ? "mobile" : "email",
        password:loginFrom.password,
        phone_code:loginFrom.activeTab == 0 ? selectedCode.value : ''
    }
    const res = await AuthService.login(params);
    store.setuserInfo(res);
    if(isRemeberPassword.value){
        localStorage.setItem('userinfo',JSON.stringify(loginFrom))
    }else{
        localStorage.removeItem('userinfo');
    }
    if(store.backRoute){
       return router.push(store.backRoute)
    }
    router.push('/')
}

onMounted(()=>{
    fetchRgMethon()
})
</script>

<style lang="less" scoped>
/* .login_page_bg {
    background: url("@/assets/images/login_bg.png") no-repeat;
    background-size: cover;
    background-position: center bottom;
    background-color: rgba(0, 0, 0, 0.04);
} */

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
</style>