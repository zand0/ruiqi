<?php /* Smarty version 3.1.27, created on 2016-12-09 10:49:11
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\order\pay_money.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:29196584a1ba7f1dce3_92243535%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e281e009ab3cdb161a235531a4ba91756f19da90' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\order\\pay_money.phtml',
      1 => 1481251117,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '29196584a1ba7f1dce3_92243535',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_584a1ba7f36023_55816226',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_584a1ba7f36023_55816226')) {
function content_584a1ba7f36023_55816226 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '29196584a1ba7f1dce3_92243535';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>提交订单</title>
	<link rel="stylesheet" href="/statics/css/base.css"/>
	<!-- <link rel="stylesheet" href="/statics/css/style.css"/> -->
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
					<span class="user_name name_word">用户名：[[user.user_name]]</span>
					<span class="user_tel">手机号：[[user.mobile_phone]]</span>
				</li>
				<li onclick="getSigin()">
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
		<div class="r_btn submit_btn">立即支付</div>
	</div>
	<!-- 选择支付方式页 -->
	<div class="mask"></div>
	<div class="pay_mode">
		<h1>选择支付方式</h1>
		<ul>
			<li><img class="icon_pay" src="/statics/images/xianj.png" alt="">货到付款<img onclick="payType=0;" class="choose c_circle" src="/statics/images/btn_f.png" alt=""></li>
			<li><img class="icon_pay" src="/statics/images/weixin.png" alt="">微信缴费<span>（功能暂未开放）</span><img onclick="payType=1" class="choose c_circle" src="/statics/images/btn_f.png" alt=""></li>
		</ul>
		<div class="f_pay">
			<div class="l_cancel">取消</div>
			<div v-on:click="pay" class="r_sure">确定</div>
		</div>
	</div>
<?php echo '<script'; ?>
 type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.1.0.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
var payType=0;
var payMoney = 0;
var Promoid = 0;
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
					if(totalpric>data.data.promotion_money){
						totalpric -= data.data.promotion_money;
					}else{
						totalpric = 0;
					}
					
					if(totalpric>5){
						totalpric -= 5;
					}
					payMoney =  totalpric;
					Promoid = data.data.promotion_id;
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
		pay:function(){
			var params = {
				paytype:payType,
				paymoney:payMoney,
				promoid:Promoid
			};

			if(params.paytype==1){
				location.href='';//支付页面
				return;
			}
			$.ajax({
				  type: 'GET',
				  url: '/wx/order/pay',
				  data: params,
				  cache:false,  
				  dataType:'json',
				  success: function(data){
					  if(data.status==1){
						//dig("绑定成功！");
						location.href="/wx/ucenter/my";
					  }else{
						dig(data.msg);
					  }
				  },
				  error:function (){
					  //dig('系统繁忙！');
				  }
			});
		}
	}
})

//获取共享地址
function getSigin(){
	return;
		var appId='';
		var timestamp='';
		var nonceStr='';
		var signature='';
		var url='';
		var addrSign = '';
		var targetUrl=location.href.split("#")[0];
		$.ajax({
			url:'/wx/order/sign?url='+encodeURIComponent(targetUrl),
			type:'get',
			dataType:'json',
			async : false, //默认为true 异步
			error:function(err){
				console.log(err);
			},
			success:function(data){
				appId=data.data.appId;
				timestamp=data.data.timestamp;
				nonceStr=data.data.nonceStr;
				signature=data.data.signature;
				url  =	data.data.url;
				addrSign = data.data.addrSign;
				//alert(addrSign);
			}
		});

		//var title='【秒小空】'+coName+',正在急聘'+jmName+',快来推荐,快拿佣金吧！';//分享标题
		//var link='http://web.miaoxiaokong.com/co_jobshow.html?jmid='+urlhref;//分享链接
    	//var link='http://web.miaoxiaokong.com/indexshare.html?jmid='+urlhref+'&S_ID='+s_id+'&CM_ID='+cm_id;//分享链接
		//var imgUrl='http://m.miaozhunpin.com/static/img/fenxiang.png';//分享图片
		//var desc='秒小空已经有10000+人次成功推荐办理入职';//描述
		wx.config({
			debug: true,
			appId: appId,
			timestamp: timestamp,
			nonceStr: nonceStr,
			signature: signature,
			jsApiList: [
				'openAddress',
				'checkJsApi',
			    'editAddress',
			    'chooseWXPay',
			    'getLatestAddress',
			    'openCard',
			    'getLocation'
			]
		});

		/*wx.checkJsApi({
		    jsApiList: [
		    	'openAddress',
		    	'checkJsApi',
			    'editAddress',
			    'chooseWXPay',
			    'getLatestAddress',
			    'openCard',
			    'getLocation'
		    ], // 需要检测的JS接口列表，所有JS接口列表见附录2,
		    success: function(res) {
		        // 以键值对的形式返回，可用的api值true，不可用为false
		        
		        
		    }
		});*/
		var editConfig = {
			"appId": appId,
			"scope": "jsapi_address",
			"signType": "sha1",
			"addrSign": addrSign,
			"timeStamp": timestamp,
			"nonceStr": nonceStr,
		};

		console.log(editConfig);

		WeixinJSBridge.invoke('editAddress', editConfig, function (res) {
			//若res 中所带的返回值不为空，则表示用户选择该返回值作为收货地址。
			//否则若返回空，则表示用户取消了这一次编辑收货地址。
			alert(res.err_msg);
		});
		//});
		wx.ready(function () {
			
		});

		wx.error(function (res) {
			dig(res.errMsg);  //打印错误消息。及把 debug:false,设置为debug:ture就可以直接在网页上看到弹出的错误提示
		});
	}
<?php echo '</script'; ?>
>
</body>
</html><?php }
}
?>