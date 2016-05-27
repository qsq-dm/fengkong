<?php
namespace Home\Controller;
use Think\Controller;

class LoanController extends Controller{

    public function manage(){
        $instData = M('institute')->select();
        $this->assign('instData',$instData);
        $this->display('manage');
    }

    public function excelImport(){
        //接收日期
        $month = I('post.dateVal');
        //获取文件名，并转编码
//        $filename = iconv('UTF-8', 'GBK', C('UPLOADS').date('Y-m-d').DIRECTORY_SEPARATOR.I('post.filePath'));
        $filename = iconv('UTF-8', 'GBK',I('post.filePath'));
        //引入读取excel类文件,这里要是物理路径
//        import('PHPEXCEL.IOFactory',C('PHPEXCEL'),'.php');
        require_once C('PHPEXCEL').'PHPExcel/IOFactory.php';
        //自动获取文件类型
        $fileType = \PHPExcel_IOFactory::identify($filename);
        //获取文件读取操作对象
        $objReader = \PHPExcel_IOFactory::createReader($fileType);
//        $objReader = new \PHPExcel_Reader_Excel2007();
        //指定工作表名
//        $sheetName = array('Sheet1');
        //只加载单元格数据
        $objReader->setReadDataOnly(true);
        //设置指定工作表
//        $objReader->setLoadSheetsOnly($sheetName);
        $objPHPExcel = $objReader->load($filename);
        $currentSheet = $objPHPExcel->getSheet(0);
        $allColumn = $currentSheet->getHighestColumn();
        $allRow = $currentSheet->getHighestRow();
        $arr = array();
        $excelData = array();
        //循环读取每个单元格数据，从第二行开始
        for($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++){
            for($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++){
                $addr = $colIndex.$rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if($cell == null){
                    continue;
                }
                if($cell instanceof \PHPExcel_RichText){
                    $cell = $cell->__toString();
                }
                $arr[] = $cell;
            }
            if($arr == null){
                continue;
            }
            $excelData[] = $arr;
            $arr = null;
        }
        //贷款机构
        $institute = M('institute')->getField('instid,instname');
        //贷款套餐
        $loantype = M('loantype')->getField('typeid,typename');
        //学校
        $school = M('school')->getField('id,name');
        $loaninfo = M('loaninfo');
        $data = array();
        $number = 0;
        foreach($excelData as $key=>$val){
            $data[$key]['loaninstid'] = array_search($val[0],$institute);
            $data[$key]['loaner'] = $val[1];
            $data[$key]['idcard'] = $val[2];
            $data[$key]['schoolid'] = array_search($val[3],$school);
            $data[$key]['loanmoney'] = $val[4];
            $data[$key]['loantypeid'] = array_search($val[5],$loantype);
            $data[$key]['loandate'] = $val[6];
            $data[$key]['returnnum'] = $val[8] == null ? 0 : $val[8];
            $data[$key]['overduenum'] = $val[9] == null ? 0 : $val[9];
            $data[$key]['isdelete'] = (bool)'1';

//            $data[$key]['returnnum'] = $val[8] == null ? 0 : $val[8];
//            $data[$key]['overduenum'] = $val[9] == null ? 0 : $val[9];
            $repayment[$key]['returnnum'] = $val[8] == null ? 0 : $val[8];
            $repayment[$key]['overduenum'] = $val[9] == null ? 0 : $val[9];
            $repayment[$key]['repaymentdate'] = $month;

            //条件为身份证号
            $where['idcard'] = $val[2];
            //根据身份证号查找是否存在
            $repeat = $loaninfo->where($where)->find();
            //如果存在这条记录
            if($repeat != null){
                $data[$key]['lastupdatedate'] = date('Y-m-d H:i:s');
                $result = $loaninfo->where($where)->setField('lastupdatedate',$data[$key]['lastupdatedate']);
                $repayment[$key]['loaninfoid'] = $repeat['loanid'];
                M('repayment')->data($repayment[$key])->add();
            }else{
                $data[$key]['infodate'] = date('Y-m-d H:i:s');
                //返回值为当前记录的ID
                $result = $loaninfo->data($data[$key])->add();
                $repayment[$key]['loaninfoid'] = $result;
                M('repayment')->data($repayment[$key])->add();
            }
            if(!$result){
                $returnData['info'] = '数据异常';
                $returnData['status'] = 0;
                $this->ajaxReturn($returnData);
            }else{
                $number += 1;
            }

        }
        $returnData['info'] = $number.'条记录被影响';
        $returnData['status'] = $number;
        $this->ajaxReturn($returnData);
    }

