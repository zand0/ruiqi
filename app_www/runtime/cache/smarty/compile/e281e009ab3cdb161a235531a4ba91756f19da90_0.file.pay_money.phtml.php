<?php /* Smarty version 3.1.27, created on 2016-12-05 17:01:00
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\order\pay_money.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:2073558452cccc1f683_58009520%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e281e009ab3cdb161a235531a4ba91756f19da90' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\order\\pay_money.phtml',
      1 => 1480928458,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2073558452cccc1f683_58009520',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_58452cccc2d9a2_18366590',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_58452cccc2d9a2_18366590')) {
function content_58452cccc2d9a2_18366590 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '2073558452cccc1f683_58009520';
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
	<?php echo '<script'; ?>
 type="text/javascript" src="/statics/js/Vue.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 type="text/javascript" src="/statics/js/vue-resource.min.js"><?php echo '</script'; ?>
>
	<?php echo $_smarty_tpl->getSubTemplate ('../header.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

</head>
<body id="Vbody">
	<div class="address_detail">
		<div class="l_img">
			<img src="/statics/images/location.png" alt="">
		</div>
		<div class="r_con">
			<ul>
				<li>
					<span class="user_name">[[user.user_name]]</span>
					<span class="user_tel">[[user.mobile_phone]]</span>
				</li>
				<li>
					[[user.address]]
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
			<template v-for="info in order.goods">
			<tr>
				<td>[[info.name]]</td>
				<td>[[info.type]]</td>
				<td>[[info.price]]</td>
				<td>[[info.num]]</td>
			</tr>
			</template>
			
		</table>
	</div>
	<div class="else-info">
		<ul>
			<li><span>优惠券</span><span>-[[order.promotion_money]]</span></li>
			<li><span>配送时间</span><span>[[order.sendtime_str]]</span></li>
			<li><span>订单备注</span><span>[[order.comment]]</span></li>
		</ul>
	</div>
	<div class="f_submit">
		<div class="l_sub">
			订单金额：
			<img src="/statics/images/money.png" alt="">
			<span class="money">[[totalprice]]</span>
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
<?php echo '<script'; ?>
 type="text/javascript">
var id = GetQueryString('id');
new Vue({
	el: '#Vbody',
	ready: function() {
		this.getOrder();
		this.getUser();
	},
	methods: {
		//获取订单
		getOrder:function(){
			this.$http.get('/wx/order/getordertmp?id='+id, function(data, status, request) {
				if(data.status==1){
					var totalpric = 0;
					var goods=data.data.goods;
					for(var i in goods){
						totalpric += goods[i].num*goods[i].price; 
					}
					totalpric -= data.data.promotion_money; 
					this.$set('totalprice',totalpric);
					this.$set('order', data.data);
				}else{

				}
				
			});
		},
		getUser:function(){
			this.$http.get('/wx/ucenter/getuser', function(data, status, request) {
				if(data.status==1){
					this.$set('user', data.data);
				}else{
					this.$set('user', '');
				}
			});
		},
	}
})
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
?>