<template>
    <div class=" pb-[74px]">
        <GeneralNavBar />
        <div class="profile_head">
            <div class="mb-6 flex mt-[24px] ml-[16px] mr-[16px] justify-between">
                <div class="w-16 h-16 rounded-full bg-gray-300 overflow-hidden">
                    <img :src="store.userInfo?.user_info?.avatar" alt="" />
                </div>
                <div class="flex-1 flex justify-between items-center">
                    <div class="ml-[10px]">
                        <div>
                            <span class="text-[16px] font-semibold">{{ showUserName() }}</span>
                            <van-icon @click="showName = !showName" class="ml-[10px]"
                                :name="showName ? 'eye-o' : 'closed-eye'" size="16" />
                        </div>
                        <div class="flex items-center">
                            <span class="text-[10px] text-gray-200">{{ $t("邀请码") }}：{{
                                store.userInfo?.user_info?.invite_code }}</span>
                            <van-icon :name="$getImageUrl('icon_copy_white.png')"
                                @click="$copy(store.userInfo?.user_info?.invite_code,t)" class="left-[7px]"></van-icon>
                        </div>
                    </div>
                    <div @click="router.push('/dailyattendance')">
                        <van-icon name="point-gift-o" size="24" />
                    </div>
                </div>
            </div>
            <div class="profile-background-linear rounded-[5px] ml-[16px] mr-[16px] pb-[40px]">
                <div class="flex justify-center">
                    <div class="mt-[16px] flex flex-col items-center" @click="router.push('/eWallet')">
                        <div class="mt-2 text-lg">{{ $t("账户余额") }}
                            <van-icon name="arrow" />
                        </div>
                        <div class="text-[16px] font-semibold">{{ store?.userInfo?.user_info?.user_money }}
                            {{ $sysCurrency }}</div>
                    </div>
                </div>
                <div class="flex ml-[10px] mr-[10px] justify-around">
                    <div class="mt-[10px] text-center">
                        <div class="mt-2 text-lg">{{ $t("总收入") }}
                        </div>
                        <div class="text-[14px] font-semibold text-amount_color">
                            {{ store?.userInfo?.user_info?.total_income }} {{ $sysCurrency }}</div>
                    </div>
                    <div class="mt-[10px] text-center">
                        <div class="mt-2 text-lg">{{ $t("佣金收入总额") }}
                        </div>
                        <div class="text-[14px] font-semibold text-amount_color">
                            {{ store?.userInfo?.user_info?.total_brokerage_income }} {{ $sysCurrency }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-[#15131D] rounded-[10px] mt-[15px] ml-[16px] mr-[16px] mb-[10px] flex flex-col justify-around">
            <div class="flex justify-around mt-[10px]">
                <div class="flex flex-col items-center justify-center">
                    <div class="text-lg">{{ $t("累计储值") }}</div>
                    <div class="text-lg">{{ store?.userInfo?.user_info?.recharge_money }} {{ $sysCurrency }}</div>
                </div>
                <!-- <div class="w-[1px] mt-[10px] mb-[10px] bg-[#FFFFFF24]"></div> -->
                <div class="flex flex-col items-center justify-center">
                    <div class="text-lg">{{ $t("累计提款") }}</div>
                    <div class="text-lg">{{ store?.userInfo?.user_info?.withdrawal_amount }} {{ $sysCurrency }}</div>
                </div>
            </div>
            <div class="mt-[20px] mb-[10px] flex justify-around">
                <div class="flex flex-col items-center justify-center">
                    <div class="text-lg">{{ $t("团队总规模") }}</div>
                    <div class="text-lg">{{ store?.userInfo?.user_info?.team_num }} {{ $t("人") }}</div>
                </div>
                <!-- <div class="w-[1px] mt-[10px] mb-[10px] bg-[#FFFFFF24]"></div> -->
                <div class="flex flex-col items-center justify-center">
                    <div class="text-lg">{{ $t("今日新增") }}</div>
                    <div class="text-lg">{{ store?.userInfo?.user_info?.today_team_num }} {{ $t("人") }}</div>
                </div>
            </div>
        </div>
        <div class="ml-[16px] mr-[16px]">
            <div class="yue-content flex flex-col relative" @click="router.push('/yuebao')">
                <div class="absolute right-[5px] top-[10px]">
                    <img :src="$getImageUrl('icon_yue_pic.png')" alt="" class="w-[107.5px] h-[103px]">
                </div>
                <span class="text-[20px] font-bold ml-[10px] pt-[30px]">{{ $t("余额宝") }}</span>
                <span class="text-[13px] ml-[10px] mr-[100px] mt-[0px] pb-[30px]">{{ $t("加入余额宝，享有更多收益") }}</span>
            </div>
        </div>

        <div>
            <van-cell :title="$t('银行卡')" :icon="$getImageUrl('icon_bankcard.png')" @click="changePage('/bankcard')"
                center icon-prefix="profile-menu-icon" is-link />
            <van-cell :title="$t('登录密码')" :icon="$getImageUrl('icon_password.png')"
                @click="changePage('/loginPassword')" center icon-prefix="profile-menu-icon" is-link />
            <van-cell :title="$t('安全密码')" :icon="$getImageUrl('icon_secure.png')" @click="changePage('/securePassword')"
                center icon-prefix="profile-menu-icon" is-link />
            <van-cell :title="$t('记录')" :icon="$getImageUrl('icon_record.png')" @click="changePage('/record')" center
                icon-prefix="profile-menu-icon" is-link />
            <van-cell :title="$t('帮助中心')" :icon="$getImageUrl('icon_help.png')" @click="changePage('/helpCenter')"
                center icon-prefix="profile-menu-icon" is-link />
            <!-- <van-cell :title="$t('客服')" :icon="$getImageUrl('icon_headset.png')" @click="handleserver" center
                icon-prefix="profile-menu-icon" is-link /> -->
            <van-cell :title="$t('通知')" :icon="$getImageUrl('icon_notice.png')" @click="changePage('/notice')" center
                icon-prefix="profile-menu-icon" is-link />
            <!-- <van-cell :title="$t('实名认证')" :icon="$getImageUrl('icon_authentication.png')"
                @click="changePage('/authentication')" center icon-prefix="profile-menu-icon" is-link /> -->
            <van-cell :title="$t('下载')" :icon="$getImageUrl('icon_app_down.png')" @click="inStall" center
                icon-prefix="profile-menu-icon" is-link />
        </div>

        <div class=" mt-[32px] mx-[15px] pb-[42px]">
            <van-button round block type="primary" @click="logout">
                <span class=" font-bold">{{ $t("退出") }}</span>
            </van-button>
        </div>
       <!--  <van-popup v-model:show="isShow" position="bottom" class="pop">
            <div class="pop-content">
                <div class="content-wapper">
                    <img :src="system.disposition?.site_info?.site_logo" class="logo">
                    <span class="desc">{{ $t("我们希望将本站添加到您的主屏幕，以获取更好的体验") }}。</span>
                </div>
                <div class="btn-wapper">
                    <span class="cancel" @click="isShow = false">{{ $t("取消") }}</span>
                    <van-button class=" ml-[20px] w-[80px] h-[30px]" round block type="primary" @click="inStall">{{
                        $t("继续")
                        }}</van-button>
                </div>
            </div>
        </van-popup> -->
    </div>

</template>

<script setup>
import { useUserStore } from '../store/userinfo';
import GeneralNavBar from "@/components/GeneralNavBar.vue"
import { useRouter } from "vue-router";
import { onMounted, ref } from "vue";
import { useSystemStore } from "../store/system";
import { useI18n } from 'vue-i18n';
const {t} = useI18n()
const showPrompt = ref(false);
const system = useSystemStore();

const store = useUserStore();

const router = useRouter()

const showName = ref(false);
function showUserName() {
    let userNameString = ref('');
    userNameString = store.userInfo?.user_info?.user_name;
    if (!showName.value) {
        if (isEmail(userNameString)) {
            userNameString = userNameString.replace(/^(.).*?(@.*)$/, (match, firstChar, domain) => {
                return firstChar + "****" + domain;
            });
        } else {
            userNameString = `${store.userInfo?.user_info?.user_name.slice(0, 4)}****${store.userInfo?.user_info?.user_name.slice(-3)}`;
        }
    }
    return userNameString;
}
function isEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}


const logout = () => {
    store.clearUserInfo();
    router.push('/login')
}

const changePage = (path) => {
    router.push(path)
}
function detectMobileType() {
  const userAgent = navigator.userAgent || navigator.vendor || window.opera;

  // 判断是否为 iOS
  if (/iPhone|iPad|iPod/i.test(userAgent)) {
    return 'iOS';
  }
  
  // 判断是否为 Android
  if (/Android/i.test(userAgent)) {
    return 'Android';
  }
  
  // 其他类型
  return 'Unknown';
}

// 示例用法
 // 输出: 'iOS' 或 'Android' 或 'Unknown'

const inStall = () => {
    if(detectMobileType() == 'iOS'){
        window.location.href = system.disposition.site_info.app_ios_download_url
    }else{
        window.location.href = system.disposition.site_info.app_android_download_url
    }
   
};
const isShow = ref(false)
/* 
const addScreen = () => {
    isShow.value = true
} */

onMounted(() => {
    store.getUserInfo()
})

</script>

<style lang="less" scoped>
.profile_head {
    width: 100%;
    // height: 334px;
    background: url("@/assets/images/mine_bg.png") no-repeat;
    background-size: 100% 100%;
    padding-top: 60px;
}

.profile-background-linear {
    // background: linear-gradient(0deg, #F4C5D7, #F0A96B, #EF8186, #03177D);
    background: linear-gradient(0deg, #15131e, #1555fa);
}

.yue-content {
    width: 100%;
    height: auto;
    background: url("@/assets/images/icon_yue_background.png") no-repeat center;
    background-size: 100% 70%;
}

::v-deep(.van-cell__left-icon) {
    margin-right: 10px;
}

::v-deep(.profile-menu-icon) {
    width: 20px;
    height: 20px;

    img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
    }

}

::v-deep(.van-cell) {
    background-color: transparent !important;

    &::after {
        content: none;
    }
}

.wallet-type-backview {
    background-color: #15131E;
    border-radius: 26px 26px 0 0;
}

.service-line-color {
    -van-cell-border-color: #FFFFFF15;
}

.pop-content {
    padding: 12px;
}

.logo {
    width: auto;
    height: 54px;
    border-radius: 12px;
    object-fit: cover;
    margin-right: 12px;
    flex-shrink: 0;
}

.content-wapper {
    display: flex;
}

.desc {
    font-size: 14px;
    line-height: 1.5;
    margin-top: 4px;
}

.btn-wapper {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-top: 2px;
}

.btn {
    margin-left: 29px;
    min-width: 89px;
}

.cancel {
    color: var(--text-color1);
    font-size: 14px;
}
</style>