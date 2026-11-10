import http from "../utils/request";
//successTips需要提示成功时使用
export class TurntableService {
    static async getTurntable() {
        try {
            return await http.get(
              "api.php/user/getTurntable",
            );
        } catch (error) {
            throw error; 
        }
    }
    static async onTurntable(params,callback) {
        try {
            return await http.get(
              "api.php/user/onTurntable",
              {params}
            );
        } catch (error) {
            callback()
            throw error; 
        }
    }
}