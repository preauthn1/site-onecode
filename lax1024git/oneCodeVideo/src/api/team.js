import http from "../utils/request";
//successTips需要提示成功时使用
export class TeamService {
    static async teamInfo(params) {
        try {
            return await http.get(
              "api.php/team/team_info",{params}
            );
        } catch (error) {
            throw error; 
        }
    }
    static async teamDetail(params) {
        try {
            return await http.get(
              "api.php/team/detail/1",
              {params}
            );
        } catch (error) {
            throw error; 
        }
    }
    
    static async directPushTask(params) {
        try {
            return await http.get(
              "api.php/task/rec_reward",
              {params}
            );
        } catch (error) {
            throw error; 
        }
    }
    //领取任务奖励
    static async get_rec_reward(params) {
        try {
            return await http.get(
              "api.php/task/get_rec_reward",
              {params}
            );
        } catch (error) {
            throw error; 
        }
    }
    //邀请活动奖励
    static async get_event_rewards (params) {
        try {
            return await http.get(
              "/api.php/activitynew",
              {params}
            );
        } catch (error) {
            throw error; 
        }
    }
    //领取邀请活动奖励
    static async getReward (params) {
        try {
            return await http.get(
              "/api.php/activitynew/getReward",
              {params}
            );
        } catch (error) {
            throw error; 
        }
    }
    
}