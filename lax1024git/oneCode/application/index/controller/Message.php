<?php

namespace app\index\controller;
use OSS\OssClient;
use think\Cache;
use util\Agent;
use Grafika\Gd\Editor;
use app\common\model\MessageModel;

class Message extends Base {
    
    public function index() {
        $this->setTitle(lang('在线客服'), 1);
        $act = session("act");
        if ($act == "uploader") {
            $this->redirect(url('uploader/index'));
        }
        
        $this->assign("isAndroid", Agent::osType() == Agent::OS_TYPE_ANDROID);
        
        $msgId = 0;//MessageModel::where('user_id', $this->userid)->order("id ASC")->value("id");
        if($msgId > 0){
            $msgId = $msgId - 1;
        }
        $kefu = [];
        $kefu_fast_message = getConfig('kefu_fast_message');
        if (empty($kefu_fast_message)) {
            $kefu_fast_message = ["我要拼单", "我要回购", "我要充值", "设置CP"];
        }else{
            $kefu_fast_message = explode("\n", $kefu_fast_message);
        }
        $this->assign("info", $this->userInfo);
        $this->assign("kefu", $kefu);
        $this->assign("msgId", $msgId);
        $this->assign("type", 0);
        $this->assign("kefu_fast_message", $kefu_fast_message);
        return $this->fetch();
    }
    
    public function getrecord(){
        $lastID = $this->request->param("lastID/d", 0);
        $unreadNum = db("message")->where('master_id',">",0)->where('user_id', $this->userid)->where('read',0)->count();
//        $data = db("message")->where('user_id', $this->userid)->where('create_time', ">=", timeToStr(strtotime("-1 days")))->where("id", ">", $lastID)->order("id ASC")->select();
        $data = db("message")->where('user_id', $this->userid)->where('create_time', ">=", timeToStr(strtotime("-1 days")))->order("id ASC")->select();
        if($lastID == 0 && count($data) == 0){
            $message = [
                "user_id" => $this->userid,
                "content" => lang("您好,有什么可以帮助您的吗?"),
                "master_id" => 999999,
                "create_time" => timeToStr(),
                "ip" => $this->request->ip()
            ];
            $message['id'] = db("message")->insertGetId($message);
            $message['MasterName'] = lang("客服");
            $message['mine'] = false;
            $message['avatar'] = "/static/service/images/Avatar_kefu.png";
            $data[] = $message;
        }else{
            foreach ($data as &$value) {
                if (stripos($value['content'], "图片地址") !== false) {
                    $value['content'] = str_replace("图片地址：", "", $value['content']);
                    $value['content'] = "<a target='_blank' href='{$value['content']}'><i class='fa fa-picture'></i> 图片信息，点击查看</a>";
                }else{
                    $value['content'] = html2(br2nl(removeXSS($value['content'])));
                }
                $value['create_time'] = removeMilliseconds($value['create_time']);
                $value['reply_time'] = removeMilliseconds($value['reply_time']);
                $value['timestamp'] = getMkTime($value['create_time'])*1000;

                if($value['master_id'] == 0){
                    $value['MasterName'] = $this->userInfo['nick_name'];
                    $value['mine'] = true;
                    $value['avatar'] = $this->userInfo['avatar'];
                }else{
                    $value['MasterName'] = lang("客服");
                    $value['mine'] = false;
                    $value['avatar'] = "/static/service/images/Avatar_kefu.png";
                }
            }
        }
        db("message")->where('user_id', $this->userid)->where('read', 0)->where('master_id', '>', 0)->update(['read' => 1]);
        unset($value);
        $this->success("ok", "", $data, $unreadNum);
    }
    
    public function filters(){
        $content = $this->request->param("content/s", "", "trim,removeXSS");
        if (empty($content)) {
            $this->success("ok!");
        }
        //敏感关键词过滤
        $sensitive = db("messagefilter")->field("keyword")->where("INSTR('{$content}',keyword)")->find();
        if(!empty($sensitive)) {
            $this->error("您提交的问题包含敏感词语,请检查后再提交");
        }else{
            $this->success("ok!");
        }
    }

