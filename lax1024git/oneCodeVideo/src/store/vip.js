import { defineStore } from "pinia";
import { ref } from "vue";
import { AuthService } from "@/api/home";
export const usevipStore = defineStore('vip',()=>{
    const taskInfo = ref()
    const choose_level_id = ref()
    const getviplist = async() => {
        const res = await AuthService.task_info()
        taskInfo.value = res
        console.log(taskInfo.value.task_count?.today_income)
    };
    return {
        choose_level_id,
        taskInfo,
        getviplist
    };
},
{
    persist: true,
})