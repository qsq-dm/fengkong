<?php
namespace Home\Controller;
use Think\Controller;
class SchoolController extends Controller{

    public function manage(){
        $this->display('manage');
    }

    public function getSchoolInfo(){
        //接收easyui传递过来的页码和分页大小
        $page = I('post.page');
        $pageSize = I('post.rows');
        //计算当前页码的开始记录数
        $first = $pageSize * ($page -1);
        $school = M('school');
        //条件为没有被删除的
        $where['isdelete'] = 1;
        //计算总的记录数
        $total = $school->where($where)->count();
        $data = $school->where($where)->limit($first,$pageSize)->order('id desc')->select();
    /*    foreach($data as $key=>$val){
            $data[$key]['state'] = $val['state'] == 1 ? '正常' : '禁用';
        }*/
        //返回给easyui的数据
        $returnData['total'] = $total;
        $returnData['rows'] = $data;
        $this->ajaxReturn($returnData,'JSON');
    }

    public function edit(){
        $where['id'] = I('post.id');
        $result = M('school')->where($where)->find();
        $this->assign('data',$result);
        $this->display('edit');
    }

    public function doedit(){
        $where['id'] = I('post.id');
        $data['name'] = I('post.name');
        $result = M('school')->where($where)->save($data);
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

    public function delete(){
        $where['id'] = I('post.id');
        $result = M('school')->where($where)->setField('isdelete',(bool)'0');
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }

    }

    public function add(){
        $this->display('add');
    }

    public function doadd(){
        $data['name'] = I('post.name');
        $data['isdelete'] = (bool)'1';
        $result = M('school')->add($data);
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

}