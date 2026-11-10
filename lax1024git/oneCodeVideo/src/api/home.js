import http from "../utils/request";
//successTips需要提示成功时使用
export class AuthService {
    /* 首页接口合并index */
    static async index(callback) {
        try {
            return await http.get(
                "api.php/index"

            );
        } catch (error) {
            callback()
            throw error;
        }
    }
    /* static async banner() {
        try {
            return await http.get(
              "api.php/banner",
            );
        } catch (error) {
            throw error; 
        }
    }*/
    static async notice(params) {
        try {
            return await http.get(
                "api.php/notice",{params}
            );
        } catch (error) {
            throw error;
        }
    }

    static async login(data) {
        try {
            return await http.post(
                "api.php/login/doLogin", data);
        } catch (error) {
            throw error;
        }
    }
    static async register(data) {
        try {
            return await http.post(
                "api.php/login/doReg", data);
        } catch (error) {
            throw error;
        }
    }

    static async sendSms(params) {
        try {
            return await http.get(
                "api.php/login/sendSms", { params });
        } catch (error) {
            throw error;
        }
    }
    static async sendEmail(params) {
        try {
            return await http.get(
                "api.php/login/sendEmail", { params });
        } catch (error) {
            throw error;
        }
    }
    //视频列表
    static async video_list(params) {
        try {
            return await http.get(
                "api.php/task/video_list", { params });
        } catch (error) {
            throw error;
        }
    }
    //视频详情
    static async video_detail(params) {
        try {
            return await http.get(
                "api.php/task/video_detail", { params });
        } catch (error) {
            throw error;
        }
    }
    //相关视频推荐
    static async recommend_video_list(params) {
        try {
            return await http.get(
                "api.php/task/recommend_video_list", { params });
        } catch (error) {
            throw error;
        }
    }
    //用户信息
    static async userinfo(params) {
        try {
            return await http.get(
                "api.php/user/info", { params });
        } catch (error) {
            throw error;
        }
    }
    //提交实名验证
    static async realNameAuth(data) {
        try {
            return await http.post(
                "api.php/user/realNameAuth", data);
        } catch (error) {
            throw error;
        }
    }
    //获取实名验证
    static async getRealNameAuth() {
        try {
            return await http.post(
                "api.php/user/getRealNameAuth");
        } catch (error) {
            throw error;
        }
    }
    //上传文件
    static async upload(data) {
        try {
            return await http.post(
                "api.php/upload/upload", data, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
        } catch (error) {
            throw error;
        }
    }
    //修改密码
    static async change_password(data) {
        try {
            return await http.post(
                "api.php/user/change_password", data);
        } catch (error) {
            throw error;
        }
    }
    //用户消息
    static async message(params) {
        try {
            return await http.get(
                "api.php/message", { params });
        } catch (error) {
            throw error;
        }
    }
    //用户信息详情
    static async message_detail(params) {
        try {
            return await http.get(
                "api.php/message/detail", { params });
        } catch (error) {
            throw error;
        }
    }
    //vip 
    static async task_info(params) {
        try {
            return await http.get(
                "api.php/task/task_info", { params });
        } catch (error) {
            throw error;
        }
    }
    //升级到指定等级
    static async upLevel(data) {
        try {
            return await http.post(
                "api.php/level/upLevel", data);
        } catch (error) {
            throw error;
        }
    }
    //获取充值方式
    static async recharge(params) {
        try {
            return await http.get(
                "api.php/recharge", { params });
        } catch (error) {
            throw error;
        }
    }
    //获取充值地址
    static async getRechargeAddress(params) {
        try {
            return await http.get(
                "api.php/recharge/getRechargeAddress", { params });
        } catch (error) {
            throw error;
        }
    }
    //修改安全密码
    static async change_paypwd(params) {
        try {
            return await http.get(
                "api.php/user/change_paypwd", { params });
        } catch (error) {
            throw error;
        }
    }
    //提现方式
    static async withdrawals() {
        try {
            return await http.get(
                "api.php/withdrawals");
        } catch (error) {
            throw error;
        }
    }
    //提现申请
    static async applicationwithdrawals(data) {
        try {
            return await http.post(
                "api.php/withdrawals/store", data);
        } catch (error) {
            throw error;
        }
    }
    //财务记录
    static async money_log(params) {
        try {
            return await http.get(
                "api.php/money_log", { params });
        } catch (error) {
            throw error;
        }
    }
    //签到天数列表
    static async signList(params) {
        try {
            return await http.get(
                "api.php/user/signList", { params });
        } catch (error) {
            throw error;
        }
    }
    //用户签到
    static async sign(params) {
        try {
            return await http.get(
                "api.php/user/sign", { params });
        } catch (error) {
            throw error;
        }
    }
    //获取余额宝基础数据
    static async getBalance(params) {
        try {
            return await http.get(
                "api.php/user/getBalance", { params });
        } catch (error) {
            throw error;
        }
    }
    //余额宝金额转入
    static async addBalance(data) {
        try {
            return await http.post(
                "api.php/user/addBalance", data);
        } catch (error) {
            throw error;
        }
    }
    //余额宝金额提取
    static async subBalance(data) {
        try {
            return await http.post(
                "api.php/user/subBalance", data);
        } catch (error) {
            throw error;
        }
    }
    //获取余额宝收益规则
   /*  static async getBalanceRule() {
        try {
            return await http.get(
                "api.php/user/getBalanceRule");
        } catch (error) {
            throw error;
        }
    } */
    //获取余额宝收益列表
    static async getBalanceLog(params) {
        try {
            return await http.get(
                "api.php/user/getBalanceLog", { params });
        } catch (error) {
            throw error;
        }
    }
    //匹配理财产品
    static async match(params) {
        try {
            return await http.get(
                "api.php/finance_product/match", { params });
        } catch (error) {
            throw error;
        }
    }
    //理财统计
    static async order_count() {
        try {
            return await http.get(
                "api.php/finance_product/order_count");
        } catch (error) {
            throw error;
        }
    }
    //理财首页
    static async finance_product(params) {
        try {
            return await http.get(
                "api.php/finance_product", { params });
        } catch (error) {
            throw error;
        }
    }
    //已支付理财列表
    static async order_list(params) {
        try {
            return await http.get(
                "api.php/finance_product/order_list", {params});
        } catch (error) {
            throw error;
        }
    }
    //购买理财
    static async buy(data) {
        try {
            return await http.post(
                "api.php/finance_product/buy", data);
        } catch (error) {
            throw error;
        }
    }
    //视频评分任务添加
    static async addVideoTask(params) {
        try {
            return await http.get(
                "api.php/mission/addVideoTask", { params });
        } catch (error) {
            throw error;
        }
    }
     //任务记录
     static async tasklist(params) {
        try {
            return await http.get(
                "api.php/task/list", { params });
        } catch (error) {
            throw error;
        }
    }
    //银行卡列表
    static async bank_list(params) {
        try {
            return await http.get(
                "api.php/bank", { params });
        } catch (error) {
            throw error;
        }
    }
     //绑定银行卡
     static async bind_bank(data) {
        try {
            return await http.post(
                "api.php/user/bind_bank",data);
        } catch (error) {
            throw error;
        }
    }
    //客服列表
    static async customer_service() {
        try {
            return await http.get(
                "api.php/customer_service");
        } catch (error) {
            throw error;
        }
    }
    //文章详情
    static async article_details(params) {
        try {
            return await http.get(
                "api.php/article/detail",{params});
        } catch (error) {
            throw error;
        }
    }
    //文章列表
    static async article(params) {
        try {
            return await http.get(
                "api.php/article",{params});
        } catch (error) {
            throw error;
        }
    }
    //系统配置
    static async init() {
        try {
            return await http.get(
                "api.php/index/init");
        } catch (error) {
            throw error;
        }
    }
    //消息列表
    static async pop_list() {
        try {
            return await http.get(
                "api.php/message/pop_list");
        } catch (error) {
            throw error;
        }
    }
    //会员列表
    static async vip_list() {
        try {
            return await http.get(
                "/api.php/index/vip_list");
        } catch (error) {
            throw error;
        }
    }
    //用户消息-弹窗消息
    static async pop_list() {
        try {
            return await http.get(
                "api.php/message/pop_list");
        } catch (error) {
            throw error;
        }
    }
    
}