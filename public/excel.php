<?php
require '../vendor/autoload.php';
ini_set('post_max_size', '1024M');
ini_set('upload_max_filesize', '1024M');

use PhpOffice\PhpSpreadsheet\Spreadsheet;

error_reporting(E_ALL);

function toString($v): string
{
    if (strpos($v, '106') === 0) {
        $v = '1' . substr($v, 3);
    }
    return trim($v . ' ');
}

if (!empty($_POST)) {

    if (empty($_FILES['excel1']['tmp_name']) or !isset($_FILES['excel2']['tmp_name']) or empty($_POST['key1']) or empty($_POST['key2'])) {
        echo '<script>alert("请完善表单资料"); window.history.go(-1);</script>';
        exit();
    }

    $f1 = $_FILES['excel1']['tmp_name'];
    $f2 = $_FILES['excel2']['tmp_name'];
    $k1 = $_POST['key1'];
    $k2 = $_POST['key2'];

    $excel1 = \PhpOffice\PhpSpreadsheet\IOFactory::load($f1);
    $excel2 = \PhpOffice\PhpSpreadsheet\IOFactory::load($f2);
    //      echo '<pre>';
    $data1 = $excel1->getActiveSheet()->toArray(null, true, true, true);
    $data2 = $excel2->getActiveSheet()->toArray(null, true, true, true);
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
    $data2 = array_filter($data2, function ($v) use ($k2) {
        if (is_null($v[$k2])) {
            return false;
        }
        if (empty($v[$k2])) {
            return false;
        }
        return true;
    });

    $data1 = array_map(function ($v) use ($k1) {
        $v[chr(ord('A') + count($v))] = $v[$k1];
        $v[$k1] = toString($v[$k1]);
        return $v;
    }, $data1);
    $data2 = array_map(function ($v) use ($k2) {
        $v[chr(ord('A') + count($v))] = $v[$k2];
        $v[$k2] = toString($v[$k2]);
        return $v;
    }, $data2);

    $action = $_POST['action'] ?? 1;
    //    if ($action == 1) {
    //        $result = array_diff($ds1, $ds2);
    //    } else {
    //        $result = array_intersect($ds1, $ds2);
    //    }
    $data2 = array_column($data2, null, $k2);
    $result = [];
    foreach ($data1 as $index => $d1) {
        $exists = false;
        //        foreach ($data2 as $index2 => $d2) {
        //            if($d1[$k1] == $d2[$k2]) {
        //                $exists = true;
        //                break;
        //            }
        //        }

        if (array_key_exists($d1[$k1], $data2)) {
            $exists = true;
        }
        if ($action == 1) {
            if (!$exists) {
                $result[] = [$d1];
            }
        } else {
            if ($exists) {
                $result[] = [$d1, $data2[$d1[$k1]]];
            }
        }
    }

    $attach = $_POST['attach'] ?? 1;
    switch ($attach) {
        case 1: //只返回结果
            $t = [];
            foreach ($result as $value) {
                $t[] = $value[0][$k1];
            }
            $result = $t;
            break;
        case 2: //附带表信息
            $t = [];
            foreach ($result as $value) {
                $t[] = $value[0];
            }
            $result = $t;
            break;
        case 3: //附带表具体某一列信息
            $t = [];
            $attachKey1 = $_POST['attach_key1'] ?? '';
            $attachKey2 = $_POST['attach_key2'] ?? '';
            foreach ($result as $value) {
                if ($attachKey1 == '') {
                    $t[] = $value[0];
                } else {
                    $t[] = [
                        'A' => $value[0][$k1],
                        'B' => $value[0][$attachKey1],
                        'C' => $value[1][$attachKey2]
                    ];
                }
            }
            $result = $t;
            break;
        default:
            $result = [];
    }
    $sh = new Spreadsheet();
    $sh->createSheet();
    $sh->setActiveSheetIndex(0);
    $title = $action == 1 ? '不同的' : '相同的';
    $ch = $sh->getActiveSheet();
    //    $ch->setCellValue('A1', $title);
    for ($i = 0; $i < (count($result)); $i++) {
        if (is_array($result[$i])) {
            $start = 'A';
            foreach ($result[$i] as $value) {
                //var_dump($start . ($i + 2));
                $ch->setCellValue($start . ($i + 2), $value);
                $start = chr(ord($start) + 1);
            }
        } else {
            $ch->setCellValue('A' . ($i + 2), $result[$i]);
        }
    }
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
                  location.href = "index.html"
        }, 2000);
        </script>';
}