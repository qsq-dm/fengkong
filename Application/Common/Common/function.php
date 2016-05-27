<?php

//所有的公共函数都在这里


// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
/**
 * @param $code
 * @param string $id
 * @return bool
 */
function check_verify($code, $id = ''){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}


/**
 * 上传插件自带上传函数
 */
function upload(){
    @set_time_limit(5 * 60);
    //$targetDir = ini_get("upload_tmp_dir").DIRECTORY_SEPARATOR . "plupload";
    $targetDir = C('UPLOADS').date('Y-m-d');
    $cleanupTargetDir = true;
    $maxFileAge = 5 * 3600;
    if (!file_exists($targetDir)) {
        @mkdir($targetDir);
    }
    if (isset($_REQUEST["name"])) {
        $fileName = $_REQUEST["name"];
    } elseif (!empty($_FILES)) {
        $fileName = $_FILES["file"]["name"];
    } else {
        $fileName = uniqid("file_");
    }
//    $fileName = iconv('UTF-8', 'GB2312', 'aa'.$fileName);//转编码

    $fileName = uniqid().'.'.end(explode('.', $fileName));

    $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    if ($cleanupTargetDir) {
        if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "未能打开临时目录"}, "id" : "id"}');
        }
        while (($file = readdir($dir)) !== false) {
            $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
            if ($tmpfilePath == "{$filePath}.part") {
                continue;
            }
            if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                @unlink($tmpfilePath);
            }
        }
        closedir($dir);
    }
    if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "未能打开输出流"}, "id" : "id"}');
    }
    if (!empty($_FILES)) {
        if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "移动上传文件失败"}, "id" : "id"}');
        }
        if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "未能打开输入流"}, "id" : "id"}');
        }
    } else {
        if (!$in = @fopen("php://input", "rb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "未能打开输入流"}, "id" : "id"}');
        }
    }
    while ($buff = fread($in, 4096)) {
        fwrite($out, $buff);
    }
    @fclose($out);
    @fclose($in);
    if (!$chunks || $chunk == $chunks - 1) {
        rename("{$filePath}.part", $filePath);
    }
//    die('{"filePath":"'.$filePath.'"}');
    die($filePath);
}

/**
 * @param $cates
 * @param int $pid
 * @param int $level
 * @return array
 * 导航使用的菜单生成函数
 */
function get_family($cates ,$pid = 0,$level = 0){
    $arr = array();
    foreach ($cates as $val) {
        if($val['pid']  == $pid){
            $val['level'] = $level+1;
            $val['iconCls'] = "icon-".$val['icon'];
            $val['children'] = get_family($cates,$val['id'],$level+1);
            $arr[] = $val;
        }
    }
    return $arr;
}

/**
 * @param null $var
 * 调试函数
 */
function show($var = null){
    header('content-type:text/html;charset=utf8');
    if(empty($var)){
        echo 'null';
    }elseif(is_array($var) || is_object($var)){
//        echo '<pre>';
        print_r($var);
//        echo '<pre>';
    }else{
        echo $var;
    }
}

function getExcelData($filename,$sheetNum = 0){
    //获取文件名，并转编码
    $filename = iconv('UTF-8', 'GBK', C('UPLOADS').$filename);
    //引入读取excel类文件,这里要是物理路径
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
    $currentSheet = $objPHPExcel->getSheet($sheetNum);
    $allColumn = $currentSheet->getHighestColumn();
    $allRow = $currentSheet->getHighestRow();
    $arr = array();
    $excelData = array();
    //循环读取每个单元格数据，从第二行开始
    for($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++){
        for($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++){
            $addr = $colIndex.$rowIndex;
            $cell = $currentSheet->getCell($addr)->getValue();
//            if($cell == null){
//                continue;
//            }
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

    return $excelData;
}