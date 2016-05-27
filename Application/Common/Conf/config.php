<?php

$config  = array(
    //'配置项'=>'配置值'
);

//引用自定义配置文件
$myconfig = require_once "Public/config/config.php";

return array_merge($config,$myconfig);

