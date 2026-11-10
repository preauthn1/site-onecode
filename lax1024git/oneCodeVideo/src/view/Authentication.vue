<template>
    <div class="pb-[50px] px-[16px]">
        <van-nav-bar fixed left-arrow :title="$t('实名认证')" @click-left="toBack()" />
        <div class=" mt-[71px]">
            <div class=" mb-[12px] text-[15px]">{{ $t('基本信息') }}</div>
            <van-cell-group inset :border="false">
                <van-field v-model="authenForm.real_name" :placeholder="$t('请输入真实姓名')" :label="$t('真实姓名')"
                    :disabled="authInfo?.status == 0 ? true : authInfo?.status == 1 ? true : false" />
                <!-- <van-field v-model="authenForm.phone" placeholder="请输入手机号码" label="手机号码" /> -->
                <van-field v-model="authenForm.id_card" :placeholder="$t('请输入证件号码')" :label="$t('证件号码')"
                    :disabled="authInfo?.status == 0 ? true : authInfo?.status == 1 ? true : false" />
            </van-cell-group>
        </div>

        <div class=" mt-2">
            <div class=" text-[15px] mb-[18px]">{{ $t('请上传证件正面照片') }}</div>
            <van-uploader v-model="fileList" multiple :max-count="1" :deletable="hasClear" :after-read="getid_card_front"
                 />
            <div class=" text-[15px] mb-[18px]">{{ $t('请上传证件反面照片') }}</div>
            <van-uploader v-model="fileList01" multiple :max-count="1" :deletable="hasClear" :after-read="getid_card_back"
                 />
            <div class=" text-[15px] mb-[18px]">{{ $t('请上传手持证件照片') }}</div>
            <van-uploader v-model="fileList02" multiple :max-count="1" :deletable="hasClear" :after-read="gethand_hold_photo"
                 />
            <div class=" mt-[20px]">
                <div class=" flex items-center gap-[9px] mb-[12px]">
                    <img class=" w-auto h-[19px]" :src="$getImageUrl('icon_warn.png')" alt="">
                    <span class="text-[15px]">{{ $t('温馨提示') }}</span>
                </div>
                <div class=" mb-[14px]">1.{{ $t('上传照片时请保证照片清晰完整、无水印') }}</div>
                <div>2.{{ $t('证件照片中的号码与填写的证件号码保持一致') }}</div>
            </div>

            <div class=" mt-[28px]" v-if="!authInfo?.real_name || authInfo?.status == 2">
                <van-button round block type="primary" @click="onSubmitForm">
                    {{ $t('提交') }}
                </van-button>
            </div>
            <div class=" mt-[28px]" v-else-if="authInfo?.status == 0">
                <van-button round block type="primary">
                    {{ $t('审核中') }}
                </van-button>
            </div>
            <div class=" mt-[28px]" v-else-if="authInfo?.status == 1">
                <van-button round block class="bg-[#999]" type="default" disabled>
                    {{ $t('实名完成') }}
                </van-button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { AuthService } from "@/api/home";
import { showToast } from "vant";

import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const router = useRouter()
const route = useRoute()
const authInfo = ref()
const hasClear = ref(true)
const authenForm = reactive(
    {
        real_name: "",
        id_card: "",
        id_card_front: "",
        id_card_back: "",
        hand_hold_photo: "",
    }
)
const fileList = ref([]);
const fileList01 = ref([]);
const fileList02 = ref([]);

const onSubmitForm = async () => {
    if (!authenForm.real_name) {
        return showToast(t("请输入真实姓名"))
    }
    if (!authenForm.id_card) {
        return showToast(t("请输入证件号码"))
    }
    if (!authenForm.id_card_front) {
        return showToast(t("请上传证件正面照片"))
    }
    if (!authenForm.id_card_back) {
        return showToast(t("请上传证件反面照片"))
    }
    if (!authenForm.hand_hold_photo) {
        return showToast(t("请上传手持证件照片"))
    }
    const params = {
        real_name: authenForm.real_name,
        id_card: authenForm.id_card,
        id_card_front: authenForm.id_card_front,
        id_card_back: authenForm.id_card_back,
        hand_hold_photo: authenForm.hand_hold_photo
    }
    await AuthService.realNameAuth(params)
    fileList.value = []
    fileList01.value = []
    fileList02.value = []
    await getNameAuth()

}
const getid_card_front = async (file) => {
    try {
        const res = await afterRead(file)
        authenForm.id_card_front = res
    } catch (error) {
        fileList.value[0].status = 'failed'
        fileList.value[0].message = t('上传失败');
    }


}
const getid_card_back = async (file) => {
    try {
        const res = await afterRead(file)
        authenForm.id_card_back = res
    } catch (error) {
        fileList01.value[0].status = 'failed'
        fileList01.value[0].message = t('上传失败');
    }

}
const gethand_hold_photo = async (file) => {
    try {
        const res = await afterRead(file)
        authenForm.hand_hold_photo = res
    } catch (error) {
        fileList02.value[0].status = 'failed'
        fileList02.value[0].message = t('上传失败');
    }
}
const afterRead = async (file) => {
    const formData = new FormData();
    formData.append('file', file.file);
    formData.append('type', "auth");
    const res = await AuthService.upload(formData)
    return res.url
}
const getNameAuth = async () => {
    const res = await AuthService.getRealNameAuth();
    authInfo.value = res;
    if(res.status ==2){
        hasClear.value =true
    }else{
        hasClear.value =false
    }
    authenForm.id_card = res.id_card;
    authenForm.real_name = res.real_name
    authenForm.id_card_front = res.id_card_front,
        authenForm.id_card_back = res.id_card_back,
        authenForm.hand_hold_photo = res.hand_hold_photo,
        fileList.value.push({ "url": res.id_card_front })
    fileList01.value.push({ "url": res.id_card_back })
    fileList02.value.push({ "url": res.hand_hold_photo })
}
function toBack() {
    if (route.query.cate == 1) {
        router.go(-2);
    } else {
        router.go(-1);
    }
}
onMounted(async () => {
    await getNameAuth()
    console.log(authInfo.value.status)
})
</script>

<style lang="less" scoped>
::v-deep(.van-cell-group--inset) {
    margin: 0 !important;
}

::v-deep(.van-cell) {
    &::after {
        content: none;
    }
}
</style>