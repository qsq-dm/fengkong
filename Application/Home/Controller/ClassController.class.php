<?php
namespace Home\Controller;
use Think\Controller;

class ClassController extends  Controller{

    public function manage(){
        $this->display('manage');
    }

    public function selectSch(){
        $this->display('selectSch');
    }

    /**
     *得到学校信息
     */
    public function getSchool(){
        $school = M('school');
        $where['isdelete'] = 1;
        $result = $school->where($where)->select();
        $this->ajaxReturn($result);
    }

    /**
     * @throws \PHPExcel_Reader_Exception
     * 数据导入
     */
    public function excelImport(){
        //获取文件名，并转编码
        $filename = iconv('UTF-8', 'GB2312', C('UPLOADS').I('post.fileName'));
        //引入读取excel类文件,这里要是物理路径
//        import('PHPEXCEL.IOFactory',C('PHPEXCEL'),'.php');
        require_once C('PHPEXCEL').'PHPExcel/IOFactory.php';
        //自动获取文件类型
        $fileType = \PHPExcel_IOFactory::identify($filename);
        //获取文件读取操作对象
        $objReader = \PHPExcel_IOFactory::createReader($fileType);
//        $objReader = new \PHPExcel_Reader_Excel2007();
        //只加载单元格数据
        $objReader->setReadDataOnly(true);
        //设置指定工作表
//        $objReader->setLoadSheetsOnly($sheetName);
        $objPHPExcel = $objReader->load($filename);
        $currentSheet = $objPHPExcel->getSheet(0);

        $data[0]['classid'] = $currentSheet->getCell('D3')->getValue();
        $data[0]['preclassid'] = $currentSheet->getCell('F3')->getValue();
        $data[0]['people'] = $currentSheet->getCell('B4')->getValue();
        $data[0]['distribution'] = $currentSheet->getCell('D4')->getValue();
        $data[0]['tutor'] = $currentSheet->getCell('F4')->getValue();
        $data[0]['schooltime'] = $currentSheet->getCell('B5')->getValue();
        $data[0]['classhours'] = $currentSheet->getCell('D5')->getValue();
        $data[0]['totalclass'] = $currentSheet->getCell('F5')->getValue();
        $data[0]['tution'] = date('Y-m-d',(($currentSheet->getCell('B6')->getValue() - 25569)  * 24 * 60 * 60));
        $data[0]['ending'] = date('Y-m-d',(($currentSheet->getCell('E6')->getValue() - 25569)  * 24 * 60 * 60));
        $data[0]['standard'] = $currentSheet->getCell('D8')->getValue();
        $data[0]['location'] = $currentSheet->getCell('D7')->getValue();

        $data[0]['schoolid'] = I('post.schoolId');
        $data[0]['isdelete'] = (bool)'1';

        $class = M('class');
        $where['classid'] = $data[0]['classid'];
        $res = $class->where($where)->count();
        if($res){
            $returnData['info'] = "导入数据库失败,数据库已存在";
            $returnData['status'] = 0;
            $this->ajaxReturn($returnData);
        }else{
            $result = $class->add($data[0]);
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
        $this->ajaxReturn($data,'JSON');

    }

    /**
     * 从数据库中读取班级信息
     */
    public function getClassInfo(){
        //接收easyui传递过来的页码和分页大小
        $page = I('post.page');
        $pageSize = I('post.rows');
        //计算当前页码的开始记录数
        $first = $pageSize * ($page -1);
        $class = M('class');
        $where['class.isdelete'] = 1;
        //计算总的记录数
        $total = $class->where($where)->count();
        $data = $class->join('school ON class.schoolid = school.id')->where($where)->limit($first,$pageSize)->order('class.id desc')->field('class.*,school.name')->select();
        //返回给easyui的数据
        $returnData['total'] = $total;
        $returnData['rows'] = $data;
        $this->ajaxReturn($returnData,'JSON');
    }

    public function add(){
        $school = M('school');
        $where['isdelete'] = 1;
        $data = $school->where($where)->select();
        $this->assign('data',$data);
        $this->display('add');
    }

    public function doadd(){
        $data = I('post.');
        $data['isdelete'] = (bool)'1';
        $result = M('class')->add($data);
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

    public function detailed(){
        $where['class.id'] = I('post.id');
        $result = M('class')->join('school ON class.schoolid = school.id')->where($where)->field('class.*,school.name')->find();
        $this->assign('data',$result);
        $map['isdelete'] = 1;
        $map['id'] = array('not in',$result['schoolid']);
        $schoolNmae = M('school')->where($map)->select();
        $this->assign('school',$schoolNmae);
        $this->display('detailed');
    }

    public function dodetailed(){
        $data = I('post.');
        $where['id'] = I('post.id');
        $result = M('class')->where($where)->save($data);
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

    public function delete(){
        $where['id'] = I('post.id');
        $result = M('class')->where($where)->setField('isdelete',(bool)'0');
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }

}