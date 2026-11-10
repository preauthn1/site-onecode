<?php
use think\Env;
$default_theme = Env::get("INDEX_THEME", "default");
if (!$default_theme) {
    $default_theme = 'default';
}
return [
    // 模板参数替换
    'view_replace_str' => array(
        '__PUBLIC__' =>  '/static',
        '__LIBS__' =>  '/static/libs',
        '__COMM__' =>  '/static/common',
        '__SERVICE__' =>  '/static/service',
		'__SERVICE1__' => '/static/service/service1',
        '__CSS__' => "/static/index/{$default_theme}/css",
        '__JS__' =>  '/static/common/js',
        '__THEME_JS__' =>  "/static//index/{$default_theme}/js",
        '__IMG__' => "/static/index/{$default_theme}/images",
    ),
    'auto_bind_module' => false,
    
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------
    'template' => [
        // 模板引擎类型 支持 php think 支持扩展
        'type' => '\liupanv\think\theme\Driver',
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
];
