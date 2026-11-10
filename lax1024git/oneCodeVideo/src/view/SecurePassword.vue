<template>
    <div class=" px-[16px]">
        <van-nav-bar fixed left-arrow :title="$t('安全密码')" @click-left="router.go(-1)" />
        <div class=" pt-[78px]">
            <van-cell-group inset :border="false">
                <van-field v-model="passwordFrom.oldpassword" type="password" :placeholder="$t('请输入安全密码')" />
                <van-field v-model="passwordFrom.password" type="password" :placeholder="$t('请输入新的安全密码')" />
                <van-field v-model="passwordFrom.repassword" type="password" :placeholder="$t('请再次确认密码')" />
            </van-cell-group>
        </div>
        <div class=" mt-[96px]">
            <van-button round block type="primary" @click="subBtn">
                {{ $t("确认") }}
            </van-button>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { showToast } from "vant";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const router = useRouter()

const passwordFrom = reactive(
    {
        oldpassword: "",
        password: "",
        repassword: ""
    }
)
const subBtn = async() => {
    if(!passwordFrom.oldpassword){
        return showToast(t("请输入安全密码"))
    }
    if(!passwordFrom.password){
        return showToast(t("请输入新的安全密码"))
    }
    if(!passwordFrom.repassword){
        return showToast(t("请再次确认密码"))
    }
    const res = await AuthService.change_paypwd({...passwordFrom})
    showToast(t("修改成功"))
}
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