<?php
namespace Home\Controller;
use Think\Controller;

class PublicController extends Controller{
    public function verify(){
        $config = array(
            'fontSize'  =>  14,    // 验证码字体大小
            'length'    =>  4,     // 验证码位数
            'useNoise'  =>  false, // 关闭验证码杂点
            'codeSet'   =>  '0123456789', //验证码字符集合
            'imageW'    =>  100,
            'imageH'    =>  38,
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    /**
     * 跳转到修改头像页面
     */
    public function photo(){
        $this->display('photo');
    }

    /**
     * 修改头像后更新数据库
     */
    public function changePhoto(){
        $photoName = I('post.photoName');
        $user = M('user');
        $where['id'] = session('id');
        $historyInfo = $user->where($where)->getField('photo');
        $url = $_SERVER['DOCUMENT_ROOT']."/Public/plugins/shearphoto_common/file/shearphoto_file/".$historyInfo;
        if(file_exists($url)){
            unlink($url);
        }
        $data['photo'] = $photoName;
        $user->where($where)->save($data);
        $this->ajaxReturn($photoName);
    }

    public function upload(){
        //函数来自phpexcel提供的例子
        upload();
    }

}