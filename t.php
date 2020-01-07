<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/1/2
 * Time: 14:50
 */


/**
 *
 * dbhost = rm-ly250tladaff9r77z.mysql.rds.aliyuncs.com
dbport = 3306
dbuser = mgtj_admin_2018
dbpassword = mgtj123456
dbname = mgtjv3
 */
//utf8mb4
$pdo = new PDO('mysql:dbname=mgtjv3_test;host=rm-ly29w98y58jjy4s7x.mysql.rds.aliyuncs.com;charset=utf8mb4', 'ceshiceshi', 'mgtj123456');
//$pdo = new PDO('mysql:dbname=mgtjv3;host=rm-ly250tladaff9r77z.mysql.rds.aliyuncs.com;charset=utf8mb4', 'mgtj_admin_2018', 'mgtj123456');

$page = 1;
$limit = 2000;
$userCount = [];

while (true) {
    $offset = ($page - 1) * $limit;
    $result = $pdo->query('select * from user_order_info where (status = 1 or status = 6) order by payType limit 2000 offset '. $offset)->fetchAll();
    //->prepare('select * from user_fee_info');
    //$result = $sth->fetchAll();
    if (empty($result)) {
        break;
    }
    $page ++;
    foreach ($result as $value) {
        if (!isset($userCount[$value['userId']])) {
            $userCount[$value['userId']] = [
                'give' => [
                    '2019-07-25 00:00:00' => 0,
                    '2019-10-09 00:00:00' => 0,
                    '2019-11-09 00:00:00' => 0,
                ],
                'used' => 0,
                'ios_input' => 0,
                'android_input' => 0,
                //            'preBalance' => -1
            ];
        }
        //    if ($userCount[$value['userId']]['preBalance'] == -1) {
        //        $userCount[$value['userId']]['preBalance'] = $value['preBalance'];
        //    }
        if ($value['payType'] == 0) {
            continue;
        }
        if ($value['payType'] == 6) {
            if ($value['createTime'] < '2019-07-25 00:00:00') {
                $userCount[$value['userId']]['give']['2019-07-25 00:00:00'] += $value['totalFee'];
            } else if ($value['createTime'] < '2019-10-09 00:00:00') {
                $userCount[$value['userId']]['give']['2019-10-09 00:00:00'] += $value['totalFee'];
            } else {
                $userCount[$value['userId']]['give']['2019-11-09 00:00:00'] += $value['totalFee'];
            }
            //        $userCount[$value['userId']]['give'] += $value['totalFee'];
        } else if ($value['payType'] == 4) {
            $userCount[$value['userId']]['used'] += $value['totalFee'];
        } else {
            if ($value['payType'] == 3) {
                $userCount[$value['userId']]['ios_input'] = $value['totalFee'];
            } else {
                $userCount[$value['userId']]['android_input'] = $value['totalFee'];
            }
        }
    }
}

/**
 * CREATE TABLE `user_wallet` (
 * `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 * `userId` bigint(20) unsigned NOT NULL,
 * `bindId` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '绑定的外部id(活动id、充值流水)',
 * `worth` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
 * `residue` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
 * `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 充值币 6 赠币,预留2 3 4 5为其他类型',
 * `platform` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 安卓 2 ios 3 web ',
 * `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 正常 2 失效 3 过期',
 * `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 * `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 * `expireTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '果冻才有过期时间',
 * PRIMARY KEY (`id`)
 * ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
 */
