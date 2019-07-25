<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2019/6/18
 * Time: 14:53
 */


require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$excel1 = \PhpOffice\PhpSpreadsheet\IOFactory::load('/Users/limars/Desktop/到期卡.xls');
$excel2 = \PhpOffice\PhpSpreadsheet\IOFactory::load('/Users/limars/Desktop/注销卡.xls');


$data1 = $excel1->getActiveSheet()->toArray(null, true, true, true);
$data2 = $excel2->getActiveSheet()->toArray(null, true, true, true);

//$db = new PDO('mysql:dbname=test;host=127.0.0.1;charset=utf8','root', 'sa');
//foreach ($data1 as $item) {
//    $stmt = $db->prepare("INSERT INTO t1 (card) VALUES(?)");
//    $stmt->execute( array($item['A']));
//}
//foreach ($data2 as $item) {
//    $stmt = $db->prepare("INSERT INTO t2 (card) VALUES(?)");
//    $stmt->execute( array($item['A']));
//}

$data1 = array_column($data1, 'A');
$data2 = array_column($data2, 'A');

//var_dump($data1, $data2);
$i = $j = 0;
foreach($data1 as $d1) {
	var_dump($d1, in_array($d1, $data2));
	if(!in_array($d1, $data2)) {
		$i ++;
		$result[] = $d1;	
	}else{
		$j ++;	
	}	
}
echo $i.PHP_EOL;
echo $j.PHP_EOL;
exit();
//var_dump(count($data1)  , count($data2));
//if(count($data1)  < count($data2)) {
//    $dataT = $data1;
//    $data1 = $data2;
//    $data2 = $dataT;
//}
//$data1 = array_column($data1, null, 'D');
//$data2 = array_column($data2, null, 'D');

$result = [];
//foreach ($data1 as $id => $value) {
//    $id1 = '106'.substr($id, 1);

//    if(isset($data2[$id])) {
//        continue;
//    }
//    if(isset($data2[$id1])) {
//        continue;
//    }
//    $result[] = $value;
//}

$sh = new Spreadsheet();
$sh->createSheet();
$sh->setActiveSheetIndex(1);
$sh->getActiveSheet()->setCellValue('A1', '不同卡')
//   ->setCellValue('C1', '总金额')
//    ->setCellValue('D1', '用户号码')
//    ->setCellValue('E1', '用户名称')
//    ->setCellValue('F1', '金额');
;
for ($i = 0;$i< (count($result)); $i++) {
    //$sh->setActiveSheetIndex($i);
    
    $sh->getActiveSheet()
        ->setCellValue('A'.($i+2), $result[$i])
    ;
}
$filename = '/Users/limars/Desktop/不同的.xlsx';
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($sh, 'Xlsx');
$writer->setIncludeCharts(true);
$callStartTime = microtime(true);
$writer->save($filename);

//$helper->logWrite($writer, $filename, $callStartTime);
