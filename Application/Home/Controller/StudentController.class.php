<?php
namespace Home\Controller;
use Think\Controller;

class StudentController extends  Controller{
    /**
     * 显示页面信息导入界面
     */
    public function import(){
        $this->display('import');
    }


    /**
     * 显示管理学生页面
     */
    public function manage(){
        $this->display('manage');
    }

    /**
     * 从数据库中读取学员信息
     */
    public function getStudentInfo(){
        //接收easyui传递过来的页码和分页大小
        $page = I('post.page');
        $pageSize = I('post.rows');
        //计算当前页码的开始记录数
        $first = $pageSize * ($page -1);
        $student = M('student');
        //计算总的记录数
        $total = $student->where('isdelete = 1')->count();
        $where = array();
        $where['isdelete'] = 1;
        $name = I('post.name');
        $studentid = I('post.studentid');
        $idcard = I('post.idcard');
        if(isset($name) && !empty($name)){
            $where['name'] = array('like',"%{$name}%");
        }
        if(isset($studentid) && !empty($studentid)){
            $where['studentid'] = array('like',"%{$studentid}%");
        }
        if(isset($idcard) && !empty($idcard)){
            $where['idcard'] = array('like',"%{$idcard}%");
        }

        $data = $student->where($where)->limit($first,$pageSize)->order('student.id desc')->join('degree on student.degreeid = degree.id')->field('student.*,degree.degreename')->select();

        foreach($data as $key=>$val){
            $data[$key]['textbook'] = $val['textbook'] == 1 ? '是' : '否';
            $data[$key]['sex'] = $val['sex'] == 1 ? '男' : '女';
        }
        //返回给easyui的数据
        $returnData['total'] = $total;
        $returnData['rows'] = $data;
        $this->ajaxReturn($returnData,'JSON');
    }

    public function studentExport(){
        $student = M('student');
        $data = $student->field('classid,name,education')->order('id asc')->select();
//        require_once C('PHPEXCEL').'PHPExcel.php';
        //引入phpexcel文件
        import('PHPExcel',C('PHPEXCEL'),'.php');
//        实例化phpexcel类，等同于在桌面上新建一个excel表格
        $objPHPExcel = new \PHPExcel();
//        获得当前活动sheet的操作对象
        $objSheet = $objPHPExcel->getActiveSheet();
//        给当前活动sheet设置名称
        $objSheet->setTitle('student');
//        给当前活动sheet填充数据
        $objSheet->setCellValue('A1','classid')->setCellValue('B1','name')->setCellValue('C1','education');
        $i = 2;
        foreach($data as $key=>$val){
            $objSheet->setCellValue("A{$i}",$val['classid'])->setCellValue("B{$i}",$val['name'])->setCellValue("C{$i}",$val['education']);
            $i++;
        }
//        $objSheet->setCellValue('A1','姓名')->setCellValue('B1','分数');
//        $objSheet->setCellValue('A2','李四')->setCellValue('B2','100');
/*        $arr = array(
            array(),
            array('姓名','分数'),
            array('王五','60'),
        );
        $objSheet->fromArray($arr);*/
//        按照指定格式生成excel文件
        $objWrite = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
//        $objWrite->save(C('PUBLIC').'demo.xlsx');


        $this->browser_export('Excel2007','学生信息.xlsx');
        $objWrite->save('php://output');
    }

