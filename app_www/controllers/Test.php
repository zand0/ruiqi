<?php

/**
 * test方法
 */
class TestController extends \Com\Controller\Common {
    public function indextttttttAction(){
        
        
        /*$listVal = array();
        
        $where['bottle_data'] = array('neq', '');
        $kehu_data = LibF::M('kehu')->where($where)->select();
        
        $date = date('Ymd');
        $i = 1;
        if(!empty($kehu_data)){
            foreach($kehu_data as $value){
                if (!empty($value['bottle_data'])) {
                    $bottle_data = json_decode($value['bottle_data'], TRUE);
                    if(!empty($bottle_data)){
                        foreach ($bottle_data as $val) {
                            if ($val['good_num'] > 0) {
                                for ($ii = 1; $ii <= $val['good_num']; $ii++) {
                                    $dVal['promotionsn'] = $date . $i . $value['kid'];
                                    $dVal['kid'] = $value['kid'];
                                    $dVal['title'] = $val['good_name'] . '优惠券';
                                    $dVal['type'] = 1;
                                    $dVal['good_type'] = $val['good_kind'];
                                    $dVal['price'] = floatval($val['good_price']);
                                    $dVal['num'] = 1;
                                    $dVal['money'] = floatval($val['good_price']);
                                    $dVal['time_created'] = $value['ctime'];90901257

                                    $listVal[] = $dVal;
                                    $i++;
                                }
                            }
                        }
                    }
                }
            }
            if(!empty($listVal)){
               echo LibF::M('promotions_user')->uinsertAll($listVal);
            }
        }*/

        $bottleArr = array(
            'PS901098','PS901873','PS901159','PS901521','PS902130','PS901095','PS901103','PS901344','PS901093',
            'PS901510','PS901269','PS901839','PS901210','PS901289','PS901257','PS901742','PS901554','PS901913',
            'PS901232','PS901226'
        );
        
        $sn = 'ckd16100198495110';
        $shop_id = 7;
        
        $time = time();
        $isql = "INSERT INTO rq_store_inventory(sn,number,xinpian,bar_code,type,shop_id,is_open,status,is_use,time_created) VALUES ";
        $bsql = "INSERT INTO rq_bottle_log(number,xinpian,bar_code,status,type,property,shop_id,shop_time,time_created) VALUES ";
        foreach($bottleArr as $bVal){
            $isql .= "('".$sn."','".$bVal."','".$bVal."','".$bVal."',2,'".$shop_id."',1,1,1,".$time."),";
            $bsql .="('".$bVal."','".$bVal."','".$bVal."',1,1,0,".$shop_id.",".$time.",".$time."),";
        }
        
        echo $isql;
        echo '<br />';
        echo $bsql;
        exit;
        
        $bottleModel = new BottleModel();
        $bottleObject = $bottleModel->bottleOData();
        
        
        $where['sn'] = $sn;
        $where['shop_id'] = $shop_id;
        $where['id'] = array('egt',6590);
        
        $data = LibF::M('store_inventory')->where($where)->select();

        $iVal = '';
        
        $i = 0;
        foreach ($data as $value) {
            $valtt['number'] = $value['number'];
            $valtt['xinpian'] = $value['xinpian'];
            $valtt['bar_code'] = $value['bar_code'];
            $valtt['status'] = 1;
            $valtt['type'] = 1;
            $valtt['property'] = 0;
            $valtt['shop_id'] = $shop_id;
            $valtt['time_created'] = time();
            $iVal[] = $valtt;
            $i++;
        }
        print_r($iVal);

        $status = LibF::M('bottle_log')->uinsertAll($iVal);
        
        
        exit;
        
        
        
        
        
        
        
        
        $i = 0;
        
        $newbottle = array();
        $oldbottle = array();
        
        $iVal = '';
        
        foreach($bottleArr as $value){

            $val['sn'] = $sn;
            $val['number'] = $value;
            $val['xinpian'] = isset($bottleObject['number'][$value]) ? $bottleObject['number'][$value]['xinpian'] : '';
            $val['bar_code'] = $value;
            $val['type'] = isset($bottleObject['number'][$value]) ? $bottleObject['number'][$value]['type'] : '';
            $val['shop_id'] = $shop_id;
            $val['is_open'] = 1;
            $val['status'] = 1;
            $val['is_use'] = 1;
            if(!empty($val['xinpian'])){
                $where['number'] = $val['number'];
                $where['sn'] = $sn;
                $where['shop_id'] = $shop_id;
                $d = LibF::M('store_inventory')->where($where)->find();
                if(empty($d)){
                    $newbottle[] = $val['number'];
                    
                    LibF::M('store_inventory')->add($val);
                    
                    
                    $valtt['number'] = $val['number'];
                    $valtt['xinpian'] = $val['xinpian'];
                    $valtt['bar_code'] = $val['bar_code'];
                    $valtt['status'] = 1;
                    $valtt['type'] = 1;
                    $valtt['property'] = 0;
                    $valtt['shop_id'] = $shop_id;
                    $valtt['time_created'] = time();
                    $iVal[] = $valtt;

                    $i++;
                }else{
                  $oldbottle[] = $val['number'];  
                }
            }else{
                $nobottle[] = $val['number'];
            }
        }
        
        //$status = LibF::M('bottle_log')->uinsertAll($iVal);
        
        echo $i;
        exit;
        
        
        
        
        
        
        
        $where['sn'] = $sn;
        $where['shop_id'] = $shop_id;
        $where['id'] = array('egt',6303);
        
        $data = LibF::M('store_inventory')->where($where)->select();

        $iVal = '';
        
        $i = 0;
        foreach ($data as $value) {
            $valtt['number'] = $value['number'];
            $valtt['xinpian'] = $value['xinpian'];
            $valtt['bar_code'] = $value['bar_code'];
            $valtt['status'] = 1;
            $valtt['type'] = 1;
            $valtt['property'] = 0;
            $valtt['shop_id'] = $shop_id;
            $valtt['time_created'] = time();
            $iVal[] = $valtt;
            $i++;
        }
        print_r($iVal);

        $status = LibF::M('bottle_log')->uinsertAll($iVal);
echo $i;

        exit;
        
        echo $i;exit;
        
    }

