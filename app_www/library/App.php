<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class App {

    /**
     * 返回数据存储数组;
     * @var array
     */
    private $respondData = array();

    /**
     * 返回数据类型，默认'json'
     * @var string
     */
    protected $respondType = 'json';
    
    /**
     * 返回已设置的数据
     * @param int $code 返回状态代码，1:正确, <=0:表示错误
     * @param array|string $info 设置返回数据，如果是字符串则覆盖之前的返回数据
     * @param boolean $exit 是否退出
     */
    public function respond($code = 1, $info = array(), $exit = true) {
        $data = array();
        $data['resultCode'] = $code;
        $data['resultInfo'] = is_array($info) ? $this->setRespondData($info) : $info;
        if ($this->respondType === 'json') {
            echo json_encode($data);
            if ($exit === true) {
                exit;
            }
        }
    }

    /**
     * 设置返回的数据
     * @param array|string $key 如果是数组则合并到[$respondData]中，如果是字符串则为键值
     * @param mixed $value 当[$key]为字符串时，设置值
     * @return array 返回已设置的结果
     */
    public function setRespondData($key, $value = null) {
        if (is_array($key)) {
            $this->respondData = empty($this->respondData) ? $key : array_merge($this->respondData, $key);
        } else {
            $this->respondData[$key] = $value;
        }
        return $this->respondData;
    }

    /**
     * 获取当前密码
     * @param var 当前传递密码__base64相关解码
     * @return 解析完成数据
     */
    public function getPassword($password, $boolean = true) {
        if (!empty($password)) {
            $strlen = strlen($password);
            $password = substr($password, 0, $strlen - 1);
            $password = base64_decode($password);

            return $password;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取唯一订单号
     * @return 获取唯一订单号
     */
    function build_order_no(){
        return date('ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

}