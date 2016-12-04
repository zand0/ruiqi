<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TbModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    /**
     * 模拟post进行url请求
     * @param string $url
     * @param string $param
     */
    function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);

        return $data;
    }

    //同步钢瓶充装记录
    public function synchronizebottle($data) {

        $returnData = array('code' => 0);
        if (isset($data['data']) && !empty($data['data'])) {
            $fillVal = $bottleLog = $blog = array();
            
            $time = time();
            $end_time = '';

            $xinpianArr = array();
            $date = date('Y-m-d H:i');
            foreach ($data['data'] as $value) {
                
                $addData['number'] = $bottleLog['number'] = $value['gpbm']; //钢瓶编码
                $addData['xinpian'] = $bottleLog['xinpian'] = $value['rfidcode']; //芯片编码
                $addData['bar_code'] = $bottleLog['bar_code'] = $value['tenantcode']; //忽略
                $addData['type'] = $value['ggxh']; //钢瓶类型
                $addData['filling_no'] = $value['officecode'];  //忽略
                $addData['total'] = $value['czjz'];  //充装重量
                $addData['ftbm'] = $value['gpbm']; //阀体编码
                $addData['cszl'] = isset($value['cszl']) ? $value['cszl'] : 0; //初始重量
                $addData['mbzl'] = isset($value['mbzl']) ? $value['mbzl'] : 0; //目标重量
                $addData['czjz'] = isset($value['czjz']) ? $value['czjz'] : 0; //充装净重
                $addData['ggxh'] = isset($value['ggxh']) ? $value['ggxh'] : ''; //钢瓶类型
                $addData['czsj'] = $end_time = isset($value['czkssj']) ? date('Y-m-d H:i:s', substr($value['czkssj'], 0, -3)) : $date; //充装时间
                $addData['yxsj'] = isset($value['czyxsj']) ? $value['czyxsj'] : 0; //充装消耗时间
                $addData['cjqbm'] = isset($value['cjqbm']) ? $value['cjqbm'] : ''; //抢
                $addData['time'] = $time;
                $fillVal[] = $addData;
                if ($addData['czjz'] > 0) {
                    $xinpianArr[] = "'" . $value['rfidcode'] . "'";
                }

                $bottleLog['comment'] = '钢瓶充装,充装重量' . $value['czjz'];
                $bottleLog['time_created'] = $time;
                $blog[] = $bottleLog;
            }
            $status = LibF::M('bottle_tb')->uinsertAll($fillVal);
            if (!empty($blog)) {
                LibF::M('bottle_transfer_logs')->uinsertAll($blog);
            }
            if (!empty($xinpianArr)) {
                $whereList = "xinpian IN(" . implode(',', $xinpianArr) . ")";
                LibF::M('bottle')->where($whereList)->save(array('status' => 2));
            }

            $returnData['code'] = 1;
            $returnData['end_time'] = $end_time;
        } else {
            $returnData['msg'] = '当前获取数据为空';
        }
        return $returnData;
    }

    //同步钢瓶初始化信息
    public function initializationbottle($data) {

        $returnData = array('code' => 0);
        if (isset($data['data']) && !empty($data['data'])) {

            $bottleVal = $bottleLog = $blog = array();
            $time = time();
            foreach ($data['data'] as $value) {
                $addData['number'] = $bottleLog['number'] = $value['gpbm']; //钢瓶编码
                $addData['xinpian'] = $bottleLog['xinpian'] = $value['xpbm']; //芯片编码
                $addData['bar_code'] = $bottleLog['bar_code'] = $value['ftbm']; //阀体编码
                $addData['type'] = $value['ggxh']; //规格型号
                $addData['chang_name'] = $value['sccj']; //生产厂家
                $addData['fpr'] = (isset($value['fpr']) && !empty($value['fpr'])) ? $value['fpr'] : '';
                $addData['fpsj'] = isset($value['fpsj']) && !empty($value['fpsj']) ? $value['fpsj'] : '';
                $addData['ggxh'] = isset($value['ggxh']) ? $value['ggxh'] : '';
                $addData['ftbm'] = isset($value['ftbm']) ? $value['ftbm'] : '';
                //$addData['gpid'] = isset($value['gpid']) ? $value['gpid'] : '';
                $addData['gplx'] = isset($value['gplx']) ? $value['gplx'] : '';
                $addData['jdfs'] = isset($value['jdfs']) ? $value['jdfs'] : '';
                $addData['jdr'] = isset($value['jdr']) ? $value['jdr'] : '';
                $addData['jdsj'] = isset($value['jdsj']) ? substr($value['jdsj'], 0,-3) : $time; //建档时间
                $addData['khbm'] = isset($value['khbm']) ? $value['khbm'] : '';
                $addData['scjysj'] = isset($value['scjysj']) ? $value['scjysj'] : ''; //最后一次安检时间
                $addData['xcjysj'] = isset($value['xcjysj']) ? $value['xcjysj'] : ''; //下次安检时间
                $addData['officecode'] = isset($value['officecode']) ? $value['officecode'] : ''; //所属气站
                $addData['czjz'] = isset($value['czjz']) ? $value['czjz'] : ''; //充装介质
                $addData['synx'] = isset($value['synx']) ? $value['synx'] : ''; //使用年限
                $addData['qjzq'] = isset($value['qjzq']) ? $value['qjzq'] : ''; //强检周期
                $addData['sccj'] = isset($value['sccj']) ? $value['sccj'] : ''; //钢瓶厂家编号
                $addData['scrq'] = isset($value['scrq']) ? $value['scrq'] : ''; //钢瓶生产日期
                $addData['tenantcode'] = isset($value['tenantcode']) ? $value['tenantcode'] : '';
                $addData['daxgsj'] = isset($value['daxgsj']) ? $value['daxgsj'] : '';
                $addData['ywzt'] = isset($value['ywzt']) ? $value['ywzt'] : '';
                $addData['gpzt'] = isset($value['gpzt']) ? $value['gpzt'] : '';
                $addData['czcs'] = isset($value['czcs']) ? $value['czcs'] : ''; //充装次数
                $addData['scczsj'] = isset($value['scczsj']) ? $value['scczsj'] : '';
                $addData['sjrq'] = isset($value['sjrq']) ? $value['sjrq'] : '';
                $addData['jyzcrq'] = isset($value['jyzcrq']) ? $value['jyzcrq'] : '';
                $addData['is_active'] = 1;
                $addData['is_used'] = 1;
                $addData['ctime'] = $time;
                $bottleLog['comment'] = '钢瓶初始化';

                $bottleVal[] = $addData;
                $blog[] = $bottleLog;
            }
            if (!empty($bottleVal)) {
                $status = LibF::M('bottle_initialization')->uinsertAll($bottleVal);
                if (!empty($blog)) {
                    foreach ($blog as &$bvalue) {
                        $bvalue['time_created'] = time();
                    }
                    LibF::M('bottle_transfer_logs')->uinsertAll($blog);
                }
                foreach ($bottleVal as $bVal) {
                    $bv['number'] = $bVal['number'];
                    $bv['xinpian'] = $bVal['xinpian'];
                    $bv['bar_code'] = $bVal['bar_code'];
                    $bv['type'] = $bVal['type'];
                    $bv['is_active'] = 1;
                    $bv['is_used'] = 1;
                    $bv['status'] = 1;
                    $bv['ctime'] = time();
                    $bottleData = LibF::M('bottle')->where(array('xinpian' => $bVal['xinpian']))->find();
                    if (empty($bottleData)) {
                        LibF::M('bottle')->add($bv);
                    }
                }
            }
            $returnData['code'] = 1;
            $returnData['end_time'] = $time;
        } else {
            $returnData['msg'] = '当前获取数据为空';
        }
        return $returnData;
    }

}
