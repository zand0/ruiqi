<?php /* Smarty version 3.1.27, created on 2016-11-29 13:50:54
         compiled from "E:\xampp\htdocs\rq\app_www\modules\Wx\views\ordermanage\order_detail.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:251583d173e687ee6_90748066%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3842da16475b8f26305084f58f2e50dd70cd4d15' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\app_www\\modules\\Wx\\views\\ordermanage\\order_detail.phtml',
      1 => 1480398644,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '251583d173e687ee6_90748066',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_583d173e68eb94_35660227',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_583d173e68eb94_35660227')) {
function content_583d173e68eb94_35660227 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '251583d173e687ee6_90748066';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>订单详情（已完成）</title>
	<link rel="stylesheet" href="/statics/css/base.css"/>
	<link rel="stylesheet" href="/statics/css/style.css"/>
	<?php echo '<script'; ?>
 src="/statics/js/jquery-1.8.3.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="/statics/js/main.js"><?php echo '</script'; ?>
>
</head>
<body style="overflow: inherit;">
	<div class="order_num">
		<span>订单号：dd1234555</span>
		<span class="state">已完成</span>
	</div>
	<div class="address_details">
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
	<div class="staff">
		<p>
			<span>配送门店</span>
			<span class="s_con">沧县李天木店</span>
		</p>
		<p>
			<span>送气工</span>
			<span class="s_con">杨大力（18510205564）</span>
		</p>
	</div>
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
	<div class="all_sum">
		<p class="sum_t">
			<span>
				商品金额
			</span>
			<span>
				￥300
			</span>
		</p>
		<p class="sum_t">金额</p>
		<p class="sum_c">
			<span>15kg</span>
			<span>1</span>
			<span>￥200</span>
		</p>
		<p class="sum_c">
			<span>30kg</span>
			<span>1</span>
			<span>￥300</span>
		</p>
	</div>
	<div class="wx_order">
		<p class="sum_t">
			<span>
				微信下单
			</span>
			<span>
				-￥5
			</span>
		</p>
		<p class="sum_t">
			<span>
				余气抵扣
			</span>
			<span>
				-￥5
			</span>
		</p>
		<p class="sum_t">
			<span>
				残液抵扣
			</span>
			<span>
				-￥5
			</span>
		</p>
		<p>折旧</p>
		<p class="sum_cc">
			<span>30kg</span>
			<span>08年</span>
			<span>2</span>
			<span>-￥30</span>
		</p>
	</div>
	<div class="pay_con">
		<p class="sum_t">
			<span>
				应付
			</span>
			<span>
				￥161
			</span>
		</p>
		<p class="sum_t">
			<span>
				实付
			</span>
			<span>
				￥161
			</span>
		</p>
		<p class="sum_p">
			<span>
				支付方式
			</span>
			<span>
				预存支付
			</span>
		</p>
	</div>
	<div class="remark">
		<div class="l_remark">订单备注</div>
		<div class="r_remark">配送前请提前打电话告知。谢谢！</div>
	</div>

	<div class="finish_time">
		<div class="l_time">完成时间</div>
		<div class="r_time">2016-11-24 08:00</div>
	</div>
	<div class="blank_c"></div>
	<div class="f_submit">
		<div class="l_sub">
			删除订单
		</div>
		<div class="r_btn evaluate">去评价</div>
	</div>
</body>
</html><?php }
}
?>