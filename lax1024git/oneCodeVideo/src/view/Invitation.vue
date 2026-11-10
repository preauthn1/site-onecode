<template>
    <div class="pb-[80px]">
        <GeneralNavBar />
        <div class="content">
            <div class="pt-[441px] ml-[29px]">
                <span class="text-[13px] font-medium">{{ $t("邀请链接") }}</span>
                <div class="flex items-center">
                    <span class="text-[11px] ">{{store.userInfo?.user_info?.share_link}}</span>
                    <van-icon :name="$getImageUrl('icon_copy_white.png')" class="left-[7px]" @click="$copy(store.userInfo?.user_info?.share_link,t)"></van-icon>
                </div>
            </div>
            <div class="pt-[64px] pl-[17px] pb-[19px] pr-[16px] flex justify-between">
                <div class="flex items-center">
                    <van-image fit="cover" round width="62.5" height="62.5" :src="store.userInfo?.user_info?.avatar" />
                    <div class="flex flex-col pl-[8px] justify-center">
                        <!-- <span class="text-[13px] font-medium">{{store.userInfo?.user_info?.mobile}}</span> -->
                        <div>
                            <span class="text-[14px] font-bold">{{$t("邀请代码")}}：{{store.userInfo?.user_info?.invite_code}}</span>
                            <van-icon :name="$getImageUrl('icon_copy_white.png')" class="left-[7px]" @click="$copy(store.userInfo?.user_info?.invite_code,t)"></van-icon>
                        </div>
                    </div>
                </div>
                <div class="text-center flex flex-col items-center">
                    <div class="w-[87px] h-[87px]">
                        <qcord :url="store.userInfo?.user_info?.share_link" class="rounded-[5px]"></qcord>
                    </div>
                    <span class="text-[10px] ml-[-10px] mr-[-10px]">{{ $t("点击保存二维码") }}</span>
                </div>
            </div>
            
        </div>
        <div v-html="content?.content" class="mt-[20px] px-[15px]"></div>
    </div>
</template>

<script setup>
import GeneralNavBar from "@/components/GeneralNavBar.vue"
import qcord from "@/components/qcord.vue";
import { useUserStore } from "@/store/userinfo";
import { AuthService } from "@/api/home";
import { onMounted,ref } from "vue";
import { useI18n } from 'vue-i18n';
const {t} = useI18n()
const store = useUserStore();
const content = ref()
onMounted(async()=>{
    store.getUserInfo();
    const res = await AuthService.article_details({type:"6"})
    content.value = res
})
</script>

<style scoped>
    .invite_nav_bg {
        --van-nav-bar-background: #00000000;
    }
    .content{
        width: 100%;
        height: auto;
        background: url("@/assets/images/invite_top_bg.png") no-repeat top center;
        background-size: 100% 100%;
    }
    .bottom-content {
        margin-top: 20px;
        margin-left: 16px;
        margin-right: 16px;
        margin-bottom: 100px;
        background-color: #15131E;
        border-radius: 5px;
    }
</style>