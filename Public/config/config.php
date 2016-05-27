<?php
//自定义配置文件

//判断是否加载thinkphp
if(!defined('THINK_PATH')) exit();

return array(
//    'URL_HTML_SUFFIX' => 'html', // URL伪静态后缀设置
    // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    'URL_MODEL' => 2,

    'DEFAULT_TIMEZONE' => 'PRC', // 默认时区
    //数据库配置信息
    'DB_TYPE'   => 'mysql', // 数据库类型

    'DB_HOST'   => 'localhost', // 服务器地址
    'DB_NAME'   => 'fengkong', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'askedu73@q8', // 密码

    //疑问：用127.0.0.1比localhost快几秒
//    'DB_HOST'   => '127.0.0.1', // 服务器地址
//    'DB_NAME'   => 'manage', // 数据库名
//    'DB_USER'   => 'root', // 用户名
//    'DB_PWD'    => 'root', // 密码
    'DB_PORT'   => 3306, // 端口
    'DB_PARAMS' =>  array(), // 数据库连接参数
    'DB_PREFIX' => '', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集
    'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志

    'PUBLIC'=> '/Public/',
    //eacyui路径
    'EASYUI' => '/Public/plugins/easyui/',
    //phpexcel路径要是物理路径
    'PHPEXCEL' => dirname(__DIR__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'phpexcel'.DIRECTORY_SEPARATOR,
    //上传插件
    'PLUPLOAD' => '/Public/plugins/plupload/',
    //图片上传插件
    'SHEARPHOTO'=>'/Public/plugins/shearphoto_common/',
    'HOME_CSS' => '/Public/Home/css/',
    'HOME_JS' => '/Public/Home/js/',
    'HOME_IMAGES' =>'/Public/Home/images/',
    'UPLOADS'=>dirname(__DIR__).DIRECTORY_SEPARATOR.'Uploads'.DIRECTORY_SEPARATOR,
);