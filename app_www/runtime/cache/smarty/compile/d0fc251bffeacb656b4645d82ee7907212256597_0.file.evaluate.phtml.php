<?php /* Smarty version 3.1.27, created on 2016-12-02 17:36:43
         compiled from "E:\xampp\htdocs\rq\app_www\modules\Wx\views\ordermanage\evaluate.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:14003584140abacaf09_58344988%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd0fc251bffeacb656b4645d82ee7907212256597' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\app_www\\modules\\Wx\\views\\ordermanage\\evaluate.phtml',
      1 => 1480671382,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14003584140abacaf09_58344988',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_584140abad5953_67287731',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_584140abad5953_67287731')) {
function content_584140abad5953_67287731 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '14003584140abacaf09_58344988';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>评价</title>
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
	<div class="goods_detail" style="margin-top: 0">
		<table >
			<tr>
				<td>商品名称</td>
				<td>规格</td>
				<td>单价</td>
				<td>数量</td>
			</tr>
			<template v-for="info in order_info">
				<tr>
					<td>[[info.name]]</td>
					<td>[[info.type]]</td>
					<td>[[info.goods_price]]</td>
					<td>[[info.goods_num]]</td>
				</tr>
			</template>
		</table>
	</div>
	<div class="eval_con">
		<ul class="choose_eval">
			<li><img class="ch_btn cirle_btn" src="/statics/images/btn_f.png" alt="">好评<img class="flow" src="/statics/images/hao.png" alt=""></li>
			<li><img class="ch_btn cirle_btn" src="/statics/images/btn_f.png" alt="">中评<img class="flow" src="/statics/images/zhong.png" alt=""></li>
			<li><img class="ch_btn cirle_btn" src="/statics/images/btn_f.png" alt="">差评<img class="flow" src="/statics/images/cha.png" alt=""></li>
		</ul>
		<div class="eval_text">
			<textarea name="" id="" placeholder="说说你的想法"></textarea>
		</div>
		<div class="finish">提交</div>
		
	</div>
<?php echo '<script'; ?>
 type="text/javascript">

new Vue({
	el: '#Vbody',
	ready: function() {
		this.$set('currtime',Date.parse(new Date()));
		this.getOrder();
	},
	methods: {
		//获取订单
		getOrder:function(){
			var id = GetQueryString('orderid');
			this.$http.get('/wx/ordermanage/one?id='+id, function(data, status, request) {
				if(data.status==1){
					this.$set('order_info', data.data.order_info);
				}else{
					
				}
				
				//this.redirect(data);
				//console.log(data);
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