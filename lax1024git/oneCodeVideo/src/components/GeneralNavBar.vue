<template>
    <van-nav-bar fixed :style="navbarStyle">
        <template #left v-if="showLogo">
            <div class="text-[16px]">
                <img :src="store.disposition?.site_info?.site_logo" class="w-auto h-[46px]" alt="">
            </div>
        </template>
       <!--  <template #right>
            <div class=" flex items-center gap-[26px]">
                <van-image contain class=" w-[20px] h-auto" :src="$getImageUrl('icon_down.png')" />
               
            </div>

        </template> -->
    </van-nav-bar>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { useSystemStore } from "../store/system";
const store = useSystemStore()
const navbarStyle = ref({'background-color':'rgba(0,0,0,0)!important'})
const windowScroll = ()=> {
    let scrollTop =  document.documentElement.scrollTop || document.body.scrollTop;
    let opacity = Math.abs(Math.round(scrollTop)) / 250;
    navbarStyle.value = {'background-color': `rgba(0,0,0,${opacity})!important`}
}

onMounted(()=>{
    window.addEventListener('scroll',windowScroll)
})

onBeforeUnmount(()=>{
    window.removeEventListener('scroll',windowScroll)
}) 

const props = defineProps({
  showLogo:{
    type:Boolean,
    default:true
  },
  showBack:{
    type:Boolean,
    default:true
  }
});
</script>

<style scoped>

</style>