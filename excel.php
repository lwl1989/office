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
    $sh->getActiveSheet();
    $ch = $sh->getActiveSheet();
    $ch->setCellValue('A1', $title);
    for ($i = 0;$i< (count($result)); $i++) {

        $ch->setCellValue('A'. ($i + 2), $result[$i]);
        //$sh->setActiveSheetIndex($i);
//        $n = ord('A');
//
//        $ch = $sh->getActiveSheet();
//        $k = 0;
//        for($j = $n; $j < ($n+count($result[$i])); $j++) {
//            $ch->setCellValue(chr($j). ($i + 2), $result[$i]);
//            $k++;
//        }
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
        $writer->save( "php://output" );
}else{
?>
    <html>
        <head>
            <title>青蛙专属excel对比</title>
            <meta charset="utf-8">
            <link href="https://cdn.bootcdn.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
            <style type="text/css">
                html {
                    position: relative;
                    min-height: 100%;
                }
                body {
                    margin-bottom: 60px;
                }
                .footer {
                    position: absolute;
                    bottom: 0;  width: 100%;
                    /* Set the fixed height of the footer here */
                    height: 60px;
                    background-color: #f5f5f5;
                }
            </style>
        </head>
        <body>
            <div class="container" >
                <div class="row" style="text-align: center;">
                    <div>
                    <img src="data:image/jpeg;base64,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">
                        <span class="label label-primary" style="position: absolute;top: 335px;text-align: center;">青蛙专属</span>
                    </div>
                </div>
                <div class="row">
                    <form method="post" action="excel.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="exampleInputFile">excel1</label>
                            <input type="file" name="excel1">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputFile">excel2</label>
                            <input type="file" name="excel2">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">列名1</label>
                            <input type="text" class="form-control" name="key1" placeholder="第一个excel的列">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">列名2</label>
                            <input type="text" class="form-control" name="key2" placeholder="第二个excel列">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">对比方式</label>
                            <label class="radio-inline">
                                <input type="radio" name="action" value="1">对比不同
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="action" value="2">对比相同
                            </label>
                        </div>
                        <button type="submit" class="btn btn-default">确定</button>
                    </form>
                </div>
            </div>
            <div class="footer">
                <p style="text-align: center">技术支持：李蠢蠢</p>
            </div>
        </body>
    </html>



<?php
}
?>