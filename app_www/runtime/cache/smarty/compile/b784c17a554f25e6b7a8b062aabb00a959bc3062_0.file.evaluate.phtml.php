<?php /* Smarty version 3.1.27, created on 2016-12-05 10:22:27
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\ordermanage\evaluate.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:179405844cf635e8319_66083097%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b784c17a554f25e6b7a8b062aabb00a959bc3062' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\ordermanage\\evaluate.phtml',
      1 => 1480904544,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '179405844cf635e8319_66083097',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_5844cf635f5ec0_93360417',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5844cf635f5ec0_93360417')) {
function content_5844cf635f5ec0_93360417 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '179405844cf635e8319_66083097';
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
			<li><img onclick="type=1;" class="ch_btn cirle_btn" src="/statics/images/btn_[[type.hp]].png" alt="">好评<img class="flow" src="/statics/images/hao.png" alt=""></li>
			<li><img onclick="type=2;" class="ch_btn cirle_btn" src="/statics/images/btn_[[type.zp]].png" alt="">中评<img class="flow" src="/statics/images/zhong.png" alt=""></li>
			<li><img onclick="type=3;" class="ch_btn cirle_btn" src="/statics/images/btn_[[type.cp]].png" alt="">差评<img class="flow" src="/statics/images/cha.png" alt=""></li>
		</ul>
	<template v-if="pl==0">
		
		<div class="eval_text">
			<textarea name="comment" id="comment" placeholder="说说你的想法">[[pl.comment]]</textarea>
		</div>
		<div v-on:click="addComment" class="finish">提交</div>
	</template>
	<template v-else>
		<div class="eval_text">
			<textarea readonly="readonly" name="comment" id="comment" placeholder="说说你的想法">[[pl.comment]]</textarea>
		</div>
	</template>
	</div>
<?php echo '<script'; ?>
 type="text/javascript">
var type = 1;
var ordersn = null;
var type={
		hp:'f',
		zp:'f',
		cp:'f'
	};
new Vue({
	el: '#Vbody',
	ready: function() {
		this.$set('currtime',Date.parse(new Date()));
		this.getOrder();
		this.$set('type',type);
	},
	methods: {
		//获取订单
		getOrder:function(){
			var id = GetQueryString('orderid');
			this.$http.get('/wx/ordermanage/one?id='+id, function(data, status, request) {
				if(data.status==1){
					ordersn = data.data.order_sn;
					this.$set('order_info', data.data.order_info);
					this.getComment();
				}else{
					
				}
				
				//this.redirect(data);
				//console.log(data);
			});
		},
		getComment:function(){
			this.$http.get('/wx/ordermanage/getcomment?ordersn='+ordersn,function(data, status, request){
				if(data.status==1){
					
					switch(data.data.type){
						case '1':
							type.hp='t';
						break;
						case '2':
							type.zp='t';
						break;
						case '3':
							type.cp='t';
						break;
					}
					this.$set('type',type);
					this.$set('pl',data.data);
				}else{
					this.$set('pl',0);
				}
				
			});
		},
		addComment:function(){
			var params = {
				type:type,
				comment:$("#comment").val(),
				ordersn:ordersn,
			};
			$.ajax({
				  type: 'POST',
				  url: '/wx/ordermanage/addcomment',
				  data: params,
				  cache:false,  
				  dataType:'json',
				  success: function(data){
					  if(data.status==1){
						 //dig("保存成功！");alert
						 alert("评论成功");
					  }else{
						 alert("评论失败");
					  }
				  },
				  error:function (){
					  //dig('系统繁忙！');
				  }
			});
		}
	}
})
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
?>