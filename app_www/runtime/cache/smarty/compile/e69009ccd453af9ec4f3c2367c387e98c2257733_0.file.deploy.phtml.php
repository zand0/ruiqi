<?php /* Smarty version 3.1.27, created on 2016-12-03 11:34:00
         compiled from "F:\wamp\www\rq\app_www\views\index\deploy.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:2666458423d28441196_88452951%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e69009ccd453af9ec4f3c2367c387e98c2257733' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\app_www\\views\\index\\deploy.phtml',
      1 => 1472104438,
      2 => 'file',
    ),
    '1005974a6130c70841390c0689fd1d8e98151978' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\app_www\\views\\main_new.phtml',
      1 => 1467013758,
      2 => 'file',
    ),
    '9772ec5b977b47f2f65342a9db81ad5bd98d801d' => 
    array (
      0 => '9772ec5b977b47f2f65342a9db81ad5bd98d801d',
      1 => 0,
      2 => 'string',
    ),
    '84502e65bb97a9f07fe5b298fff093fe73a8099c' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\app_www\\views\\main_new_dynamic.phtml',
      1 => 1478587992,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2666458423d28441196_88452951',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_58423d2852f649_07217363',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_58423d2852f649_07217363')) {
function content_58423d2852f649_07217363 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '2666458423d28441196_88452951';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="renderer" content="webkit">
        <title>瑞气科技</title>
        <link rel="stylesheet" type="text/css" href="/statics/newhome/css/style.css">
        <?php echo '<script'; ?>
 type="text/javascript" src="/statics/js/require.min.js"><?php echo '</script'; ?>
>
    </head>
    <body>
        <div class="wrap">
            <?php echo $_smarty_tpl->getSubTemplate ('main_new_header.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

            <?php
$_smarty_tpl->properties['nocache_hash'] = '2666458423d28441196_88452951';
?>


<style type="text/css">
    body, html {width: 100%;height: 100%;margin:0;font-family:"微软雅黑";}
    #allmap{width:100%;height:500px;}
    p{margin-left:5px; font-size:14px;}
</style>
<?php echo '<script'; ?>
 type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=GGzsVgy3Ra6GWd6n3Sww2Kdx"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="http://libs.baidu.com/jquery/1.9.0/jquery.js"><?php echo '</script'; ?>
>

<div id="content">
    <!-- 地图 -->
    <div id="allmap" style="width:100%;height:647px;overflow:hidden;"></div>
    <!-- 全国气站 -->
    <div class="all">
        <h2>全国门店<?php echo count($_smarty_tpl->tpl_vars['shopData']->value);?>
<a href="javascript:void(0);">申请加入智慧燃气平台</a></h2>
        <div class="qizhan">
            <div class="item clearfix">
                <div class="fl province">
                    <h3 class="fl">河北</h3>
                    <p class="fl"><span><?php echo count($_smarty_tpl->tpl_vars['shopData']->value);?>
</span><br>气站/门店</p>
                </div>
                <div class="fr area">
                    <div class="city clearfix">
                        <a href="javascript:void(0);" class="active">沧州</a>
                    </div>
                    <div class="site">
                        <ul class="clearfix">
                            <?php if ($_smarty_tpl->tpl_vars['shopData']->value != '') {?>
                            <?php
$_from = $_smarty_tpl->tpl_vars['shopData']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['value'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['value']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
$foreach_value_Sav = $_smarty_tpl->tpl_vars['value'];
?>
                            <li>
                                <h4><a href="javascript:;"><?php echo $_smarty_tpl->tpl_vars['value']->value['shop_name'];?>
</a></h4>
                                <p><?php echo $_smarty_tpl->tpl_vars['value']->value['address'];?>
</p>
                                <div class="star">
                                    <span></span>4.8
                                </div>
                            </li>
                            <?php
$_smarty_tpl->tpl_vars['value'] = $foreach_value_Sav;
}
?>
                            <?php }?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 行业动态 安全常识 -->
    <?php /*  Call merged included template "main_new_dynamic.phtml" */
