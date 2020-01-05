<?php
require '../vendor/autoload.php';
ini_set('post_max_size', '1024M');
ini_set('upload_max_filesize', '1024M');

use PhpOffice\PhpSpreadsheet\Spreadsheet;

error_reporting(E_ALL);

function toString($v): string
{
    //    if (strpos($v, '106') === 0) {
    //        $v = '1' . substr($v, 3);
    //    }
    return trim($v . ' ');
}

if (!empty($_FILES)) {

    if (empty($_FILES['excel1']['tmp_name'])) {
        echo '<script>alert("请完善表单资料"); window.history.go(-1);</script>';
        exit();
    }

    $f1 = $_FILES['excel1']['tmp_name'];

    $excel1 = \PhpOffice\PhpSpreadsheet\IOFactory::load($f1);

    //$data1 = $excel1->getActiveSheet()->toArray(null, true, true, true);
    $data = [];

    $AllSheets = $excel1->getAllSheets();
    foreach ($AllSheets as $index => $sheet) {
        $data[$index] = [
            'value' => $sheet->toArray(),
            'name' => $sheet->getTitle()
        ];
    }
    $keys = ['1', '0', '3', '0'];
    foreach ($data as $index => $datum) {
        $data1 = $datum['value'];
        $k1 = $keys[$index];
        $data1 = array_filter($data1, function ($v) use ($k1) {
            if (is_null($v[$k1])) {
                return false;
            }
            if (empty($v[$k1])) {
                return false;
            }
            if (is_string($v[$k1])) {
                $v[$k1] = trim($v[$k1]);
            }
            return true;
        });
        $data1 = array_map(function ($v) use ($k1) {
//            $v[chr(ord('A') + count($v))] = $v[$k1];
//            $v[$k1] = toString($v[$k1]);
            return $v;
        }, $data1);
        $data[$index] = [
            'value' => $data1,
            'name' => $datum['name']
        ];
    }

    $result = [];

    $realData = $data[0]['value'];
    $realIndex = $keys[0];

    foreach ($realData as $key => &$value) {
        if ($value[3] == '供货来源') {
            continue;
        }

        $value[6] = 'null';
        foreach ($data as $index => $datum) {
            if ($index == 0) {
                continue;
            }
            switch ($index) {
                case '1':
                    if($value[6] == 'null') {
                        $real = array_column($datum['value'], '0');
                        if (in_array($value['1'], $real)) {

                            if ($value['3'] != '城西移动') {
                                $value['6'] = '城西移动';
                            } else {
                                $value['6'] = '';
                            }
                        } else {
                            if ($value['3'] == '城西移动') {
                                $value['6'] = '未知供应商';
                            } else {
                                $value['6'] = '';
                            }
                        }
                    }
                    break;
                case '2':
//                    if(isset($value['6'])) {
//                        continue;
//                    }
                    if($value[6] == 'null') {
                        $real = array_column($datum['value'], '3');
                        if (in_array($value['1'], $real)) {
                            if ($value['3'] != '外协移动') {
                                $value['6'] = '外协移动';
                            } else {
                                $value['6'] = '';
                            }
                        } else {
                            if ($value['3'] == '外协移动') {
                                $value['6'] = '未知供应商';
                            } else {
                                $value['6'] = '';
                            }
                        }
                    }
                    break;
                case '3':
                    $real = array_column($datum['value'], '1', '0');
                    if (array_key_exists($value['1'], $real)) {
                        if ($value[0] == $real[$value[1]]) {
                            $value['7'] = '';
                        } else {
                            $value['7'] = $real[$value[1]];

                        }
                    }
            }
        }
        unset($value);
    }
    $data[0]['value'] = $realData;
    $sh = new Spreadsheet();
    $title = $_FILES['excel1']['name'] . ' - 矫正 - ' . date('Y-m-d H:i:s');

    foreach ($data as $index => $datum) {
        $sh->createSheet();
        $sh->setActiveSheetIndex($index);
        $ch = $sh->getActiveSheet();
        $ch->setTitle($datum['name']);
        $result = $datum['value'];

        for ($i = 0; $i < (count($result)); $i++) {
            $start = 'A';
            foreach ($result[$i] as $i1 => $value) {
                $ch->setCellValue($start . ($i + 1), $value);
                if($index == 0) {
                    if($i1 > 5) {
                        if($value != '') {
                            $ch->getStyle($start . ($i + 1))->getFont()->getColor()->setRGB('FF0000');
                        }
                    }
                }
                $start = chr(ord($start) + 1);
            }
        }
    }
//    $sh->createSheet();
    //    $sh->setActiveSheetIndex(0);
    //    $ch = $sh->getActiveSheet();
    //    //    $ch->setCellValue('A1', $title);
    //    for ($i = 0; $i < (count($result)); $i++) {
    //        if (is_array($result[$i])) {
    //            $start = 'A';
    //            foreach ($result[$i] as $value) {
    //                //var_dump($start . ($i + 2));
    //                $ch->setCellValue($start . ($i + 2), $value);
    //                $start = chr(ord($start) + 1);
    //            }
    //        } else {
    //            $ch->setCellValue('A' . ($i + 2), $result[$i]);
    //        }
    //    }
    //    var_dump($result);
    //    exit();
    //    $ds1 = array_column(array_filter($data1, function ($v) use ($k1) {
    //        if (is_null($v[$k1])) {
    //            return false;
    //        }
    //        if (empty($v[$k1])) {
    //            return false;
    //        }
    //        if (is_string($v[$k1])) {
    //            $v[$k1] = trim($v[$k1]);
    //        }
    //        return true;
    //    }), $k1);
    //    $ds2 = array_column(array_filter($data2, function ($v) use ($k2) {
    //        if (is_null($v[$k2])) {
    //            return false;
    //        }
    //        if (empty($v[$k2])) {
    //            return false;
    //        }
    //        return true;
    //    }), $k2);
    //
    //    $ds1 = array_map('toString', $ds1);
    //    $ds2 = array_map('toString', $ds2);
    //    $ds1 = array_unique($ds1);
    //    $ds2 = array_unique($ds2);
    //
    //    if (count($ds2) > count($ds1)) {
    //        $t = $ds1;
    //        $ds1 = $ds2;
    //        $ds2 = $t;
    //    }
    //    $action = $_POST['action'] ?: 1;
    //    if ($action == 1) {
    //        $result = array_diff($ds1, $ds2);
    //    } else {
    //        $result = array_intersect($ds1, $ds2);
    //    }
    //    if (is_null($result)) {
    //        $result = [];
    //    }
    //    $result = array_values($result);

    //    $sh = new Spreadsheet();
    //    $sh->createSheet();
    //    $sh->setActiveSheetIndex(0);
    //    $title = $action == 1 ? '不同的' : '相同的';
    //    $ch = $sh->getActiveSheet();
    //    $ch->setCellValue('A1', $title);
    //    for ($i = 0; $i < (count($result)); $i++) {
    //
    //        $ch->setCellValue('A' . ($i + 2), $result[$i]);
    //    }

    $filename = $title . '.xlsx';
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($sh, 'Xlsx');
    $writer->setIncludeCharts(true);
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header('Content-Disposition:inline;filename="' . $filename . '"');
    header("Content-Transfer-Encoding: binary");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");
    $writer->save("php://output");
} else {
    echo '无效处理，即将自动跳转';
    echo '<script>
        setTimeout(function() {
                  location.href = "index1.html"
        }, 2000);
        </script>';
}