    function browser_export($type,$filename){
        if($type == 'Excel5'){
//            输出excel03文件
            header('Content-Type: application/vnd.ms-excel');
        }else{
//            输出excel07文件
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
//        输出文件名称
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        //禁止缓存
        header('Cache-Control: max-age=0');
    }


    /**
     * @throws \PHPExcel_Reader_Exception
     * 在datagrid页面显示数据
     */
    public function excelImport(){
        $excelData = getExcelData(I('post.fileName'),1);
        $data = array();
        $idcards = array();
        $payment = array();
        //学历
        $degree = M('degree')->getField('id,degreename');
        foreach($excelData as $key=>$val){
            $data[$key]['classid'] = $val[0];
            $data[$key]['studentid'] = $val[1];
            $data[$key]['name'] = $val[2];
            $data[$key]['textbook'] = $val[6] == '是' ? (bool)'1' : (bool)'0';
            $data[$key]['status'] = $val[7];
            $data[$key]['spellname'] = $val[8];
            $data[$key]['sex'] = $val[9] == '男' ? (bool)'1' : (bool)'0';
            $data[$key]['nation'] = $val[10];
            $data[$key]['idcard'] = $val[11];
            $data[$key]['degreeid'] = array_search($val[13],$degree);
            $data[$key]['phone'] = $val[12];
            $data[$key]['address'] = $val[14];
            $data[$key]['postcode'] = $val[15];
            $data[$key]['email'] = $val[16];
            $data[$key]['remark'] = $val[17];
            $data[$key]['isdelete'] = (bool)'1';

            $idcards[] = $val[11];

            //缴费信息
            $payment[$key]['studentid'] = $val[1];
            $payment[$key]['money'] = $val[4];
            $payment[$key]['paydate'] = date('Y-m-d',(($val[3] - 25569) * 24 * 60 * 60));
        }

        $student = M('student');
        //查询有无重复记录数条件
        $where['idcard'] = array('in',$idcards);
        //返回重复记录数查询结果
        $repeatNumber = $student->where($where)->count();
        //如果有重复记录，退出
        if($repeatNumber){
            $returnData['info'] = "导入数据库失败,数据库已有{$repeatNumber}条重复记录数";
            $returnData['status'] = 0;
            $this->ajaxReturn($returnData);
        }else{
            //开启事务
//        $student->startTrans();
            //返回值为数组中插入第一条数据的ID
            $result = $student->addAll($data);
            $result2 = M('payment')->addAll($payment);
//            $res2 = M('studentclass')->addAll($classInfo);

            if($result){
                $returnData['info'] = '数据导入成功';
                $returnData['status'] = $result;
                $this->ajaxReturn($returnData);
            }else{
                $returnData['info'] = '导入失败,系统异常';
                $returnData['status'] = 0;
                $this->ajaxReturn($returnData);
            }
        }

    }

    public function detailed(){
        $where['id'] = I('post.id');
        $result = M('student')->where($where)->find();
        $result['degreename'] = M('degree')->where("id = {$result['degreeid']}")->getField('degreename');
        $degree = M('degree')->select();
        $this->assign('degree',$degree);
        $this->assign('data',$result);
        $this->display('detailed');
    }

    public function dodetailed(){
        $where['id'] = I('post.id');
        $data = I('post.');
        $data['sex'] = (bool)$data['sex'];
        $result = M('student')->where($where)->save($data);
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

    public function add(){
        $degree = M('degree')->select();
        $this->assign('degree',$degree);
        $this->display('add');
    }

    public function doadd(){
        $data = I('post.');
        $data['isdelete'] = (bool)'1';
        $result = M('student')->add($data);
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

    public function delete(){
        $where['id'] = I('post.id');
        $result = M('student')->where($where)->setField('isdelete',(bool)'0');
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

    public function payment(){
        $where['id'] = I('post.id');
        $result = M('student')->where($where)->find();
        $studentid = $result['studentid'];
        session('studentid',$studentid);
        $map['studentid'] = $studentid;
        $res = M('payment')->where($map)->order('paydate asc')->select();
        $sum = M('payment')->where($map)->sum('money');
        $arr[0]['paydate'] = '总计金额';
        $arr[0]['money'] = $sum;
        $aa['total'] = M('payment')->where($map)->count('money');
        $aa['rows'] = $res;
        $aa['footer'] = $arr;
        $this->ajaxReturn($aa);
    }

    public function paymentSave(){
        $data['money'] = I('post.money');
        $data['paydate'] = I('post.paydate');
        $data['studentid'] = session('studentid');
        $result = M('payment')->add($data);
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

    public function aa(){
        $student = M('student');

        $student->startTrans();
        $student->commit();
        $student->rollback();

        $data['studentid'] = '123';
        $data['classid'] = '444';
//        $data['aa'] = 'aaa';

//        $res = M('student')->where('studentid = 123')->save($data);

//        $res = M('student')->add($data);
        $res = M('studentclass')->add($data);

//        $res = M('student')->where('studentid = 123')->delete();
        show($res);
    }
}