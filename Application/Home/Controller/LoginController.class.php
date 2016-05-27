<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {

    /**
     * 显示登录页面
     */
    public function login(){
        $this->display('login');
    }

    /**
     * 验证登录信息
     * state用户状态，0和1
     */
    public function check_login(){
        if(IS_AJAX){
            $verify = I('post.verify');
            //验证验证码
            if(!check_verify($verify)){
                $this->ajaxReturn(0);
            }else{
                $where['username'] = I('post.username');
                $where['password'] = md5(I('post.password'));
                $user = M('user');
                $result = $user->where($where)->find();
                if($result != null && $result['state'] == 1){
                    session('id',$result['id']);
                    //设置时间
                    date_default_timezone_set('PRC');
                    $data['lastlogintime'] = date('Y-m-d H:i:s');
                    $map['id'] = $result['id'];
                    //更新数据库登录时间
                    $user->where($map)->save($data);
                    $this->ajaxReturn(1);
                }else if($result != null && $result['state'] != 1){
                    $this->ajaxReturn(2);
                }else{
                    $this->ajaxReturn(3);
                }
            }
        }
    }

    /**
     * 退出登录
     * 清空session，并返回登录页面
     */
    public function logout(){
        session(null);
        $this->redirect('Home/Login/login');
    }
}