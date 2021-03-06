<?php /* Smarty version 3.1.27, created on 2016-12-09 10:48:50
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\ordermanage\order_management.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:23082584a1b92b71785_60605354%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7605af652b5c9b834365ac41c3fce50e33d70176' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\ordermanage\\order_management.phtml',
      1 => 1481250501,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '23082584a1b92b71785_60605354',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_584a1b92b7f553_92958780',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_584a1b92b7f553_92958780')) {
function content_584a1b92b7f553_92958780 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '23082584a1b92b71785_60605354';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>订单管理</title>
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
	<div class="t_manager">
		<ul>
			<li class="blue_bottom"><span class="r_tip blue_text">全部</span></li>
			<li><span class="r_tip">待收货</span></li>
			<li><span class="r_tip">已完成</span></li>
			<li><span>已评价</span></li>
		</ul>
	</div>
	<!--  全部-->
	<div class="all">
		<div  v-for="order in orders" class="all_detail">
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'" class="all_head">
				<span>订单号：</span>
				<span>[[order.order_sn]]</span>
				<span>[[order.orderstatus]]</span>
			</p>
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'">
				<span class="st">商品：</span>
				<span class="ell"><template v-for="info in order.order_info">[[info.goods_name]],</template> </span>
			</p>
			<p class="f_border" onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'">
				<span>金额：</span>
				<span>￥[[order.money]]</span>
				<span class="l_">[[order.order_paytype==1?'网上支付':'现金']]</span>
			</p>
			<div v-if="order.status==4 && order.is_evaluation==0" onclick="location.href='/wx/ordermanage/comment?orderid=[[order.order_id]]'" class="go_eval">去评价</div>
			<div v-if="order.status==4 && order.is_evaluation==1" onclick="location.href='/wx/ordermanage/comment?orderid=[[order.order_id]]'" class="go_eval">已评价</div>
		</div>
		
	</div>
	<!--  待收货-->
	<div class="wait_goods">
		
		<template v-for="order in orders">
		<div  v-if="order.status==2"  class="all_detail go_ps">
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'" class="all_head">
				<span>订单号：</span>
				<span>[[order.order_sn]]</span>
				<span>[[order.orderstatus]]</span>
			</p>
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'">
				<span class="st">商品：</span>
				<span class="ell">
				<template v-for="info in order.order_info">[[info.goods_name]],</template>
				...
				</span>
			</p>
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'">
				<span>金额：</span>
				<span>￥[[order.money]]</span>
				<span class="l_">[[order.order_paytype==1?'网上支付':'现金']]</span>
			</p>
		
		</div></template>
		
	</div>
	<!-- 已完成 -->
	<div class="finish_order">
		
		<template v-for="order in orders">
		<div  v-if="order.status==4"  class="all_detail">
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'" class="all_head">
				<span>订单号：</span>
				<span>[[order.order_sn]]</span>
				<span>[[order.orderstatus]]</span>
			</p>
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'">
				<span class="st">商品：</span>
				<span class="ell"><template v-for="info in order.order_info">[[info.goods_name]],</template>...</span>
			</p>
			<p class="f_border" onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'">
				<span>金额：</span>
				<span>￥[[order.money]]</span>
				<span class="l_">[[order.order_paytype==1?'网上支付':'现金']]</span>
			</p>
			<div v-if="order.is_evaluation==0" onclick="location.href='/wx/ordermanage/comment?orderid=[[order.order_id]]'" class="go_eval">去评价</div>
			<div v-if="order.is_evaluation==1" onclick="location.href='/wx/ordermanage/comment?orderid=[[order.order_id]]'" class="go_eval">已评价</div>
		
		</div></template>
	</div>
	<!-- 已评价 -->
	<div class="finish_eval">
		
		<template v-for="order in orders">
		<div  v-if="order.is_evaluation==1"  class="all_detail go_detail">
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'" class="all_head">
				<span>订单号：</span>
				<span>[[order.order_sn]]</span>
				<span>[[order.orderstatus]]</span>
			</p>
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'">
				<span class="st">商品：</span>
				<span class="ell"><template v-for="info in order.order_info">[[info.goods_name]],</template></span>
			</p>
			<p onclick="location.href='/wx/ordermanage/detail?id=[[order.order_id]]'">
				<span>金额：</span>
				<span>￥[[order.money]]</span>
				<span class="l_">[[order.order_paytype==1?'网上支付':'现金']]</span>
			</p>
		</div></template>
	</div>
<?php echo '<script'; ?>
 type="text/javascript">
new Vue({
	el: '#Vbody',
	ready: function() {
		this.getOrder();
	},
	methods: {
		//获取订单
		getOrder:function(){
			this.$http.get('/wx/ordermanage/getlist', function(data, status, request) {
				
				if(data.status==1){
					this.$set('orders', data.data);
				}else{
					//alert(123);
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