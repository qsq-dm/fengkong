<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController
{
    public function index(){
        $this->assign('userInfo', $this->userInfo());
        $this->display();
    }

    /**
     * @return mixed
     * 得到用户信息
     */
    public function userInfo()
    {
        $user = M('user');
        $where['id'] = session('id');
        $info = $user->field('username,name,photo')->where($where)->find();
        $url = $_SERVER['DOCUMENT_ROOT'] . "/Public/plugins/shearphoto_common/file/shearphoto_file/" . $info['photo'];
        if ($info['photo'] == "" || (!file_exists($url))) {
            $info['photo'] = 'default_head.jpg';
        }
        return $info;
    }

    /**
     * @return array
     * 得到左侧导航栏菜单
     */
    public function menu(){
        //查询条件为当前用户登录ID
        $where['user.id'] = session('id');
        //得到当前用户菜单树规则id
        $rules = M('user')->where($where)->join('role ON user.roleid = role.id')->getField('role.rules');
        //将菜单id生成数组
        $menuId = explode(',',$rules);
        //菜单查询条件
        $map['id'] = array('in',$menuId);
        $map['state'] = 1;
        $resultMenu = M('menu')->where($map)->select();
        //将生成结果转换为数组菜单
        $resultArr = get_family($resultMenu);
        $this->ajaxReturn($resultArr);
    }

    //修改密码
    public function changePass(){
        $data = I('post.');
        if($data['oldPass'] == $data['newPass']){
            //两次输入密码一样
            $this->ajaxReturn(3);
        }
        $user = M('user');
        $where['id'] = session('id');
        $password = $user->where($where)->getField('password');
        if(md5($data['oldPass']) == $password){
            $pass['password'] = md5($data['newPass']);
            $result = $user->where($where)->save($pass);
            if($result){
                //修改成功
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn(4);
            }
        }else{
            //原始密码输入错误
            $this->ajaxReturn(2);
        }
    }
}