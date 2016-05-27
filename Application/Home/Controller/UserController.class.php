<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends CommonController{

    public function manage(){
        $this->display('manage');
    }

    public function getUserInfo(){
        //接收easyui传递过来的页码和分页大小
        $page = I('post.page');
        $pageSize = I('post.rows');
        //计算当前页码的开始记录数
        $first = $pageSize * ($page -1);
        $user = M('user');
        //计算总的记录数
        $total = $user->count();
        $data = $user->limit($first,$pageSize)->select();
        foreach($data as $key=>$val){
            $data[$key]['state'] = $val['state'] == 1 ? '正常' : '禁用';
        }
        //返回给easyui的数据
        $returnData['total'] = $total;
        $returnData['rows'] = $data;
        $this->ajaxReturn($returnData,'JSON');
    }

    public function add(){
        $this->display('add');
    }

    public function doadd(){
        $data['username'] = I('post.username');
        $data['name'] = I('post.name');
        $data['password'] = md5(I('post.password'));
        show($data);
        exit();
        $result = M('student')->add($data);
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

}