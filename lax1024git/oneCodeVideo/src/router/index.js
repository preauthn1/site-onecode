// src/router/index.ts

import { createRouter, createWebHashHistory } from 'vue-router'
import { useUserStore } from '../store/userinfo';
import { KeepAlive } from 'vue';
const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('../view/Home.vue'),
        meta: {
            showTabbar: false //控制是否显示底部导航
        }
    },
    {
        path: '/upgrade',
        name: 'Upgrade',
        component: () => import('../view/Upgrade.vue'),
        meta: {
            showTabbar: false //控制是否显示底部导航
        }
    },
    {
        path: '/honouredGuest',
        name: 'HonouredGuest',
        component: () => import('../view/HonouredGuest.vue'),
        meta: {
            showTabbar: false //控制是否显示底部导航
        }
    },
    {
        path: '/invitation',
        name: 'Invitation',
        component: () => import('../view/Invitation.vue'),
        meta: {
            showTabbar: false //控制是否显示底部导航
        }
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('../view/Profile.vue'),
        meta: {
            showTabbar: false //控制是否显示底部导航
        }
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('../view/Login.vue')
      
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('../view/Register.vue')
      
    },
    {
        path: '/notice',
        name: 'Notice',
        component: () => import('../view/Notice.vue'),
        meta:{KeepAlive:true}
      
    },
    {
        path: '/noticeDetails',
        name: 'NoticeDetails',
        component: () => import('../view/NoticeDetails.vue')
      
    },
    {
        path: '/authentication',
        name: 'Authentication',
        component: () => import('../view/Authentication.vue')
      
    },
    {
        path: '/helpCenter',
        name: 'HelpCenter',
        component: () => import('../view/HelpCenter.vue')
      
    },
    {
        path: '/record',
        name: 'Record',
        component: () => import('../view/Record.vue')
      
    },
    {
        path: '/historyRecord',
        name: 'HistoryRecord',
        component: () => import('../view/HistoryRecord.vue')
      
    },
    {
        path: '/securePassword',
        name: 'SecurePassword',
        component: () => import('../view/SecurePassword.vue')
      
    },
    {
        path: '/loginPassword',
        name: 'LoginPassword',
        component: () => import('../view/LoginPassword.vue')
      
    },
    {
        path: '/movieDetails',
        name: 'MovieDetails',
        component: () => import('../view/MovieDetails.vue')
    },
    {
        path: '/movieLibrary',
        name: 'MovieLibrary',
        component: () => import('../view/MovieLibrary.vue')
    },
    {
        path: '/financialcenter',
        name: 'FinancialCenter',
        component: () => import('../view/FinancialCenter.vue')
    },
    {
        path: '/investmentrecords',
        name: 'InvestmentRecords',
        component: () => import('../view/InvestmentRecords.vue')
    },
    {
        path: '/rechargechannels',
        name: 'RechargeChannels',
        component: () => import('../view/RechargeChannels.vue')
    },
    {
        path: '/rechargedetail',
        name: 'RechargeDetail',
        component: () => import('../view/RechargeDetail.vue')
    },
    {
        path: '/eWallet',
        name: 'EWallet',
        component: () => import('../view/EWallet.vue')
    },
    {
        path: '/withdrawal',
        name: 'Withdrawal',
        component: () => import('../view/Withdrawal.vue')
    },
    {
        path: '/guestscorecenter',
        name: 'GuestScoreCenter',
        component: () => import('../view/GuestScoreCenter.vue')
    },
    {
        path: '/upgradelist',
        name: 'UpgradeList',
        component: () => import('../view/UpgradeList.vue')
    },
    {
        path: '/commadvertis',
        name: 'CommAdvertis',
        component: () => import('../view/CommAdvertis.vue')
    },
    {
        path: '/investconfir',
        name: 'InvestConfir',
        component: () => import('../view/InvestConfir.vue')
    },
    {
        path: '/yuebao',
        name: 'Yuebao',
        component: () => import('../view/Yuebao.vue')
    },
    {
        path: '/profitrules',
        name: 'ProfitRules',
        component: () => import('../view/ProfitRules.vue')
    },
    {
        path: '/transferyue',
        name: 'TransferYue',
        component: () => import('../view/TransferYue.vue')
    },
    {
        path: '/membershiplist/:id',
        name: 'MembershipList',
        component: () => import('../view/MembershipList.vue')
    },
    {
        path: '/dailyattendance',
        name: 'DailyAttendance',
        component: () => import('../view/DailyAttendance.vue')
    },
    {
        path: '/bankcard',
        name: 'BankCard',
        component: () => import('../view/BankCard.vue')
    },
    {
        path: '/editbankcard',
        name: 'EditBankCard',
        component: () => import('../view/EditBankCard.vue')
    },
    {
        path: '/directpushtask',
        name: 'DirectPushTask',
        component: () => import('../view/DirectPushTask.vue')
    },
    {
        path: '/agreement',
        name: 'Agreement',
        component: () => import('../view/Agreement.vue')
    },
    {
        path: '/helpdetail',
        name: 'HelpDetail',
        component: () => import('../view/HelpDetail.vue')
    }
    
];
const whiteList = ["/","/login","/register","/agreement"]

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

//全局前置守卫
router.beforeEach((to,from,next)=>{
    const store = useUserStore();
    if(!whiteList.includes(to.path)){
        if(store.is_login){
            next()
        }else{
            store.backRoute = to.path
            router.push("/login")
        }
    }else{
        next();
    }
    
})
export default router;