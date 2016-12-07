<?php /* Smarty version 3.1.27, created on 2016-12-07 18:04:24
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\modules\Wx\views\order\order_online.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:276735847dea8dd8e68_82090729%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e5501647169c41c4d66f021aeb211e82e1326c03' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\modules\\Wx\\views\\order\\order_online.phtml',
      1 => 1481104183,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '276735847dea8dd8e68_82090729',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_5847dea8df5058_25250497',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5847dea8df5058_25250497')) {
function content_5847dea8df5058_25250497 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '276735847dea8dd8e68_82090729';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0"/>
	<title>在线下单</title>
	<link rel="stylesheet" href="/statics/css/base.css"/>
	<link rel="stylesheet" href="/statics/css/style.css"/>
	<?php echo '<script'; ?>
 src="/statics/js/jquery-1.8.3.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="/statics/js/main.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="/statics/js/radialIndicator.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="/statics/js/laydate/laydate.js"><?php echo '</script'; ?>
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
<style>

</style>
<body id="Vbody">
	<div class="warn_tip">
		<span class="warn_text">微信下单立减5元</span>
		<img class="close" src="/statics/images/close.png" alt="">
	</div>
	<div class="con_detail">
		<ul>
			<li class="b_bottom choose_goods"><span>选择商品</span> <img src="/statics/images/right.png" alt="">
			<span id="goods_list" class="yhj length_over"></span>
			</li>
			<li onclick="location.href='/wx/ucenter/promotion?from=order'" class="b_bottom">
    			<span>优惠券
    				<img src="/statics/images/right.png" alt="">
				</span> 
				<span class="yhj" >
					[[promotion.id==null?promocount+'张':'￥'+promotion.name]]
				</span>
			</li>
			<li><span>配送时间</span><img src="/statics/images/right.png" alt=""><span id="sendtime" class="datat"></span></li>
		</ul>
	</div>
	<div class="note">
		<span>订单备注</span>
		<textarea name="beizhu" id="beizhu" cols="" rows="" placeholder="(选填)对本次交易的说明"></textarea>
	</div>
	<div class="f_submit">
		<div class="l_sub">
			订单金额：
			<img src="/statics/images/money.png" alt="">
			<span class="money">[[order.price]]</span>
		</div>
		<div v-on:click="submitOrder" class="r_btn">提交订单</div>
	</div>
	<!-- 选择规格页 -->
	<div class="mask"></div>
	<div class="choose_item">
		<div class="mask_top">
			<ul>
				<li class="cancel">取消</li>
				<li class="blank_con">清空</li>
				<li class="sure_btn">确定</li>
			</ul>
		</div>
		<div class="mask-con">
		<template v-for="g in goods2">
			<div class="item_spec">
				<ul>
					<li class="name">[[g.name]]</li>
					<li class="weight">[[g.cname]]</li>
					<li class="price">￥[[g.retail_price]]</li>
					<li class="num_btn">
						<div id="b[[g.id]]" gid="[[g.id]]" class="num">
							<span onclick="changeNum(this,-1)" class="sub_btn">-</span>
							<span class="number">[[g.num]]</span>
							<span onclick="changeNum(this,+1)" class="add_btn">+</span>
						</div>
					</li>
				</ul>
			</div>
		</template>
			<!--<div class="item_spec">
				<ul>
					<li class="name">瓶装液化气</li>
					<li class="weight">10kg</li>
					<li class="price">￥45</li>
					<li class="num_btn">
						<div class="num">
							<span class="sub_btn">-</span>
							<span class="number">0</span>
							<span class="add_btn">+</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="item_spec">
				<ul>
					<li class="name">瓶装液化气</li>
					<li class="weight">15kg</li>
					<li class="price">￥55</li>
					<li class="num_btn">
						<div class="num">
							<span class="sub_btn">-</span>
							<span class="number">0</span>
							<span class="add_btn">+</span>
						</div>
					</li>
				</ul>
			</div>-->
		</div>	
	</div>
<!-- <?php echo '<script'; ?>
 src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"><?php echo '</script'; ?>
> -->
<?php echo '<script'; ?>
 type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.1.0.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
	var start = {
	  elem: '#sendtime',
	  format: 'YYYY-MM-DD hh:00',
	  min: laydate.now(), //设定最小日期为当前日期
	  max: '2099-06-16 23:59:59', //最大日期
	  istime: true,
	  istoday: false,
	  choose: function(datas){
	     end.min = datas; //开始日选好后，重置结束日的最小日期
	     end.start = datas //将结束日的初始值设定为开始日
	  }
	};
	laydate(start);
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 type="text/javascript">
var submitable= true;
var promotion = {
			id:GetQueryString('promoid'),
			name:GetQueryString('pname'),
			limit:GetQueryString('limit')
		};
var User = {
	kid:0,
	uname:null,
	phone:null,
	addr:null
};
var Order = {
		goods:Goods,
		//promo:promotion,
		price:0	
}
if(getCookie('goods')!=null){
	var Goods = eval("("+getCookie('goods')+")");console.log(Goods);
	//var Goods = Order.goods;
	
	//promotion
	makePrice();
	showGoods();
}else{
	//商品集合
	var Goods=[];
	//优惠券
	
	
}

var vv = new Vue({
	el: '#Vbody',
	data:{
		//aa:123456
	},
	ready: function() {
		//this.$set('currtime',Date.parse(new Date()));
		//this.getPromotion();
		this.$set('promotion',promotion);
		this.$set('order',Order);
		this.getGoods();
		this.getPromoCount();
		this.getUser();
	},
	methods: {
		//获取用户信息
		getUser:function(){
			this.$http.get('/wx/ucenter/getuser', function(data, status, request) {
				if(data.status==1){
					User = data.data;//alert(User.addr);
					if(User.address==''){
						getSigin();
					}
					this.$set('user', data.data);
				}else{
					this.$set('user', '');
				}
			});
		},
		//获取可用优惠券数量
		getPromoCount:function(){
			this.$http.get('/wx/ucenter/promoCount', function(data, status, request) {
				if(data.status==1){
					this.$set('promocount', data.data.count);
				}else{
					
				}
			});
		},
		//提交订单
		submitOrder:function(){
			setCookie('goods',null);
			var comment = $("#beizhu").val();
			// var phone = $('#phone').val();
			// var vcode = $("#vcode").val();
			// var openid = GetQueryString('openid');
			// var fromurl = GetQueryString('fromurl');
			var bottle_id = [];
			var bottle_num = [];
			for(var i in Goods){
				bottle_id[Goods[i].id]=Goods[i].id+'|'+Goods[i].norm_id+'|'+Goods[i].retail_price;
				bottle_num[Goods[i].id] = Goods[i].num;
			}
			var params = {
					mobile:User.mobile_phone,
					username:User.user_name,
					kid:User.kid,
					address:User.address,
					bottle_id:bottle_id,
					bottle_num:bottle_num,
					comment:comment,
					sendtime:$("#sendtime").html(),
					promotion_id:promotion.id,
					promotion_money:promotion.name
			};
			//console.log(params);
			if(submitable){
				submitable=false;
				$.ajax({
					  type: 'POST',
					  url: '/wx/order/createorder',
					  data: params,
					  cache:false,  
					  dataType:'json',
					  success: function(data){
						  if(data.code==1){
							 //dig("保存成功！");alert
							 var url = 'http://'+window.location.host+"/wx/order/vpay?id="+data.data.id;
							 //alert(url);
							 location.href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxccf5868dd605affe&redirect_uri='+encodeURIComponent(url).toLowerCase()+'&response_type=code&scope=snsapi_userinfo&state=5#wechat_redirect';
						  }else{
							 dig(data.msg);
						  }
					  },
					  error:function (){
						  //dig('系统繁忙！');
					  }
				});
				setTimeout(function(){
					submitable=true;
				},2000);
			}
			
		},
		//获取商品
		getGoods:function(){
			this.$http.get('/wx/order/getgoods',function(data, status, request){
				if(data.status==1){
					
					if(Goods==null || Goods.length==0){
						Goods=data.data;
					}
					this.$set('goods2',Goods);
				}else{
					this.$set('goods2','');
				}
			});
		}
	}
});
function changeNum(obj,num){
	var id = $(obj).parent().attr('gid');
	for(var i in Goods){
		if(Goods[i].id==id){
			Goods[i].num = parseInt(Goods[i].num)+num;
			if(Goods[i].num<0){
				Goods[i].num=0;
			}
		}
	}
	vv.$set('goods2',Goods);//console.log(vv.aa);
}
//获取共享地址
function getSigin(){
		var appId='';
		var timestamp='';
		var nonceStr='';
		var signature='';
		var url='';
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
			}
		});

		//var title='【秒小空】'+coName+',正在急聘'+jmName+',快来推荐,快拿佣金吧！';//分享标题
		//var link='http://web.miaoxiaokong.com/co_jobshow.html?jmid='+urlhref;//分享链接
    	//var link='http://web.miaoxiaokong.com/indexshare.html?jmid='+urlhref+'&S_ID='+s_id+'&CM_ID='+cm_id;//分享链接
		//var imgUrl='http://m.miaozhunpin.com/static/img/fenxiang.png';//分享图片
		//var desc='秒小空已经有10000+人次成功推荐办理入职';//描述
		wx.config({
			debug: false,
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
		wx.ready(function () {
			//alert(wx.openAddress);
			wx.openAddress({
			     success: function (res) {
			          // 用户成功拉出地址
			          //alert(data);
			          var params = {
			          	addr:res.provinceName+res.cityName+res.detailInfo
			          };
			          $.ajax({
						  type: 'POST',
						  url: '/wx/ucenter/upaddr',
						  data: params,
						  cache:false,  
						  dataType:'json',
						  success: function(data){
							  if(data.status==1){
								 dig("保存成功！");
								 //location.href="/wx/order/vpay?id="+data.data.id;
							  }else{
								 dig(data.msg);
							  }
						  },
						  error:function (){
							  dig('系统繁忙！');
						  }
					});
			     },
			     cancel: function (data) {
			          // 用户取消拉出地址
			    	 dig(data);
				 }
			});
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