echo $_smarty_tpl->getInlineSubTemplate('main_new_dynamic.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, '1130358423d28500839_08443286', 'content_58423d28500834_06982594');
/*  End of included template "main_new_dynamic.phtml" */?>

</div>

<?php echo '<script'; ?>
 type="text/javascript">
    // 百度地图API功能	
    map = new BMap.Map("allmap");
    map.centerAndZoom('北京', 15);
    var data_info = [[116.417854, 39.921988, "地址：北京市东城区王府井大街88号乐天银泰百货八层"],
        [116.406605, 39.921585, "地址：北京市东城区东华门大街"],
        [116.412222, 39.912345, "地址：北京市东城区正义路甲5号"]
    ];
    
    setTimeout(function(){
        map.setZoom(14);   
    }, 2000);  //2秒后放大到14级
    map.enableScrollWheelZoom(true);    
        
    //var data_info =[<?php echo $_smarty_tpl->tpl_vars['maplist']->value;?>
];
    var opts = {
        width: 250, // 信息窗口宽度
        height: 80, // 信息窗口高度
        title: "门店相关信息", // 信息窗口标题
        enableMessage: true//设置允许信息窗发送短息
    };
    for (var i = 0; i < data_info.length; i++) {
        var marker = new BMap.Marker(new BMap.Point(data_info[i][0], data_info[i][1]));  // 创建标注
        var content = data_info[i][2];
        map.addOverlay(marker);               // 将标注添加到地图中
        addClickHandler(content, marker);
    }
    function addClickHandler(content, marker) {
        marker.addEventListener("click", function(e) {
            openInfo(content, e)
        });
    }
    function openInfo(content, e) {
        var p = e.target;
        var point = new BMap.Point(p.getPosition().lng, p.getPosition().lat);
        var infoWindow = new BMap.InfoWindow(content, opts);  // 创建信息窗口对象 
        map.openInfoWindow(infoWindow, point); //开启信息窗口
    }
<?php echo '</script'; ?>
>


            <?php echo $_smarty_tpl->getSubTemplate ('main_new_footer.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

        </div>
        <?php echo '<script'; ?>
 type="text/javascript" src="/statics/js/controllers/indexCtrl.js"><?php echo '</script'; ?>
>
    </body>
</html><?php }
}
?><?php
/*%%SmartyHeaderCode:1130358423d28500839_08443286%%*/
if ($_valid && !is_callable('content_58423d28500834_06982594')) {
function content_58423d28500834_06982594 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '1130358423d28500839_08443286';
?>
<div class="bottom clearfix">
    <div class="fl leftTxt">
        <a href="#">服务协议</a><br>
        <a href="#">公司公告</a><br>
        <a href="#">服务协议</a><br>
        <a href="#">关于我们</a><br>
        <a href="#">联系我们</a>
    </div>
    <ul class="fl saoma">
        <li>
            <div class="code">
                <img src="/statics/upload/custom.png">
            </div>	
            <p>客户端APP</p>
            <p>扫一扫关注</p>    
        </li>
        <li>
            <div class="code">
                <img src="/statics/upload/shipper.png">
            </div>	
            <p>送气工端APP</p>
            <p>扫一扫下载</p>    
        </li>
        <li>
            <div class="code">
                <img src="/statics/upload/general.png">
            </div>	
            <p>管理端APP</p>
            <p>扫一扫下载</p>    
        </li>
    </ul>
    <dl class="fl">
        <dt>行业动态</dt>
        <dd><a href="#">河北沧州智能燃气平台已开通！</a></dd>
        <dd><a href="#">智慧燃气App 2.0发布！</a></dd>
        <dd><a href="#">交易量突破1000KG！</a></dd>
    </dl>
    <dl class="fr">
        <dt>安全常识</dt>
        <dd><a href="#">液化石油气安全使用常识</a></dd>
        <dd><a href="#">安全使用液化气的方法</a></dd>
        <dd><a href="#">安全使用液化气的消防安全知识</a></dd>
        <dd><a href="#">液化气站安全距离</a></dd>
    </dl>
</div><?php
/*/%%SmartyNocache:1130358423d28500839_08443286%%*/
}
}
?>