//foreach ($userCount as $userId => $value) {
//    if ($value['preBalance'] > 0) {
//        $sql = 'insert into user_wallet(`type`,userId,bindId,worth,residue,status) values (1,?,?,?,?,1)';
//        $sth = $pdo->prepare($sql);
//        $sth->execute([$userId, '100000', $value['preBalance'], $value['preBalance']]);
//    }
//}
//订单状态：0-未知，1-微信，2-支付宝，3-ios，4-果冻消费 6-果冻赠币
//foreach ($result as $value) {
//    if ($value['payType'] != 4) {
//        $sql = 'insert into user_wallet(`type`,userId,bindId,worth,residue,platform,status,expireTime) values (?,?,?,?,?,?,?,?)';
//        $sth = $pdo->prepare($sql);
//        $pt = $value['platform'] == 'ios' ? 2 : ($value['platform'] == 'web' ? 3 : 1);
//        $type = $value['payType'] == 6 ? 6 : 1;
//        $expire = $value['payType'] != 6 ? '0000-00-00 00:00:00' : date('Y-m-d', strtotime('+180 days')).' 23:59:59';
//        $residue = $value['totalFee'];
//        $sth->execute([
//            $type,
//            $value['userId'], $value['orderId'], $value['totalFee'], $value['totalFee'], $value['platform'], 1, $expire
//        ]);
//        echo $pdo->lastInsertId() . "\n";
//    }
//}
/**
 * select sum(totalFee) as residue from user_order_info where status = 1 and userId = 335094211743424512 and (payType!=4)
123818.00
112148.00
 */
foreach ($userCount as $userId => $value) {
//    if($userId == '335094211743424512') {
//        var_dump($value);
//    }
//    continue;
    $used = $value['used'];
    $value['orderId'] = '99999';
    if ($value['ios_input'] > 0) {
        $sql = 'insert into user_wallet(`type`,userId,bindId,worth,residue,platform,status,expireTime) values (?,?,?,?,?,?,?,?)';
        $sth = $pdo->prepare($sql);
        $type = 1;
        $expire = '0000-00-00 00:00:00';
        $residue = $value['ios_input'];
        $sth->execute([
            $type,
            $userId, $value['orderId'], $value['ios_input'], $value['ios_input'], 1, 1, $expire
        ]);
    }

    if ($value['android_input'] > 0) {
        $sql = 'insert into user_wallet(`type`,userId,bindId,worth,residue,platform,status,expireTime) values (?,?,?,?,?,?,?,?)';
        $sth = $pdo->prepare($sql);
        $type = 1;
        $expire = '0000-00-00 00:00:00';
        $residue = $value['android_input'];
        $sth->execute([
            $type,
            $userId, $value['orderId'], $value['android_input'], $value['android_input'], 1, 1, $expire
        ]);
    }

    foreach ($value['give'] as $key => $v) {
        if ($v > 0) {
            $sql = 'insert into user_wallet(`type`,userId,bindId,worth,residue,platform,status,expireTime) values (?,?,?,?,?,?,?,?)';
            $sth = $pdo->prepare($sql);
            $type = 6;
            //$expire = date('Y-m-d', strtotime($key.' +179 days')) . ' 23:59:59';
//             '2019-07-25 00:00:00' => 0,
            //                '2019-10-09 00:00:00' => 0,
            //                '2019-11-09 00:00:00' => 0,
            switch ($key) {
                case '2019-07-25 00:00:00':
                    $expire = '2020-01-20 23:59:59';
                    break;
                case '2019-10-09 00:00:00':
                    $expire = '2020-03-31 23:59:59';
                    break;
                case '2019-11-09 00:00:00':
                    $expire = '2020-05-06 23:59:59';
                    break;
            }
            $sth->execute([
                $type,
                $userId, $value['orderId'], $v, $v, 3, 1, $expire
            ]);
        }
    }

    if ($used > 0) {
        $sql = 'select id,residue from user_wallet where userId=? and  status = ? and (type = ? or expireTime >= ?) order by type desc,expireTime asc';
        $sth = $pdo->prepare($sql);
        $sth->execute([
            $userId, 1, 1, date('Y-m-d H:i:s')
        ]);

        $d = $sth->fetchAll();
        foreach ($d as $item) {
            $old = $item['residue'];
            if ($item['residue'] > $value['used']) {
                $item['residue'] -= $value['used'];
                $used = 0;
            } else {
                $item['residue'] = 0;
                $used = $used - $item['residue'];
            }
            $sql = 'update user_wallet set residue = ? where id = ?';
            $sth = $pdo->prepare($sql);
            $e = $sth->execute([
                $item['residue'], $item['id']
            ]);
            if ($used == 0) {
                break;
            }
        }
    }

}