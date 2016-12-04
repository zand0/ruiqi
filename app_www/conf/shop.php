<?php
    return array(
        'shop_level' => array(
            2 => '中心店', //'center'
            3 => '卫星店', // satellite
            4 => '社区店', // community
            5 => '合作伙伴', // partner
        ),
        'shop_manage_level'=>array(
            1 => array(2),   // 气站可添加中心店面
            2 => array(3,4,5), // 中心店可以添加卫星店，社区点，合作伙伴
        ),
        'shop_status'=>array(
            '0' => '停业',
            '1' => '营业'
        ),
        'shop_default_status' => 1,
        'custum_source' => array(//客户来源
            1 => '门店拓展',
            2 => '合伙人拓展',
            3 => '呼叫中心',
            4 => '气站',
        )
    );
