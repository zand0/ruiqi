<?php /* Smarty version 3.1.27, created on 2016-12-04 18:56:36
         compiled from "F:\wamp\www\rq\ruiqi\app_www\modules\My\views\home\createorder.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:72615843f664a9fb34_55422602%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0d6011c6c9028edceb8b2a1875629e11e56dce97' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\ruiqi\\app_www\\modules\\My\\views\\home\\createorder.phtml',
      1 => 1464684568,
      2 => 'file',
    ),
    '8c5b1b6f825b4fee181b300f7dd5db806feb3c3a' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\ruiqi\\app_www\\modules\\My\\views\\layouts\\main.phtml',
      1 => 1462934430,
      2 => 'file',
    ),
    '7adb568c149627d23f3b03178b968cd869502b0e' => 
    array (
      0 => '7adb568c149627d23f3b03178b968cd869502b0e',
      1 => 0,
      2 => 'string',
    ),
    'c2ba585b9775e614614df1102fe6c8a1754903df' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\ruiqi\\app_www\\modules\\My\\views\\layouts\\left.phtml',
      1 => 1461644466,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '72615843f664a9fb34_55422602',
  'variables' => 
  array (
    '_PUBLIC' => 0,
  ),
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_5843f664df34f2_25976204',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5843f664df34f2_25976204')) {
function content_5843f664df34f2_25976204 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '72615843f664a9fb34_55422602';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>瑞气科技</title>
        <link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->tpl_vars['_PUBLIC']->value;?>
/css/style.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->tpl_vars['_PUBLIC']->value;?>
/css/base/jquery-ui.css">
        <?php echo '<script'; ?>
 type="text/javascript" src="<?php echo $_smarty_tpl->tpl_vars['_PUBLIC']->value;?>
/js/jquery.min.js"><?php echo '</script'; ?>
>
        <?php echo '<script'; ?>
 type="text/javascript" src="<?php echo $_smarty_tpl->tpl_vars['_PUBLIC']->value;?>
/js/jquery-ui.js"><?php echo '</script'; ?>
>
        <?php echo '<script'; ?>
 type="text/javascript" src="<?php echo $_smarty_tpl->tpl_vars['_PUBLIC']->value;?>
/js/jquery.cookie.js"><?php echo '</script'; ?>
>
        <?php echo '<script'; ?>
 type="text/javascript" src="<?php echo $_smarty_tpl->tpl_vars['_PUBLIC']->value;?>
/js/pages.js"><?php echo '</script'; ?>
>
    </head>
    <body>
        <div class="wrap">
            <?php echo $_smarty_tpl->getSubTemplate ('layouts\header.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

            <?php
$_smarty_tpl->properties['nocache_hash'] = '72615843f664a9fb34_55422602';
?>


<?php echo '<script'; ?>
>
    $(function () {

        $(".typeID").click(function(){
            var typeID = $(this).val();
            if(typeID == 4){
                $(".ptbottle").hide();
                $(".tybottle").show();
                $(".yhbottle").hide();
            }else if(typeID == 5){
                $(".ptbottle").hide();
                $(".tybottle").hide();
                $(".yhbottle").show();
            }else{
                $(".ptbottle").show();
                $(".tybottle").hide();
                $(".yhbottle").hide();
            }
            $("#orderTablelist").html('');
        });

        //下订单选中列表显示
        $(".addbottleBtn").click(function () {
            var typeID = $("input[type='radio']:checked").val();
            
            var bottleType = $(this).parent('div').find("#goodType").val();
            var bottleName = $(this).parent('div').find("#goodType option:selected").text();
            var bottleNum = $(this).parent('div').find("#goodNum").val();
            if (bottleType != 0 && bottleName != '') {
                var bArr = bottleType.split('|');
                
                if(typeID == 4 || typeID == 5){
                    var numTotal = bArr[5] * bottleNum;
                    var moneyTotal = bottleNum * bArr[4];
                }else{
                    var numTotal = bottleNum;
                    var moneyTotal = bArr[4];
                }
                var bottleList = "<tr><td>" + bottleName + "</td>";
                bottleList += "<td>" + numTotal + "</td>";
                bottleList += "<td>" + bArr[4] + "</td>";
                bottleList += "<td></td>";
                bottleList += "<td></td>";
                bottleList += "<td>" + parseInt(bottleNum) * parseInt(bArr[4]) + "</td>";
                bottleList += "</tr>";

                $("#orderTablelist").append(bottleList);

                var order_bottle = $("#order_bottle").val();
                var show_bottle = bottleType + "|" + bottleNum;
                if (order_bottle != '') {
                    $("#order_bottle").val(order_bottle + '*' + show_bottle);
                } else {
                    $("#order_bottle").val(show_bottle);
                }
            } else {
                alert('请选择');
            }
        });
        //创建订单
        $("#addorder").click(function () {
            var plist = $("#orderTablelist").html();
            if(plist == ''){
                alert('请添加数据');
                return false;
            }
            var typeID = $("input[type='radio']:checked").val();
            $("#order_type").val(typeID);
            var formdata = $('#formorder').serialize();
            $.ajax({
                type: "POST",
                async: false,
                url: "/my/home/ajaxorder",
                data: formdata,
                success: function (result) {
                    var preview = eval("(" + result + ")");
                    if (preview.code >= 1) {
                        alert('创建成功');
                    } else {
                        alert('创建失败');
                    }
                }
            });
        });
    });
<?php echo '</script'; ?>
>

<div class="content clearfix">
    <?php /*  Call merged included template "layouts\left.phtml" */