    public function send() {
        $content = $this->request->param("content/s", "", "trim,removeXSS");
        if (empty($content)) {
            $this->error(lang("消息内容不能为空"));
        }
        if (strlen($content) > 200) {
            $this->error(lang('内容最长不能超过x个字符', [200]));
        }
        $count = db("message")
                ->whereBetween("create_time", [date("Y-m-d H:i:s",time()-30), date("Y-m-d H:i:s",time())])
                ->where('user_id', $this->userid)
                ->where('master_id', 0)
                ->count();
        if($count > 3){
            $this->error(lang("消息发送过于频繁，请稍后再试!"));
        }
        //敏感关键词过滤
//        $sensitive = db("messagefilter")->field("keyword")->where("INSTR('{$content}',keyword)")->find();
//        if(!empty($sensitive)) {
//            $this->error(lang("您提交的问题包含敏感词语，请检查后再提交!"));
//        }
        
        $data = [
            "user_id" => $this->userid,
            "content" => $content,
            "create_time" => timeToStr(),
            "ip" => $this->request->ip(),
            "source_url" => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            "browser" => $_SERVER['HTTP_USER_AGENT']
        ];
        
//        $redis = Cache::store("redis3");
//        if ($redis->has(md5("mute{$userid}"))) {
//            $data['read'] = 1;
//        }

        if (stripos($content, "img[") === false) {
            $fastkeywords = [];//Cache::store("redis5")->get("fastkeywords");
            $keyword = "";
            $keys2 = [];
            if (!empty($fastkeywords) && is_array($fastkeywords)) {
                foreach ($fastkeywords as $v2) {
                    foreach ($v2['keywords'] as $key) {
                        if (strexists(strtolower($content), strtolower($key))) {
                            if (isset($keys2[$v2['id']])) {
                                $keys2[$v2['id']]+=1;
                            }else{
                                $keys2[$v2['id']]=1;
                            }
                        }
                    }
                }
                arsort($keys2);
                $keyword = implode(",", $keys2);
            }
            
            $data["keyword"] = $keyword;
            $data['id'] = db("message")->insertGetId($data);
            //db("message")->where(['user_id'=>$userid])->where("id", "<", $data['id'])->update(['read' => 1]);
            db("message")->where(['user_id'=>$this->userid])->where("id", "<", $data['id'])->where("master_id", ">", 0)->update(['read' => 1]);
            
            //自动回复
            if (!empty($keys2)) {
                $auto_id = array_keys($keys2)[0];
                $AutoReply = db("messagefast")->where("id", $auto_id)->where(["status" => 1, "auto_reply"=>1])->field("id,content,count")->find();
            
                if(!empty($AutoReply)) {
                    db("messagefast")->where(['id'=>$AutoReply['id']])->update(['count'=>$AutoReply['count']+1]);

                    $message = [
                        "user_id" => $this->userid,
                        "content" => $AutoReply['content'],
                        "master_id" => 999999,
                        "create_time" => timeToStr(),
                        "ip" => $this->request->ip()
                    ];
                    db("message")->insertGetId($message);
                    db("message")->where(['user_id'=>$this->userid])->where("id", "=", $data['id'])->update(['read' => 1]);
                }
            }
            //自动回复 END
        }else{
            $data['id'] = db('message')->insertGetId($data);
            if($data['id'] > 0){
                db("message")->where(['user_id'=>$this->userid])->where("id", "<", $data['id'])->where("master_id", ">", 0)->update(['read' => 1]);
            }else
                $this->result("", 201, 'fail');
        }
        $this->success("ok!", "", $data);
    }

    public function upload() {
        $redis_key = "message_upload:{$this->userid}";
        if (Cache::get($redis_key) >= 5) {
            $this->success(lang("上传失败,请勿频繁发送图片"));
        }
        
        $UploadModel = new \app\common\model\UploadModel();
        $ret = $UploadModel->Upload('file','kfimage');
        if($ret){
            $ret['data']['src'] = toMedia($ret['data']['path']);
            $ret['data']['url'] = toMedia($ret['data']['path']);
            if (Cache::has($redis_key)) {
                Cache::inc($redis_key);
            }else{
                Cache::set($redis_key, 1, 300);
            }
            return $this->error($ret['msg'], '', $ret['data']);
        } else {
            return $this->success($ret['msg']);
        }
    }
}
