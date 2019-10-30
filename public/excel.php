<?php
require '../vendor/autoload.php';
ini_set('post_max_size','1024M');
ini_set('upload_max_filesize','1024M');
use PhpOffice\PhpSpreadsheet\Spreadsheet;

error_reporting(E_ALL);

function toString($v) : string {
    return trim($v.' ');
}

if(!empty($_POST)) {

    if(empty($_FILES['excel1']['tmp_name']) or !isset($_FILES['excel2']['tmp_name']) or empty($_POST['key1']) or empty($_POST['key2'])) {
        echo '<script>alert("请完善表单资料"); window.history.go(-1);</script>';exit();
    }

    $f1 = $_FILES['excel1']['tmp_name'];
    $f2 =$_FILES['excel2']['tmp_name'];
    $k1 = $_POST['key1'];
    $k2 = $_POST['key2'];

    $excel1 = \PhpOffice\PhpSpreadsheet\IOFactory::load($f1);
    $excel2 = \PhpOffice\PhpSpreadsheet\IOFactory::load($f2);
    //      echo '<pre>';
    $data1 = $excel1->getActiveSheet()->toArray(null, true, true, true);
    $data2 = $excel2->getActiveSheet()->toArray(null, true, true, true);

    $ds1 = array_column(array_filter($data1, function ($v) use($k1){
        if(is_null($v[$k1])) {
            return false;
        }
        if(empty($v[$k1])) {
            return false;
        }
        if(is_string($v[$k1])) {
            $v[$k1] = trim($v[$k1]);
        }
        return true;
    }), $k1);
    $ds2 = array_column(array_filter($data2, function ($v) use($k2){
        if(is_null($v[$k2])) {
            return false;
        }
        if(empty($v[$k2])) {
            return false;
        }
        return true;
    }),  $k2);
    $ds1 = array_map('toString', $ds1);
    $ds2 = array_map('toString', $ds2);
    $ds1 = array_unique($ds1);
    $ds2 = array_unique($ds2);

    if (count($ds2) > count($ds1)) {
        $t = $ds1;
        $ds1 = $ds2;
        $ds2 = $t;
    }
    $action = $_POST['action'] ?: 1;
    if($action == 1) {
        $result = array_diff($ds1, $ds2);
    }else{
        $result = array_intersect($ds1, $ds2);
    }
    if(is_null($result)) {
        $result = [];
    }
    $result = array_values($result);

    $sh = new Spreadsheet();
    $sh->createSheet();
    $sh->setActiveSheetIndex(0);
    $title = $action == 1 ? '不同的' : '相同的';
    $ch = $sh->getActiveSheet();
    $ch->setCellValue('A1', $title);
    for ($i = 0;$i< (count($result)); $i++) {

        $ch->setCellValue('A'. ($i + 2), $result[$i]);
    }

        $filename = $title.'.xlsx';
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($sh, 'Xlsx');
        $writer->setIncludeCharts(true);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $writer->save( "php://output");
}else{
    echo '无效处理，即将自动跳转';
    echo '<script>
        setTimeout(function() {
                  location.href = "index.html"
        }, 2000);
        </script>';
}