echo $_smarty_tpl->getInlineSubTemplate('layouts\left.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, '4165843f664b380d3_77329177', 'content_5843f664b34252_28688154');
/*  End of included template "layouts\left.phtml" */?>

    <div class="fr" style="width:904px;">
        <div class="contRItem myorder">
            <div class="contRTitle">
                下单订气
                <span></span>
            </div>
            <div class="contRList">
                <div class="rqOption clearfix rqRadio">
                    <label class="fl">类型：</label>
                    <div class="myradio fl">
                        <div class="radiobox clearfix">
                            <input type="radio" name="type" value="1" class='typeID' checked="checked"><label for='s1'>燃气</label>
                            <input type="radio" name="type" value="4" class='typeID'><label for='s1'>体验套餐</label>
                            <input type="radio" name="type" value="5" class='typeID'><label for='s1'>优惠套餐</label>
                        </div>
                    </div>
                </div>
                <div class="rqLine clearfix ptbottle">
                    <div class="rqOption fl">
                        <label class="fl">商品：</label>
                        <div class="selectBox fl">
                            <div class="mySelect">
                                <select name="goodType" id="goodType" class="selectmenu" style="width:130px;">
                                    <option value="0">选择商品</option>
                                    <?php if ($_smarty_tpl->tpl_vars['goodData']->value != '') {?>
                                    <?php
$_from = $_smarty_tpl->tpl_vars['goodData']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['value'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['value']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
$foreach_value_Sav = $_smarty_tpl->tpl_vars['value'];
?>
                                    <?php if ($_smarty_tpl->tpl_vars['value']->value['type'] == 1) {?>
                                    <option value="<?php echo $_smarty_tpl->tpl_vars['value']->value['id'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['type'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['name'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['norm_id'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['retail_price'];?>
"><?php echo $_smarty_tpl->tpl_vars['value']->value['name'];?>
-<?php echo $_smarty_tpl->tpl_vars['bottlTypeData']->value[$_smarty_tpl->tpl_vars['value']->value['norm_id']]['bottle_name'];?>
</option>
                                    <?php } else { ?>
                                    <option value="<?php echo $_smarty_tpl->tpl_vars['value']->value['id'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['type'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['name'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['norm_id'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['retail_price'];?>
"><?php echo $_smarty_tpl->tpl_vars['value']->value['name'];?>
-<?php echo $_smarty_tpl->tpl_vars['productTypeData']->value[$_smarty_tpl->tpl_vars['value']->value['norm_id']]['name'];?>
</option>
                                    <?php }?>
                                    <?php
$_smarty_tpl->tpl_vars['value'] = $foreach_value_Sav;
}
?>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="rqOption fl">
                        <label class="fl">数量：</label>
                        <div class="inputBox fl">
                            <input type="text" name="goodNum" id="goodNum" value="1" style="width:65px;">
                        </div>
                    </div>
                    <a href="javascript:;" class="blueBtn5 fl addbottleBtn" id="addbottleTable">添加</a>
                </div>
                <div class="rqLine clearfix tybottle" style="display: none;">
                    <div class="rqOption fl">
                        <label class="fl">体验套餐：</label>
                        <div class="selectBox fl">
                            <div class="mySelect">
                                <select name="goodType" id="goodType" class="selectmenu" style="width:130px;">
                                    <option value="0">选择套餐</option>
                                    <?php if ($_smarty_tpl->tpl_vars['tcData']->value['ty'] != '') {?>
                                    <?php
$_from = $_smarty_tpl->tpl_vars['tcData']->value['ty'];
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['value'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['value']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
$foreach_value_Sav = $_smarty_tpl->tpl_vars['value'];
?>
                                    <option value="<?php echo $_smarty_tpl->tpl_vars['value']->value['id'];?>
|4|<?php echo $_smarty_tpl->tpl_vars['value']->value['name'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['norm_id'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['money'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['num'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['deposit'];?>
"><?php echo $_smarty_tpl->tpl_vars['value']->value['name'];?>
</option>
                                    <?php
$_smarty_tpl->tpl_vars['value'] = $foreach_value_Sav;
}
?>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="rqOption fl">
                        <label class="fl">套餐数量：</label>
                        <div class="inputBox fl">
                            <input type="text" name="goodNum" id="goodNum" value="1" style="width:65px;">
                        </div>
                    </div>
                    <a href="javascript:;" class="blueBtn5 fl addbottleBtn" id="addbottleTable">添加</a>
                </div>
                <div class="rqLine clearfix yhbottle" style="display: none;">
                    <div class="rqOption fl">
                        <label class="fl">优惠套餐：</label>
                        <div class="selectBox fl">
                            <div class="mySelect">
                                <select name="goodType" id="goodType" class="selectmenu" style="width:130px;">
                                    <option value="0">优惠套餐</option>
                                    <?php if ($_smarty_tpl->tpl_vars['tcData']->value['yh'] != '') {?>
                                    <?php
$_from = $_smarty_tpl->tpl_vars['tcData']->value['yh'];
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['value'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['value']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
$foreach_value_Sav = $_smarty_tpl->tpl_vars['value'];
?>
                                    <option value="<?php echo $_smarty_tpl->tpl_vars['value']->value['id'];?>
|5|<?php echo $_smarty_tpl->tpl_vars['value']->value['name'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['norm_id'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['money'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['num'];?>
|<?php echo $_smarty_tpl->tpl_vars['value']->value['deposit'];?>
"><?php echo $_smarty_tpl->tpl_vars['value']->value['name'];?>
</option>
                                    <?php
$_smarty_tpl->tpl_vars['value'] = $foreach_value_Sav;
}
?>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="rqOption fl">
                        <label class="fl">套餐数量：</label>
                        <div class="inputBox fl">
                            <input type="text" name="goodNum" id="goodNum" value="1" style="width:65px;">
                        </div>
                    </div>
                    <a href="javascript:;" class="blueBtn5 fl addbottleBtn" id="addbottleTable">添加</a>
                </div>
                <div class="rqTabItem">
                    <table>
                        <thead>
                            <tr>
                                <th>规格</th>
                                <th>数量</th>
                                <th>单价</th>
                                <th>运送费</th>
                                <th>优惠</th>
                                <th>总金额</th>
                            </tr>
                        </thead>
                        <tbody id="orderTablelist"></tbody>
                    </table>
                </div>
                <dl>
                    <dt>客户详细信息：</dt>
                    <dd>
                        姓名：<span><?php echo $_smarty_tpl->tpl_vars['userRow']->value['user_name'];?>
</span>
                        电话：<span><?php echo $_smarty_tpl->tpl_vars['userRow']->value['mobile_phone'];?>
</span>
                    </dd>
                    <dd>详细地址：<span><?php echo $_smarty_tpl->tpl_vars['userRow']->value['address'];?>
</span></dd>
                </dl>
                <div class="rqLine">
                    <div class="rqOption">
                        <label>订单备注：</label>
                        <div class="clearfix">
                            <form action="" method="post" id="formorder">
                                <div class="rqOption fl">
                                    <textarea style="width:400px;height:110px;" name="order_comment" id="order_comment"></textarea>
                                </div>
                                <div class="rqOption fl mycheckList remark mycheckbox">
                                    <div class="checkList" style="width:90px;">
                                        <a href="javascript:;">押金用户</a>
                                        <a href="javascript:;">紧急订单</a>
                                    </div>
                                    <input type="hidden" name="order_bottle" id="order_bottle" value="" />
                                    <input type="hidden" name="order_product" id="order_product" value="" />
                                    <input type="hidden" name="order_type" id="order_type" value="" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="rqLine">
                    <div class="rqOption">
                        <a href="javascript:;" class="blueBtn" id="addorder">提交订单</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="contRItem">
            <div class="contRTitle">
                历史订单记录
                <span></span>
            </div>
            <div class="contRList">
                <div class="rqTabItem">
                    <table>
                        <thead>
                            <tr>
                                <th>订单号</th>
                                <th>运送费</th>
                                <th>送气工</th>
                                <th>送气工电话</th>
                                <th>总金额</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($_smarty_tpl->tpl_vars['data']->value['ext']['list'] != '') {?>
                            <?php
$_from = $_smarty_tpl->tpl_vars['data']->value['ext']['list'];
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['value'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['value']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
$foreach_value_Sav = $_smarty_tpl->tpl_vars['value'];
?>
                            <tr>
                                <td><?php echo $_smarty_tpl->tpl_vars['value']->value['order_sn'];?>
</td>
                                <td><?php echo $_smarty_tpl->tpl_vars['value']->value['shipment'];?>
</td>
                                <td><?php echo $_smarty_tpl->tpl_vars['value']->value['shipper_name'];?>
</td>
                                <td><?php echo $_smarty_tpl->tpl_vars['value']->value['shipper_mobile'];?>
</td>
                                <td><?php echo $_smarty_tpl->tpl_vars['value']->value['money'];?>
</td>
                                <td class="tableBtn">
                                    <a href="javascript:;">投诉</a>
                                    <span>|</span>
                                    <a href="javascript:;">评价</a>
                                </td>
                            </tr>
                            <?php
$_smarty_tpl->tpl_vars['value'] = $foreach_value_Sav;
}
?>
                            <?php }?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

            <?php echo $_smarty_tpl->getSubTemplate ('layouts\footer.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

        </div>
        <?php echo '<script'; ?>
 type="text/javascript" src="<?php echo $_smarty_tpl->tpl_vars['_PUBLIC']->value;?>
/js/common.js"><?php echo '</script'; ?>
>
    </body>
</html><?php }
}
?><?php
/*%%SmartyHeaderCode:4165843f664b380d3_77329177%%*/
if ($_valid && !is_callable('content_5843f664b34252_28688154')) {
function content_5843f664b34252_28688154 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '4165843f664b380d3_77329177';
?>
<div class="fl" style="width:183px;">
    <div class="contRItem">
        <div class="iconTitle clearfix">
            <h3 class="fl">个人中心</h3>
        </div>
        <div class="personalCenter">
            <div class="myheadface">
                <?php if ($_smarty_tpl->tpl_vars['kehuInfo']->value['photo'] != '') {?>
                <img src="/statics/upload/photo/<?php echo $_smarty_tpl->tpl_vars['kehuInfo']->value['photo'];?>
">
                <?php } else { ?>
                <img src="/statics/upload/photo/touxiang.jpg">
                <?php }?>
            </div>
            <ul>
                <li class="myname"><?php echo $_smarty_tpl->tpl_vars['kehuInfo']->value['user_name'];?>
</li>
                <li><span></span><?php echo $_smarty_tpl->tpl_vars['kehuInfo']->value['mobile_phone'];?>
</li>
                <!--li class="myaddress"><span></span><?php echo $_smarty_tpl->tpl_vars['kehuInfo']->value['region_name'];?>
</li-->
                <li class="mybalance"><span></span>余额：<font><?php echo $_smarty_tpl->tpl_vars['kehuInfo']->value['balance'];?>
元<font></li>
                <li class="mybalance"><span></span>欠款额：<font><?php echo $_smarty_tpl->tpl_vars['kehuInfo']->value['money'];?>
元<font></li>
                <li class="mybalance"><span></span>积分：<font><?php echo $_smarty_tpl->tpl_vars['kehuInfo']->value['point'];?>
元<font></li>
            </ul>
            <!--a href="javascript:;" class="blueBtn4">充值</a-->
        </div>
    </div>
    <div class="contLeft fl operateList">
        <ul style="display:block;">
            <li class="show_active">
                <a class="show_url" href="/my/home/createorder">我要订气<span></span></a>
            </li>
            <li class="dq_li2 show_active">
                <a class="show_url" href="/my/home/tousu">我要投诉<span></span></a>
            </li>
            <li class="dq_li3 show_active">
                <a class="show_url" href="/my/home/baoxiu">我要报修<span></span></a>
            </li>
            <li class="dq_li4 show_active">
                <a class="show_url" href="/my/home/backbottle">我要退瓶<span></span></a>
            </li>
            <li class="dq_li5 show_active">
                <a class="show_url" href="/my/home/myorder">我的订单<span></span></a>
            </li>
            <li class="dq_li6 show_active active">
                <a class="show_url" href="/my/home/index">个人资料<span></span></a>
            </li>
        </ul>
        
        <?php echo '<script'; ?>
>
            $(function () {
                var url = document.location.href;  //得到链接地址 
                //字符串去空
                String.prototype.trim = function () {
                    return this.replace(/(^\s*)|(\s*$)/g, "");
                }
                if (url) {
                    $('.show_url').each(function () {
                        var myIndex = $(this).attr('href').lastIndexOf("/");
                        var href = $(this).attr('href');
                        var test = new RegExp(href); //创建正则表达式对象
                        var result = url.match(test);
                        if (result) {
                            $(this).parents("li.show_active ").addClass('active').siblings().removeClass("active");
                        }
                    });
                }
            });
        <?php echo '</script'; ?>
>
        
    </div>
</div><?php
/*/%%SmartyNocache:4165843f664b380d3_77329177%%*/
}
}
?>