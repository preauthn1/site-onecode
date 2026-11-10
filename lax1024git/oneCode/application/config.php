<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
error_reporting(E_ERROR | E_PARSE);

use think\Env;

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    // 应用命名空间
    'app_namespace' => 'app',
    // 应用调试模式
    'app_debug' => Env::get("APP_DEBUG", false),
    // 应用Trace
    'app_trace' => Env::get("APP_TRACE", false),
    // 应用模式状态
    'app_status' => '',
    // 是否支持多模块
    'app_multi_module' => true,
    // 入口自动绑定模块
    'auto_bind_module' => false,
    // 注册的根命名空间
    'root_namespace' => [],
    // 扩展函数文件
    'extra_file_list' => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type' => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return' => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler' => 'callback',
    // 默认时区
    'default_timezone' => Env::get("TIMEZONE", 'PRC'),
    // 是否开启多语言
    'lang_switch_on' => true,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter' => 'htmlspecialchars,strip_tags,removeXSS,trim',
    // 默认语言
    'default_lang' => 'zh-cn',
    // 应用类库后缀
    'class_suffix' => false,
    // 控制器类后缀
    'controller_suffix' => false,
    'http_agent_ip' => Env::get("HTTP_AGENT_IP", 'HTTP_X_REAL_IP'),
    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------
    // 默认模块名
    'default_module' => 'index',
    // 禁止访问模块
    'deny_module_list' => ['common'],
    // 默认控制器名
    'default_controller' => 'Index',
    // 默认操作名
    'default_action' => 'index',
    // 默认验证器
    'default_validate' => '',
    // 默认的空控制器名
    'empty_controller' => 'Error',
    // 操作方法后缀
    'action_suffix' => '',
    // 自动搜索控制器
    'controller_auto_search' => false,
    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------
    // PATHINFO变量名 用于兼容模式
    'var_pathinfo' => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr' => '/',
    // URL伪静态后缀
    'url_html_suffix' => '',
    // URL普通方式参数 用于自动生成
    'url_common_param' => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type' => 0,
    // 是否开启路由
    'url_route_on' => true,
    // 路由配置文件（支持配置多个）
    'route_config_file' => ['route'],
    // 是否强制使用路由
    'url_route_must' => false,
    // 域名部署
    'url_domain_deploy' => false,
    // 域名根，如thinkphp.cn
    'url_domain_root' => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert' => true,
    // 默认的访问控制器层
    'url_controller_layer' => 'controller',
    // 表单请求类型伪装变量
    'var_method' => '_method',
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------
    'template' => [
        // 模板引擎类型 支持 php think 支持扩展
        'type' => 'Think',
        // 模板路径
        'view_path' => '',
        // 模板后缀
        'view_suffix' => 'html',
        // 模板文件名分隔符
        'view_depr' => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin' => '{',
        // 模板引擎普通标签结束标记
        'tpl_end' => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end' => '}',
    ],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => APP_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl' => APP_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------
    // 异常页面的模板文件
    'exception_tmpl' => APP_PATH . 'tpl' . DS . 'think_exception.tpl',
    // 错误显示信息,非调试模式有效
    'error_message' => 'System Error',
    // 显示错误信息
    'show_error_msg' => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle' => '\\app\\exception\\Http',
    // 是否记录trace信息到日志
    'record_trace' => true,
    'http_exception_template' => [
        404 => APP_PATH . 'tpl' . DS . '404.tpl'
    ],
    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------
    'log' => [
        // 日志记录方式，内置 file socket 支持扩展
        'type' => 'File',
        // 日志保存目录
        'path' => LOG_PATH,
        // 日志记录级别
        'level' => ['error', "debug", "info", 'transfer', 'pay'],
        'apart_level' => ['error', "debug", 'sql', 'transfer', 'pay']
    ],
    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace' => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],
    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache' => [
        'type' => 'complex',
        'default' => [
            // 驱动方式
            'type' => 'File',
            // 缓存保存目录
            'path' => CACHE_PATH,
            // 缓存前缀
            'prefix' => '',
            // 缓存有效期 0表示永久缓存
            'expire' => 0,
        ],
        'redis' => [
            // 驱动方式
            'type' => 'redis',
            // redis主机
            'host' => Env::get("redis.host"),
            // redis端口
            'port' => Env::get("redis.port"),
            // 密码
            'password' => Env::get("redis.password"),
            // 密码
            'select' => Env::get("redis.select", 0),
        ],
        'redis1' => [
            // 驱动方式
            'type' => 'redis',
            // redis主机
            'host' => Env::get("redis.host"),
            // redis端口
            'port' => Env::get("redis.port"),
            // 密码
            'password' => Env::get("redis.password"),
            // 密码
            'select' => 1,
        ],
        'redis2' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 2,
        ],
        'redis3' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 3,
        ],
        'redis4' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 4,
        ],
        'redis5' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 5,
        ],
        'redis6' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 6,
        ],
        'redis7' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 7,
        ],
        'redis8' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 8,
        ],
        'redis9' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 9,
        ],
        'redis10' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 10,
        ],
        'redis11' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 11,
        ],
        'redis12' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 12,
        ],
        'redis13' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 13,
        ],
        'redis14' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 14,
        ],
        'redis15' => [
            'type' => 'redis',
            'host' => Env::get("redis.host"),
            'port' => Env::get("redis.port"),
            'password' => Env::get("redis.password"),
            'select' => 15,
        ],
    ],
    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------
    'session' => [
        'id' => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix' => 'think',
        // 驱动方式 支持redis memcache memcached
        'type' => 'redis',
        // 是否自动开启 SESSION
        'auto_start' => true,
        // redis主机
        'host' => Env::get("redis.host"),
        // redis端口
        'port' => Env::get("redis.port"),
        // 密码
        'password' => Env::get("redis.password"),
    ],
    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie' => [
        // cookie 名称前缀
        'prefix' => '',
        // cookie 保存时间
        'expire' => 0,
        // cookie 保存路径
        'path' => '/',
        // cookie 有效域名
        'domain' => '',
        //  cookie 启用安全传输
        'secure' => false,
        // httponly设置
        'httponly' => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],
    //分页配置
    'paginate' => [
        'type' => 'bootstrap',
        'var_page' => 'page',
        'list_rows' => 15,
    ],
    'upload' => [
        'mimes' => '', // 允许上传的文件MiMe类型
        'maxSize' => 20 * 1024 * 1024, // 上传的文件大小限制 (0-不做限制，默认为2M，后台配置会覆盖此值)
        'autoSub' => true, // 自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), // 子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => ROOT_PATH . 'public' . DS . 'upload', // 保存根路径
        'savePath' => '', // 保存路径
        'saveName' => 'date', // 上传文件命名规则
        'saveExt' => '', // 文件保存后缀，空则使用原后缀
        'replace' => false, // 存在同名是否覆盖
        'hash' => true, // 是否生成hash编码
        'callback' => false, // 检测文件是否存在回调函数，如果存在返回文件信息数组
    ],
    'captcha' => [
        // 验证码字符集合
        'codeSet' => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',
        // 验证码字体大小(px)
        'fontSize' => 12,
        // 是否画混淆曲线
        'useCurve' => false,
        'useNoise' => false,
        // 验证码图片高度
        'imageH' => 30,
        // 验证码图片宽度
        'imageW' => 100,
        // 验证码位数
        'length' => 4,
        'bg' => [255, 255, 255]
    ],
    //加密串
    'salt' => 'wZPb~yxvA!ir38&Z',
    // 状态
    'status' => [
        '1' => '正常',
        '2' => '禁用'
    ],
    // 会员状态
    'user_status' => [
        '1' => '正常',
        '2' => '禁用'
    ],
    // 充值状态
    'recharge_status' => [
        '1' => '待支付',
        '2' => '已支付',
        '3' => '已驳回'
    ],
    // 余额宝状态
    'yuebao_status' => [
        '1' => '已转入',
        '2' => '已转出'
    ],
    // 申请状态
    'withdrawals_status' => [
        '1' => '申请中',
        '2' => '审核通过',
        '4' => '已出款',
        '3' => '审核失败',
    ],
    'fx_level' => [
        '1' => '一',
        '2' => '二',
        '3' => '三',
        '4' => '四',
        '5' => '五',
        '6' => '六',
        '7' => '七'
    ],
    'prize_type' => [
        '0' => '返现红包',
        '1' => '增加抢单次数',
        '2' => '实物',
        '3' => '谢谢参与',
    ],
];