    public function indexAction() {
        $numberArr = array('PS900519','PS900574','PS900606','PS900624','PS900633','PS900653','PS900750','PS900774','PS900789','PS900812','PS900859','PS900974','PS900982','PS900993');
        
        $where['number'] = array('in', $numberArr);
        $data = LibF::M('kehu_inventory')->where($where)->group('number')->order('number asc')->select();
        print_r($data);exit;


        exit;
        
        $gp_id = 290;
        $gp_eid = 1289;

        $where['gp_id'] = array('egt', $gp_id);
        $where['gp_id'] = array('elt', $gp_eid);
        $where['type'] = 2;
        $data = LibF::M('bottle')->where($where)->select();
        $numberlist = array();

        foreach ($data as $value) {
            $numberlist[] = $value['number'];
        }

        $w['number'] = array('in', $numberlist);
        $bottlelog = LibF::M('bottle_log')->where($w)->group('number')->order('number ASC')->select();
        
        $bArr = array();
        foreach($bottlelog as $val){
            $bArr[] = $val['number'];
        }
        print_r($bArr);exit;

        exit;
        $i = 902500;
        
        $data = array();
        for ($i = 902501; $i <= 903000; $i++) {
            $value = array();
            $value['number'] = 'PS' . $i;
            $value['xinpian'] = $value['number'];
            $value['bar_code'] = $value['number'];
            $value['type'] = 2;
            $value['status'] = 1;

            $data[] = $value;
        }
        LibF::M('bottle')->uinsertAll($data);
        
        
        
        
        exit;
        
        $list = '{"discount":"0.00","payment_type":"1","subject":"\u5145\u503c","trade_no":"2016110821001004450235857892","buyer_email":"550270176@qq.com","gmt_create":"2016-11-08 15:30:19","notify_type":"trade_status_sync","quantity":"1","out_trade_no":"cz16110854101485","seller_id":"2088221957053545","notify_time":"2016-11-08 15:30:21","body":"\u7528\u6237\u5145\u503c","trade_status":"TRADE_SUCCESS","is_total_fee_adjust":"N","total_fee":"0.01","gmt_payment":"2016-11-08 15:30:20","seller_email":"paisiranqi1@126.com","price":"0.01","buyer_id":"2088112411413453","notify_id":"8ca1ffcf9ea518d33dc070223e6306bjh2","use_coupon":"N","sign_type":"RSA","sign":"m\/ott9SUFeWAukRtzeh1yjT0o9ZA75p7tSLnYEsLOTVmSTJzypd3IBsgGK4GEU2\/YahkT8Ka4eGKNml1v9LDIODx\/hUynwJzvXjWs6EUpfQBS4njxCqvxsTEqdfQOvyjLGJqqi7eXEX\/oBPmbbc5hdQcI+3RjFT1lqYLIN6GnGQ="}';
        $data = json_decode($list,true);
        print_r($data);exit;
        
        exit;
        
        $i = 50000;
        
        $data = array();
        for ($i = 529977; $i <= 540000; $i++) {
            $value = array();
            $value['number'] = 'ST' . $i;
            $value['xinpian'] = $value['number'];
            $value['bar_code'] = $value['number'];
            $value['type'] = 4;
            $value['status'] = 1;

            $data[] = $value;
        }
        LibF::M('bottle')->uinsertAll($data);

        echo $i;

        exit;
    }
    
    
    public function indexxxxAction() {

        echo strtotime('2016-11-03');
        exit;

        $value['productday'] = '2016-11-03';
        $value['yearnum'] = 10;

        echo date('Y-m-d', strtotime($value['productday'] . "+" . $value['yearnum'] . " year"));    //有效期
        exit;

        $password = 'rq_xlwtx86_st';

        echo md5(md5($password) . 'FAIHRTXN');
        exit;

        $listTest = '{"return_code":"SUCCESS","return_msg":"OK","appid":"wx40dca092205d606b","mch_id":"1358732102","sub_mch_id":[],"nonce_str":"ZNpezV5AOytvxkB2","sign":"77CF776FC42BC33B53D2370252E894E0","result_code":"FAIL","err_code":"USERPAYING","err_code_des":"\u652f\u4ed8\u9501\u5b9a\u4e2d\uff0c\u6263\u6b3e\u548c\u64a4\u9500\u5efa\u8bae\u95f4\u969410\u79d2\u4ee5\u4e0a"}';

        $listTest = '{"return_code":"SUCCESS","return_msg":"OK","appid":"wx40dca092205d606b","mch_id":"1358732102","sub_mch_id":[],"nonce_str":"0uLSTwDWD0TKqwjU","sign":"93546C258BC622F9EFB0A3866F6798B9","result_code":"FAIL","err_code":"ORDERPAID","err_code_des":"order paid"}';

        $listTest = '{"return_code":"SUCCESS","return_msg":"OK","appid":"wx40dca092205d606b","mch_id":"1358732102","sub_mch_id":[],"nonce_str":"zYt12qmluGOn9LFL","sign":"302AFE3C25CFAD9212AABBFADDE66C70","result_code":"FAIL","err_code":"ORDERPAID","err_code_des":"order paid"}';

        $listTest = '{"return_code":"SUCCESS","return_msg":"OK","appid":"wx40dca092205d606b","mch_id":"1358732102","sub_mch_id":[],"nonce_str":"2P5TV8i935LAiNPy","sign":"A88B45F262B75062579F248D1ABC63EF","result_code":"FAIL","err_code":"ORDERPAID","err_code_des":"order paid"}';

        $listTest = '{"return_code":"FAIL","return_msg":"appid\u53c2\u6570\u957f\u5ea6\u6709\u8bef"}';
        $listArr = json_decode($listTest, TRUE);
        print_r($listArr);
        exit;

        exit;

        /* echo strtotime(date('Y-m-d'));
          exit;

          $cardArr = array('0F000E3A','0F000D45','0F000EDC','0F000DF0','0F000E32','0F000D4E','0F000DE0','0F000D5A','0F000D40','0F000DF5','0F000DF7','0F000E27','0F000DEB','0F000DED','0F000D4D','0F000DEC','0F000D44','0F000D47','0F000E3F','0F000DE2','0F000DFC','0F000D4C','0F000E22','0F000D5B','0F000ED5','0F000E2A','0F000D34','0F000ED7','0F000D55','0F000D50','0F000E39','0F000E2F','0F000E3E','0F000E3B','0F000EDB','0F000E33','0F000ED6','0F000D4F','0F000DF9','0F000DE7','0F000E21','0F000ED8','0F000EDD','0F000E31','0F000D4A','0F000D5C','0F000E3C','0F000EDA','0F000DE5','0F000D58','0F000D57','0F000DF8','0F000DE8','0F000ED4','0F000D43','0F000DF3','0F000E2B','0F000DE4','0F000D5D','0F000D48','0F000E23','0F000DF6','0F000DEE','0F000D52','0F000E2C','0F000DFE','0F000D51','0F000E29','0F000DF1','0F000D49','0F000E20','0F000D41','0F000E2D','0F000E24','0F000DE3','0F000ED3','0F000DF4','0F000E25','0F000D4B');

          $time = time();
          $sql = 'INSERT INTO {{1112112_timecard_user}} (aa_user_sn,card_sn,entry_time) VALUES ';

          $usql = " SELECT aa_user_sn FROM {{a_school_user}} WHERE aa_school_sn = 1112112 AND urole = 'teacher' ";
          $uresult = Yii::app()->db->createCommand($usql);
          $udata = $uresult->queryAll();
          if (!empty($udata)) {
          $i = 0;
          foreach ($udata as $value) {
          $csql = "SELECT card_sn FROM {{1112112_timecard_user}} WHERE aa_user_sn = ".$value['aa_user_sn'];
          $cresult = Yii::app()->db->createCommand($csql);
          $crow = $cresult->queryRow();
          if(empty($crow)){
          $sql .= "(".$value['aa_user_sn'].",'".$cardArr[$i]."'),";
          $i++;
          }
          }
          $sqllist = trim(',', $sql);
          $s = Yii::app()->createCommand($sqllist);
          echo $s->execute();
          }
          echo 'aa';
          exit; */




        $upload_path = define('ROOT_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/webroot_www/statics/upload/file/');

        $file_name_list = 'test';  //得到唯一名称
        if (isset($_FILES['Filedata'])) {
            $ext = pathinfo($_FILES['Filedata']['name']);
            $ext = strtolower($ext['extension']);
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $upload_path = ROOT_PATH;
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            $photo_name = time();

            $new_file_name = $photo_name . '.' . $ext;
            $targetFile = $upload_path . $new_file_name;
            move_uploaded_file($tempFile, $targetFile);

            if (file_exists($targetFile)) {

                $extend = pathinfo($targetFile);
                $extend = strtolower($extend["extension"]);
                if ($extend == 'xlsx') {
                    $type = 'Excel2007';
                } else {
                    $type = 'Excel5';
                }

                $xlsReader = PHPExcel_IOFactory::createReader($type);
                $xlsReader->setReadDataOnly(true);
                $xlsReader->setLoadSheetsOnly(true);
                $Sheets = $xlsReader->load($targetFile);

                //$return_data = $Sheets->getSheet(0)->toArray(); //读取第一个工作表(注意编号从0开始) 第一种方式
                //第二种方式读取xls
                $Sheet = $Sheets->getSheet(0);
                $allColumn = $Sheet->getHighestColumn();
                $allRow = $Sheet->getHighestRow();

                $return_data = array();
                if (!empty($Sheet)) {
                    $currentRow = 1;

                    for ($currentRow; $currentRow <= $allRow; $currentRow++) {
                        $cell_values = array();
                        for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                            $address = $currentColumn . $currentRow; // 单元格坐标
                            $cell_values[] = trim($Sheet->getCell($address)->getFormattedValue());
                        }
                        $return_data[] = $cell_values;
                    }
                }

                $time = time();
                $sql = 'INSERT INTO {{1112112_timecard_user}} (aa_user_sn,card_sn,entry_time) VALUES ';
                if (!empty($return_data)) {
                    foreach ($return_data as $key => $value) {
                        if (!empty($value[0])) {
                            $card_sn = preg_replace('/[\'\s| ]+/', '', $value[3]);
                            $aa_user_sn = preg_replace('/[\'\s| ]+/', '', $value[4]);
                            $sql .= "(" . $aa_user_sn . ",'" . $card_sn . "'," . $time . "),";
                            $i++;
                        }
                    }
                }
                echo $sql;
                exit;

                print_r($return_data);
                exit;

                $sql = 'INSERT INTO rq_bottle_log (number,xinpian,status,type,shop_id,time_created) VALUES ';

                $i = 0;
                $time = time();
                //print_r($return_data);exit;
                if (!empty($return_data)) {
                    foreach ($return_data as $key => $value) {
                        if (!empty($value[0])) {
                            $number = preg_replace('/[\'\s| ]+/', '', $value[0]);
                            //$number = trim($value[0]);
                            $sql .= "('" . $number . "','" . $number . "',1,1,8," . $time . "),";
                            $i++;
                        }
                    }
                }
                echo $sql;
                echo $i;
                exit;

                print_r($return_data);
                exit;

                $sql = "INSERT INTO `rq_bottle` (`gp_id`, `number`, `xinpian`, `bar_code`, `type`, `status`, `qi_type`, `production_date`, `check_date`, `check_date_next`, `ctime`, `utime`, `use_date`, `cuser_id`, `cuser_name`, `uuser_id`, `uuser_name`, `chang_id`, `chang_name`, `user_time`, `service_time`, `detect_time`, `is_active`, `is_used`, `order_no`) VALUES";

                $i = 4739;

                $time = strtotime('2016-03-01');
                foreach ($return_data as $key => $value) {
                    if ($key > 0) {
                        $number = $orgName = preg_replace('/[\'\s| ]+/', '', $value[0]);
                        $sql .= "(" . $i . ", '" . $number . "', '" . $number . "', '" . $number . "', 27, 1, 0, '" . $time . "', 0, 0, '" . time() . "', 0, '0', 0, '', 0, '', 0, '', 1, 0, 0, 0, 0, ''),";

                        $i++;
                    }
                }
                echo $sql;
                exit;



                $orgArr = $quarterArr = $userArr = array();
                $orgQuarterArr = $quarterUserArr = $quarterOrgArr = array();
                if (!empty($return_data)) {
                    foreach ($return_data as $key => $value) {
                        $orgName = preg_replace('/[\'\s| ]+/', '', $value[2]);
                        $orgArr[] = $orgName;

                        $quarterName = preg_replace('/[\'\s| ]+/', '', $value[3]);
                        $quarterArr[] = $quarterName;

                        $val['username'] = preg_replace('/[\'\s| ]+/', '', $value[1]);
                        $val['mobile'] = preg_replace('/[\'\s| ]+/', '', $value[4]);
                        $userArr[] = $val;

                        if (!empty($orgName) && !empty($quarterName)) {
                            $orgQuarterArr[$orgName][] = $quarterName;
                            $quarterOrgArr[$quarterName] = $orgName;
                        }
                        $quarterUserArrp[$val['username']] = $quarterName;
                    }
                    $i = 0;

                    $orglistArr = array();
                    if (!empty($orgArr)) {
                        $orgArr = array_unique($orgArr);
                        foreach ($orgArr as $oVal) {
                            if (!empty($oVal)) {
                                $oV['org_parent_id'] = 0;
                                $oV['org_name'] = $oVal;
                                $oV['org_content'] = $oVal;
                                $orglistArr[] = $oV;
                            }
                        }
                        // LibF::M('organization_depoly')->uinsertAll($orglistArr);
                        $i = 1;
                    }
                    $quarterlistArr = array();
                    if (!empty($quarterArr)) {
                        $quarterArr = array_unique($quarterArr);
                        foreach ($quarterArr as $qVal) {
                            if (!empty($qVal)) {
                                $qV['title'] = $qVal;
                                $quarterlistArr[] = $qV;
                            }
                        }

                        // LibF::M('quarters_depoly')->uinsertAll($quarterlistArr);
                        $i = 2;
                    }

                    //orgObject
                    $orgObjectData = LibF::M('organization_depoly')->select();
                    $orgObject = array();
                    foreach ($orgObjectData as $oArr) {
                        $orgObject[$oArr['org_name']] = $oArr['org_id'];
                    }
                    //quarterObject
                    $quarterObjectData = LibF::M('quarters_depoly')->select();
                    $quarterObject = array();
                    foreach ($quarterObjectData as $qArr) {
                        $quarterObject[$qArr['title']] = $qArr['id'];
                    }
                    //userObject
                    $userObjectData = LibF::M('admin_user_depoly')->select();
                    $userObject = array();
                    foreach ($userObjectData as $uArr) {
                        $userObject[$uArr['username']] = $uArr['user_id'];
                    }

                    if (!empty($quarterOrgArr)) {
                        foreach ($quarterOrgArr as $key => $value) {
                            $qid = $quarterObject[$key];
                            $where['id'] = $qid;
                            $udate['org_parent_id'] = $orgObject[$value];

                            //LibF::M('quarters_depoly')->where($where)->save($udate);
                        }
                        $i = 3;
                    }

                    $userlistArr = array();
                    if (!empty($userArr)) {
                        foreach ($userArr as $ukey => $uVal) {
                            $uV['username'] = $uVal['username'];
                            $uV['password'] = 'ea6f9e7f31c68e51c5456a7d484541bd';
                            $uV['user_salt'] = 'FAIHRTXN';
                            $uV['nickname'] = $uVal['username'];
                            $uV['mobile_phone'] = $uVal['mobile'];
                            $uV['real_name'] = $uVal['username'];

                            $userlistArr[] = $uV;
                        }
                        // LibF::M('admin_user_depoly')->uinsertAll($userlistArr);
                        $i = 4;
                    }

                    if (!empty($quarterUserArrp)) {
                        $quArr = array();
                        foreach ($quarterUserArrp as $qkey => $qValue) {
                            $uid = $userObject[$qkey];

                            $qval['uid'] = $uid;

                            $qid = $quarterObject[$qValue];
                            $qval['quarters_id'] = $qid;
                            if ($uid > 0) {
                                $quArr[] = $qval;
                            }
                        }
                        LibF::M('quarters_user_depoly')->uinsertAll($quArr);
                        $i = 5;
                    }
                    echo $i;
                    exit;
                }
            }
        }
    }

    public function index1Action() {

        $regionModel = new RegionModel();
        $regionData = $regionModel->getRegionObject();

        $i = 0;
        //$where['status'] = 0;
        //$where['shi'] = array('neq',0);
        //$where['kid'] = array('egt',1315);
        //$where['coordinate'] = array('eq','NULL');
        $kehuData = LibF::M('kehu')->where('coordinate IS NULL')->select();
        if (!empty($kehuData)) {
            foreach ($kehuData as $value) {
                $address = '';
                if ($value['sheng'] > 0) {
                    $address = $regionData[$value['sheng']]['region_name'] . $regionData[$value['shi']]['region_name'] . $regionData[$value['qu']]['region_name'] . $regionData[$value['cun']]['region_name'];
                    $address .= $value['address'];
                    $url = 'http://api.map.baidu.com/geocoder/v2/?output=json&ak=GGzsVgy3Ra6GWd6n3Sww2Kdx&address=' . $address;
                    $data = file_get_contents($url);
                    $newdata = json_decode($data, true);
                    if (!empty($newdata)) {
                        $lng = $newdata['result']['location']['lng'];
                        $lat = $newdata['result']['location']['lat'];
                        $wz = $lng . ',' . $lat;
                        if (!empty($wz)) {
                            $udata['coordinate'] = $wz;
                            LibF::M('kehu')->where(array('kid' => $value['kid']))->save($udata);
                            $i++;
                        }
                    }
                }
            }
        }
        echo $i;
        exit;


        $url = 'http://api.map.baidu.com/geocoder/v2/?output=json&ak=GGzsVgy3Ra6GWd6n3Sww2Kdx&address=北京市';
        $data = file_get_contents($url);
        $newdata = json_decode($data, true);
        if (!empty($newdata)) {
            $lng = $newdata['result']['location']['lng'];
            $lat = $newdata['result']['location']['lat'];
            $wz = $lng . ',' . $lat;
            echo $wz;
            exit;
        }
        print_r($newdata);
        exit;




        $app = new App();
        $orderSn = $app->build_order_no();

        print_r($orderSn);
        echo '<br/>';
        print_r(substr($orderSn, 0, -3));
        $data['order_sn'] = 'Dd' . $orderSn;

        exit;

        $insertData = array();

        $depositdata = LibF::M('deposit_list')->where(array('status' => 2))->select();
        if (!empty($depositdata)) {
            foreach ($depositdata as $value) {
                $val['shipper_id'] = $value['shipper_id'];
                $val['shipper_name'] = $value['shipper_name'];
                $val['mobile_phone'] = $value['shipper_mobile'];
                $val['money'] = $value['money'];
                $val['shop_id'] = $value['shop_id'];
                $val['time'] = date('Y-m-d', $value['time_created']);
                $val['time_created'] = $value['time_created'];
                $val['type'] = 3;
                $insertData[] = $val;
            }
            LibF::M('shipper_paylist')->uinsertAll($insertData);
        }
        echo 'success';

        exit;

        $data = LibF::M('kehu')->select();

        $i = 0;
        if (!empty($data)) {
            foreach ($data as $value) {
                $where['kid'] = $value['kid'];
                $udata['kehu_type'] = $value['ktype'];

                LibF::M('order')->where($where)->save($udata);
                $i++;
            }
        }
        echo $i;

        exit;

        $where = '';
        $data = LibF::M('order')->field('mobile,count(*) as num')->where($where)->group('mobile')->order('num desc')->select();
        print_r($data);
        exit;
        if (!empty($data)) {
            $i = 0;
            foreach ($data as $value) {
                $uData['buy_time'] = $value['num'];
                $uwhere['mobile_phone'] = $value['mobile'];
                LibF::M('kehu')->where($uwhere)->save($uData);
                $i += 1;
            }
        }
        echo $i;
        exit;
    }

    public function logintestAction() {
        
    }

}
