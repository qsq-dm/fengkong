<?php
namespace Home\Controller;
use Think\Controller;

class CommonController extends Controller{
    /**
     * 初始化方法
     */
    function _initialize(){
        $id = session('id');
        //如果没有设置session，则跳转到登录页面
        if(empty($id)){
            $this->redirect('Home/Login/login');
        }
    }
}