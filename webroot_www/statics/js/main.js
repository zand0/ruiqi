function GetQueryString(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}
function makePrice(){
    Order.price = 0;
	//var totalprice = Order.price;
	var promoprice = promotion.name;//console.log(promotion.name);
    var limit = promotion.limit;
	for(var i in Goods){
		if(Goods[i].num!=0){
			//str_gname += Goods[i].name+Goods[i].cname+'x'+Goods[i].num+' ';
			Order.price += Goods[i].direct_price*Goods[i].num;
		}
	}
    //alert(Order.price);alert(promoprice);
	if(Order.price > limit){
		Order.price = Order.price - promoprice;
	}else{
        //Order.price = 0;
		//Order.price += parseInt(Goods[i].direct_price)*Goods[i].num;
	}
	
}
function saveO2J(obj,name){
    //console.log(obj);
    var str = JSON.stringify(obj);
    setCookie(name,str,null);
}
function setCookie(c_name,value,expiredays){
    var exdate=new Date()
    exdate.setDate(exdate.getDate()+expiredays)
    document.cookie=c_name+ "=" +escape(value)+
    ((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
}
function getCookie(c_name){
    if (document.cookie.length>0){
        c_start=document.cookie.indexOf(c_name + "=");
    if (c_start!=-1){ 
        c_start=c_start + c_name.length+1 ;
        c_end=document.cookie.indexOf(";",c_start);
        if (c_end==-1) c_end=document.cookie.length
            return unescape(document.cookie.substring(c_start,c_end));
        } 
    }
    return null;
}
function showGoods(){
    var str_gname = '';
    for(var i in Goods){
        if(Goods[i].num!=0){
            str_gname += Goods[i].name+Goods[i].cname+'x'+Goods[i].num+' ';
            //Order.price += parseInt(Goods[i].direct_price)*Goods[i].num;
        }
    }
    $("#goods_list").html(str_gname);
}
$(function(){  
	// 提示信息
    $('.close').click(function(){
    	$('.warn_tip').hide();
    });
    // 规格选择页
    $('.choose_goods').click(function(){
    	$('.mask').css("display","block");
    	$('.choose_item').css("display","block");
    });
    // 减一
    /*$('.sub_btn').click(function(){
    	var num = $('.number').html();
    	if(num>=1){
    		num--;
	    	$('.number').html(num);
    	}  	
    });
    // 加一
    $('.add_btn').click(function(){
    	alert($(this).parent().attr('id'));
    	var num = $('.number').html();
    	if(num>=0){
    		num++;
	    	$('.number').html(num);
    	}  	
    });*/
    // 取消选择
    $('.cancel').click(function() {
    	$('.mask').css("display","none");
    	$('.choose_item').css("display","none");
    	
    });
    // 清空
    $('.blank_con').click(function(){
    	//$('.number').html(0);
    	for(var i in Goods){
    		Goods[i].num = 0;
    	}
    	makePrice();
        saveO2J(Order,'order');
    });
    // 确定
    $('.sure_btn').click(function(){
    	$('.mask').css("display","none");
    	$('.choose_item').css("display","none");
    	//window.location.href="pay_money.html";
    	showGoods();
        makePrice();
        saveO2J(Goods,'goods');
    	//console.log(str_gname);
    });
    // 支付方式选择页
    $('.r_btn').click(function(){
        $('.mask').css("display","block");
        $('.pay_mode').css("display","block");
    });
    // 取消支付
     $('.l_cancel').click(function() {
        $('.mask').css("display","none");
        $('.pay_mode').css("display","none");
    });
    //单选框
    $('.choose').click(function(){
        if($(this).attr("src")=="/statics/images/btn_f.png"){
            $(this).attr("src", "/statics/images/btn_t.png"); 
        }else{
            $(this).attr("src", "/statics/images/btn_f.png"); 
        }
        
    }) ;
    $('.t_coupon li').click(function(){
        $(this).addClass("blue_bottom").siblings().removeClass("blue_bottom");
        $(this).children().addClass("blue_text").parent().siblings().children().removeClass("blue_text");
        if( $('.t_coupon li').eq(0).hasClass('blue_bottom')){
            $('.nouse_c').css("display","block");
            $('.use_c').css("display","none");
            $('.delay_c').css("display","none");
        }
        if( $('.t_coupon li').eq(1).hasClass('blue_bottom')){
            $('.use_c').css("display","block");
            $('.nouse_c').css("display","none");
            $('.delay_c').css("display","none");
        }
        if( $('.t_coupon li').eq(2).hasClass('blue_bottom')){
            $('.delay_c').css("display","block");
            $('.use_c').css("display","none");
            $('.nouse_c').css("display","none");
        }
    });
    $('.evaluate').click(function(){
        //window.location.href="/wx/ordermanage/comment";
    });
    // 评价选择
    $('.cirle_btn').click(function(){
        $(this).parent().siblings().children('.cirle_btn').attr("src", "/statics/images/btn_f.png");
        if($(this).attr("src")=="/statics/images/btn_f.png"){
            $(this).attr("src", "/statics/images/btn_t.png"); 
        }else{
            $(this).attr("src", "/statics/images/btn_f.png"); 
        }
    });
    // 订单管理head
    $('.t_manager li').click(function(){
        $(this).addClass("blue_bottom").siblings().removeClass("blue_bottom");
        $(this).children().addClass("blue_text").parent().siblings().children().removeClass("blue_text");
        if( $('.t_manager li').eq(0).hasClass('blue_bottom')){
            $('.all').css("display","block");
            $('.wait_goods').css("display","none");
            $('.finish_order').css("display","none");
            $('.finish_eval').css("display","none");

        }
        if( $('.t_manager li').eq(1).hasClass('blue_bottom')){
            $('.wait_goods').css("display","block");
            $('.all').css("display","none");
            $('.finish_order').css("display","none");
            $('.finish_eval').css("display","none");
        }
        if( $('.t_manager li').eq(2).hasClass('blue_bottom')){
            $('.finish_order').css("display","block");
            $('.wait_goods').css("display","none");
            $('.all').css("display","none");
            $('.finish_eval').css("display","none");
        }
        if( $('.t_manager li').eq(3).hasClass('blue_bottom')){
            $('.finish_eval').css("display","block");
            $('.wait_goods').css("display","none");
            $('.all').css("display","none");
            $('.finish_order').css("display","none");

        }
    });
    $('.go_eval').click(function(){
        window.location.href="/wx/ordermanage/comment";
    });
    $('.go_ps').click(function(){
        window.location.href="order_ps.html";
    });
    $('.go_no_ps').click(function(){
        window.location.href="order_detail_no.html";
    });

    // 我的页面
    $('.go_coupon').click(function(){
        window.location.href="/wx/ucenter/promotion";
    });
    $('.go_order').click(function(){
        window.location.href="/wx/ordermanage/list";
    });
}); 