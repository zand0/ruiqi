<?php /* Smarty version 3.1.27, created on 2016-11-29 14:10:44
         compiled from "E:\xampp\htdocs\rq\app_www\modules\Wx\views\order\pay_money.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:6079583d1be446b2a9_28353409%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '99bc5f68772bd21c736146f62e6295d0f41a51d0' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\app_www\\modules\\Wx\\views\\order\\pay_money.phtml',
      1 => 1480399841,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '6079583d1be446b2a9_28353409',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_583d1be446ee44_96775572',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_583d1be446ee44_96775572')) {
function content_583d1be446ee44_96775572 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '6079583d1be446b2a9_28353409';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>提交订单</title>
	<link rel="stylesheet" href="/statics/css/base.css"/>
	<link rel="stylesheet" href="/statics/css/style.css"/>
	<?php echo '<script'; ?>
 src="/statics/js/jquery-1.8.3.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="/statics/js/main.js"><?php echo '</script'; ?>
>
</head>
<body>
	<div class="address_detail">
		<div class="l_img">
			<img src="/statics/images/location.png" alt="">
		</div>
		<div class="r_con">
			<ul>
				<li>
					<span class="user_name">胡大军</span>
					<span class="user_tel">138****4007</span>
				</li>
				<li>
					北京市朝阳区团结湖北三条43路公交公交公交公交101室
				</li>
			</ul>
		</div>
	</div>
	<div class="t_"></div>
	<div class="goods_detail">
		<table>
			<tr>
				<td>商品名称</td>
				<td>规格</td>
				<td>单价</td>
				<td>数量</td>
			</tr>
			<tr>
				<td>瓶装液化气</td>
				<td>5kg</td>
				<td>35</td>
				<td>1</td>
			</tr>
			<tr>
				<td>瓶装液化气</td>
				<td>10kg</td>
				<td>70</td>
				<td>2</td>
			</tr>
		</table>
	</div>
	<div class="else-info">
		<ul>
			<li><span>优惠券</span><span>-5</span></li>
			<li><span>配送时间</span><span>2016-11-24 08:00-10:00</span></li>
			<li><span>订单备注</span><span>无</span></li>
		</ul>
	</div>
	<div class="f_submit">
		<div class="l_sub">
			订单金额：
			<img src="/statics/images/money.png" alt="">
			<span class="money">357</span>
		</div>
		<div class="r_btn">立即支付</div>
	</div>
	<!-- 选择支付方式页 -->
	<div class="mask"></div>
	<div class="pay_mode">
		<h1>选择支付方式</h1>
		<ul>
			<li><img class="icon_pay" src="/statics/images/xianj.png" alt="">货到付款<img class="choose c_circle" src="/statics/images/btn_f.png" alt=""></li>
			<li><img class="icon_pay" src="/statics/images/weixin.png" alt="">微信缴费<span>（功能暂未开放）</span><img class="c_circle" src="/statics/images/btn_f.png" alt=""></li>
		</ul>
		<div class="f_pay">
			<div class="l_cancel">取消</div>
			<div class="r_sure">确定</div>
		</div>
	</div>
</body>
</html><?php }
}
?>