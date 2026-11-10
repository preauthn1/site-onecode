import { defineStore } from "pinia";
import { ref } from "vue";
import { AuthService } from "@/api/home";
export const useUserStore = defineStore('user',()=>{
    const is_login = ref(false)
    const userInfo = ref({})
    const backRoute = ref("")
    const setuserInfo = (user) => {
        userInfo.value = user
        is_login.value = true
    }
    const clearUserInfo = ()=>{
        is_login.value = false
        userInfo.value = {}
    }
    const getUserInfo = async() => {
        const res = await AuthService.userinfo()
        if(userInfo.value){
            userInfo.value.user_info = res;
        }
    }
    return {
        userInfo,
        backRoute,
        is_login,
        clearUserInfo,
        setuserInfo,
        getUserInfo,
    }
},
// 持久化存储
{
    persist: true,
}

)