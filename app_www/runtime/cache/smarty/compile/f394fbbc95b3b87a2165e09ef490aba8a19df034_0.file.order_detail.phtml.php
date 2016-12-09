<?php /* Smarty version 3.1.27, created on 2016-12-09 13:41:08
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\ordermanage\order_detail.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:7378584a43f476e918_64644439%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f394fbbc95b3b87a2165e09ef490aba8a19df034' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\ordermanage\\order_detail.phtml',
      1 => 1481261903,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '7378584a43f476e918_64644439',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_584a43f477c076_26720148',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_584a43f477c076_26720148')) {
function content_584a43f477c076_26720148 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '7378584a43f476e918_64644439';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>订单详情</title>
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
<body id="Vbody" style="overflow: inherit;">
	<div class="order_num">
		<span>订单号：[[order.order_sn]]</span>
		<span class="state">[[order.orderstatus]]</span>
	</div>
	<div class="address_details">
		<div class="l_img">
			<img src="/statics/images/location.png" alt="">
		</div>
		<div class="r_con">
			<ul>
				<li>
					<span class="user_name name_word">用户名：[[order.username]]</span>
					<span class="user_tel">手机号：[[order.mobile]]</span>
				</li>
				<li>
					[[order.address]]
				</li>
			</ul>
		</div>
	</div>
	<div class="staff">
		<p>
			<span>配送门店</span>
			<span class="s_con">[[order.shop_name]]</span>
		</p>
		<p>
			<span>送气工</span>
			<span class="s_con">[[order.shipper_name]]（[[order.shipper_mobile]]）</span>
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
			<template v-for="info in order.order_info">
			<tr>
				<td>[[info.name]]</td>
				<td>[[info.cname==undefined?info.type:'']]</td>
				<td>[[info.goods_price]]</td>
				<td>[[info.goods_num]]</td>
			</tr>
			</template>
		</table>
	</div>
	<!-- <div class="all_sum">
		<p class="sum_t">
			<span>
				商品金额
			</span>
			<span>
				￥[[order.money]]
			</span>
		</p>
		<p class="sum_t">金额</p>
		<template v-for="info in order.order_info">
		<p class="sum_c">
			<span>[[info.cname]]</span>
			<span>[[info.goods_num]]</span>
			<span>￥[[info.goods_price]]</span>
		</p>
		</template>
		
	</div> -->
	
	<div class="wx_order">
		<p v-if="order.order_type==0" class="sum_t">
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
				-￥[[order.residual_gas]]
			</span>
		</p>
		<p class="sum_t">
			<span>
				残液抵扣
			</span>
			<span>
				-￥[[order.raffinat==null?0:order.raffinat]]
			</span>
		</p>
		<p class="sum_t">
			<span>
				折旧
			</span>
			<span>
				-￥[[order.depreciation]]
			</span>
		</p>
		<!-- <p>折旧</p> -->
		<!-- <p class="sum_cc">
			<span>30kg</span>
			<span>08年</span>
			<span>2</span>
			<span>-￥30</span>
		</p> -->
	</div>
	<div class="pay_con">
		<p class="sum_t">
			<span>
				押金
			</span>
			<span>
				￥[[order.deposit]]
			</span>
		</p>
		<p class="sum_t">
			<span>
				商品应付金额：
			</span>
			<span>
				￥[[order.status==4?order.pay_money-order.deposit:order.money-order.deposit]]
			</span>
		</p>
		<p class="sum_t">
			<span>
				实付
			</span>
			<span>
				￥[[order.status==4?order.pay_money:order.money]]
			</span>
		</p>
		<p class="sum_p">
			<span>
				支付方式
			</span>
			<span>
				<template v-if="order.order_paytype==0">
					现金
				</template>
				<template v-if="order.order_paytype==1">
					网上支付
				</template>
			</span>
		</p>
	</div>
	<div class="remark">
		<div class="l_remark">订单备注</div>
		<div class="r_remark" style="word-break:break-all">[[order.comment]]</div>
	</div>

	<div class="finish_time">
		<div class="l_time">下单时间</div>
		<div class="r_time">[[order.otime_str]]</div>
	</div>
	<div class="finish_time">
		<div class="l_time">配送时间</div>
		<div class="r_time">[[order.good_time_str]]</div>
	</div>
	<div class="blank_c"></div>
	<template v-if="order.status==4">
	<div class="f_submit">
		<!-- <div class="l_sub">
			删除订单
		</div> -->
		<div onclick="location.href='/wx/ordermanage/comment?orderid=[[order.order_id]]'" class="r_btn evaluate">[[order.is_evaluation==1?'已评价':'去评价']]</div>
	</div>
	</template>
<?php echo '<script'; ?>
 type="text/javascript">
var id = GetQueryString('id');
new Vue({
	el: '#Vbody',
	ready: function() {
		this.getOrder();
	},
	methods: {
		//获取订单
		getOrder:function(){
			this.$http.get('/wx/ordermanage/one?id='+id, function(data, status, request) {
				
				if(data.status==1){
					this.$set('order', data.data);
				}else{

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