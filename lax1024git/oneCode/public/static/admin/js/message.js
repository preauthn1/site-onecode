// 注册
Vue.component('child', {
    // 声明 props
    props: ['message'],
    // 就像 data 一样，prop 可以用在模板内
    // 同样也可以在 vm 实例中像“this.message”这样使用
    template: '<span>Child:{{ message }}</span>'
});
Vue.component('chat-box', {
    template: '#chat-box-template',
    props: ["chatList"],
    methods: {
        saveFast:function(mseeage, chat_id) {
            console.log(chat_id);
            layer.confirm("确定添加为快捷消息？", {icon: 3, title:'提示'}, function(index) {
                $.post(save_fast_url, {message:mseeage, chat_id:chat_id}, function(res) {
                    layer.msg(res.msg);
                });
                layer.close(index);
            });
        },
		withdraw:function(chat_id) {
			layer.confirm("确定删除该条消息？", {icon: 3, title:chat_id}, function(index) {
				$.post(withdraw_url, {message_id:chat_id}, function(res) {
					location.reload();
				    layer.msg(res.msg);
				});
				layer.close(index);
			});
		},
    }
});
Vue.component('robot-talk-content', {
    template: '#robot-talk-content-template',
    props: {
        robot: Object,
        index: Number
    },
	methods: {
		delMessage:function(chat_id) {
			layer.confirm("确定删除该条消息？", {icon: 3, title:chat_id}, function(index) {
				$.post(withdraw_url, {message_id:chat_id}, function(res) {
					location.reload();
				    layer.msg(res.msg);
				});
				layer.close(index);
			});
		},
	}
});
var app = new Vue({
    el: '#app',
    data: {
        account: account,
        hideTips: true,
        disabledSendBtn: true,
        tips: [],
        tipsList: [],
        content: '',
        setKey:false,
        chatList: [],
        message_ids: [],
        autoClose:true,
        autoNext:true,
        nextUserID:0,
        lastID:0,
        mute:account.isMute,
        timer:null,
    },
    created: function () {
        this.getChatList();
        this.getTipsList();
    },
    updated: function(a,b,c,d,e) {
        if($(".chat-box").size() > 0) {
            var DialogArea = $(".dialog-area");
            var ChatBox = $(".chat-box").last();
            var o = ChatBox.position().top + DialogArea.scrollTop() - 20;
            DialogArea.stop().animate({
                    scrollTop: o
            }, 0);
        }
    },
    mounted:function() {
        var vm = this;
        this.uploader = WebUploader.create({
            // swf文件路径
            swf: '/static/libs/webuploader/Uploader.swf',
            // 文件接收服务端。
            server: upload_url,
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#uploader_picker',
            // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
            auto: true,
            // 只允许选择图片文件。
            accept: {
                title: 'Images',
                extensions: 'jpg,jpeg,png',
                mimeTypes: 'image/*'
            },
            formData:{
                _ajax:1
            }
        });

        this.uploader.on('startUpload', () => {
            layer.load(2, {shade:[0.6,"#000"]})
        });

        this.uploader.on('uploadProgress', (file, percentage) => {
            console.log(percentage);
        });

        this.uploader.on('uploadSuccess', (file, response) => {
            if (response.code != -1) {
                vm.content = "img[" + response.data.src+"]";
                layer.msg(response.msg);
            }else{
                layer.msg(response.msg);
            }
        });
        this.uploader.on('uploadComplete', (file,response) => {
            console.log(response)
            layer.closeAll("loading")
            vm.uploader.reset();
        });

        this.uploader.on('uploadError', function (file) {
            vm.uploader.reset();
            layer.msg("上传图片出错了");
        });
        if(getCookie("autoClose") !== "") {
            this.autoClose = getCookie("autoClose") === "false" ? false : true;
        }
        if(getCookie("autoNext") !== "") {
            this.autoNext = getCookie("autoNext") === "false" ? false : true;
        }
    },
    watch: {
        // 如果 question 发生改变，这个函数就会运行
        content: function (newQuestion) {
            this.disabledSendBtn = this.content === "";
            if(!this.setKey) {
                this.getTips();
            }else{
                this.setKey = false;
            }
        },
        tips: function () {
            this.hideTips = !this.tips.length;
        },
        autoClose: function() {
            setCookie("autoClose", this.autoClose);
        },
        autoNext: function() {
            setCookie("autoNext", this.autoNext);
        }
    },
    methods: {
        getTips: _.debounce(
                function () {
                    var vm = this;
                    if (!vm.content) {
                        vm.tips = [];
                        return;
                    }
                    $.post(get_tips_url, {keyword: vm.content}, function (res) {
                        if (res.code !== 1) {
                            layer.msg(res.msg);
                        } else {
                            vm.tips = res.data.tips;
                        }
                    }, "JSON").error(function (error) {
                        console.log(error);
                        layer.msg("出错了");
                    });
                }
        , 500),
        reply: function (question) {
            if(typeof question === "object" && question.type === 'keyup' && question.shiftKey === true) {
                return false;
            }
            
            if(this.content === "") {
                return false;
            }
            this.sendReply(this.content);
            this.content = "";
        },
        setContent: function (res) {
            this.content = res.title;
            this.setKey = true;
            this.tips = [];
        },
        setContent1: function (title) {
            this.content = title;
            this.setKey = true;
            this.tips = [];
        },
        sendReply:function(content) {
            var vm = this;
            $.post(reply_url, {UserID:vm.account.UserID, content:content}, function (res) {
                if (res.code !== 1) {
                    layer.msg(res.msg);
                } else {
                    var data = res.data;
                    var $me = {
                        isMe:true, 
                        content: data.Content,
                        reply_time:data.CreateTime,
                        MasterName:data.MasterName,
                    };
                    vm.message_ids.push(data.MessageID);
                    vm.chatList.push($me);
                    vm.next();
                }
            }, "JSON").error(function (error) {
                console.log(error);
                layer.msg("出错了");
            });
        },
        setRead:function(){
            var vm = this;
            layer.confirm("确定操作吗？", {icon: 3, title:'提示'}, function(index) {
                $.post(setread_url, {MessageID:vm.userLastID}, function(res) {
                    if(res.code !== 1) {
                        layer.msg(res.msg);
                    }
                    
                    vm.next()
                });
                layer.close(index);
            });
        },
        getChatList: function() {
            var vm = this;
            window.clearTimeout(vm.timer);
            $.post(get_list_url, {UserID:vm.account.UserID, lastID:vm.lastID}, function (res) {
                if (res.code !== 1) {
                    layer.msg(res.msg);
                } else {
                    var data = res.data;
                    $.each(data, function(i, _item) {
                        vm.lastID = _item.id;
                        if ($.inArray(_item.id.toString(), vm.message_ids) > -1) {
                            return;
                        }
                        vm.message_ids.push(_item.id);
                        var chat = {isMe:0};
                        if(_item.master_id > 0) {//isMe 客服管理员
                            chat.isMe = 1;
                            chat.content = _item.content;
                            chat.MessageID = _item.id;
                            chat.reply_time = _item.create_time;
                            chat.MasterName = _item.MasterName;
                        }else{//用户 !isMe
                            chat.isMe = 0;
                            chat.robot = {id:_item.id,isReady:true, question:_item.content, ask_time:_item.create_time};
                            vm.userLastID = _item.id;
                        }
                        vm.chatList.push(chat);
                    });
                }
                vm.timer = window.setTimeout(function() {
                    vm.getChatList();
                }, 3000);
            }, "JSON").error(function (error) {
                console.log(error);
                layer.msg("出错了");
            });
        },
        
        getTipsList: function() {
            var vm = this;
            $.post(get_tips_url, {keyword: ''}, function (res) {
                if (res.code !== 1) {
                    layer.msg(res.msg);
                } else {
                    vm.tipsList = res.data.tips;
                }
            }, "JSON").error(function (error) {
                console.log(error);
                layer.msg("出错了");
            });
        },
        setMute:function(){
            var vm = this;
            layer.confirm("确定关闭消息提醒？", {icon: 3, title:'提示'}, function(index) {
                $.post(mute_url, {userid:account.UserID}, function(res) {
                    if(res.code !== 1) {
                        layer.msg(res.msg);
                    }

                    vm.mute = res.data
                });
                layer.close(index);
            });
        },
        closeChat:function() {
            var curDomain = location.protocol + '//' + location.host; 
            parent.postMessage("bmbclose", curDomain);
        },
        next:function() {
            var vm = this;
            if(vm.autoNext === true) {
                $.post(get_next_url, function (res) {console.log(852);
                    if (res.code !== 1 || res.url == '') {
                        console.log(123);
                        vm.closeChat();
                    } else {
                        window.location.assign(res.url);
                    }
                }, "JSON").error(function (error) {
                    vm.closeChat();
                });
            }else if(vm.autoClose === true) {
                vm.closeChat();
            }
        },
    }
});