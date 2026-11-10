刷单系统
===========

下载项目
```
git clone https://github.com/yazi-team/ssg.git
```

安装
```
cd ssg
composer install
```

环境变量
```
cp env.sample .env
```


短信配置
```
cp application/extra/sms.php.sample application/extra/sms.php
```


支付配置
```
cp application/extra/pay.php.sample application/extra/pay.php
```

### 队列
创建自动提现监听队列
```
php think queue:create listen
```


使用supervisor管理以下进程


刷单佣金发放，默认不使用队列，在.env配置[ORDER_QUEUE]开启
```
php绝对路径/php 网站目录绝对路径/think queue:listen

/usr/bin/php think queue:listen
```


提现处理队列
```
php绝对路径/php 网站目录绝对路径/think queue:listen --queue=agentpay

/usr/bin/php think queue:listen --queue=agentpay
```
活动处理队列
```
php绝对路径/php 网站目录绝对路径/think queue:listen --queue=activity

/usr/bin/php think queue:listen --queue=activity
```
会员报表队列 设置8进程
```
php绝对路径/php 网站目录绝对路径/think queue:listen --queue=userreport

/usr/bin/php think queue:listen --queue=userreport

//创建会员报表任务
php think userreport
```

### 计划任务
自动统计日报表
```
/bin/php /www/wwwroot/ssg/think day_report
```


### 其他事项
```
文件:thinkphp/library/think/session/driver/Redis.php 115行
return $this->handler->delete($this->config['session_name'] . $sessID) > 0;
换成
return $this->handler->del($this->config['session_name'] . $sessID) > 0;


文件:thinkphp/library/think/cache/driver/Redis.php 165行
return $this->handler->delete($this->getCacheKey($name));
换成
return $this->handler->del($this->getCacheKey($name));

文件:thinkphp/library/think/cache/driver/Redis.php 180行
$this->handler->delete($key);
换成
$this->handler->del($key);

```
[更新日志](./CHANGE.md)