    public function month(){
        $this->display('month');
    }

    public function getLoanInfo(){
        //接收easyui传递过来的页码和分页大小
        $page = I('post.page');
        $pageSize = I('post.rows');
        //计算当前页码的开始记录数
        $first = $pageSize * ($page -1);
        $loaninfo = M('loaninfo');
        //计算总的记录数
        $total = $loaninfo->count();
        $where = array();
        $where['loaninfo.isdelete'] = 1;
        $loaner = I('post.loaner');
        $loaninstid = I('post.loaninstid');
        if(isset($loaner) && !empty($loaner)){
            $where['loaninfo.loaner'] = array('like',"%{$loaner}%");
        }
        if(isset($loaninstid) && !empty($loaninstid)){
            $where['loaninfo.loaninstid'] = array('like',"%{$loaninstid}%");
        }
        $data = $loaninfo->where($where)->limit($first,$pageSize)->order('loanid desc')->join('institute on loaninfo.loaninstid = institute.instid')->join('loantype on loaninfo.loantypeid = loantype.typeid')->join('school on loaninfo.schoolid = school.id')->field('loaninfo.loanid,institute.instname,loaninfo.loaner,loaninfo.idcard,school.name,loaninfo.loanmoney,loantype.typename,loaninfo.loandate,loantype.typeinterval,loaninfo.returnnum,loaninfo.overduenum,loaninfo.infodate,loaninfo.lastupdatedate')->select();

        $arr = array();
        foreach($data as $key=>$val){
            $arr[$key]['loanid'] = $val['loanid'];
            $arr[$key]['loaninstid'] = $val['instname'];
            $arr[$key]['loaner'] = $val['loaner'];
            $arr[$key]['idcard'] = $val['idcard'];
            $arr[$key]['schoolid'] = $val['name'];
            $arr[$key]['loanmoney'] = $val['loanmoney'];
            $arr[$key]['loantypeid'] = $val['typename'];
            $arr[$key]['loandate'] = $val['loandate'];
            $arr[$key]['typeinterval'] = $val['typeinterval'];
            $arr[$key]['returnnum'] = $val['returnnum'];
            $arr[$key]['overduenum'] = $val['overduenum'];
//            $arr[$key]['returnnum'] = M('repayment')->where("loaninfoid = {$val['loanid']}")->sum('returnnum');
//            $arr[$key]['overduenum'] = M('repayment')->where("loaninfoid = {$val['loanid']}")->sum('overduenum');
            $arr[$key]['infodate'] = $val['infodate'];
            $arr[$key]['lastupdatedate'] = $val['lastupdatedate'];
        }
        //返回给easyui的数据
        $returnData['total'] = $total;
        $returnData['rows'] = $arr;
        $this->ajaxReturn($returnData,'JSON');
    }

    public function add(){
        $instData = M('institute')->select();
        $this->assign('instData',$instData);
        $schoolData = M('school')->where('isdelete = 1')->select();
        $this->assign('schoolData',$schoolData);
        $loantypeData = M('loantype')->select();
        $this->assign('loantypeData',$loantypeData);
        $this->display('add');
    }

    public function doadd(){
        $data = I('post.');
        $where['idcard'] = $data['idcard'];
        $loaninfo = M('loaninfo');
        $res = $loaninfo->where($where)->find();
        if($res){
            $this->ajaxReturn(0);
        }else{
            $data['infodate'] = date('Y-m-d H:i:s');
            $result = $loaninfo->add($data);
            $this->ajaxReturn(1);
        }

    }

    public function detailed(){
        $where['loaninfoid'] = I('post.id');
        $this->display('detailed');
    }

    public function edit(){
        echo date('Y-m-d H:i:s');
    }


    public function delete(){
        $where['loanid'] = I('post.id');
        $result = M('loaninfo')->where($where)->setField('isdelete',(bool)'0');
        if($result){
            $this->ajaxReturn(1);
        }else {
            $this->ajaxReturn(0);
        }
    }



}