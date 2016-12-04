$(function () {
    // 控制页面背景高度
    controlsSize();
    // add by zls

    //头部选项卡
    $('.contNav ul li').on('click', function () {
        var _index = $(this).index();
        $(this).addClass('active').siblings().removeClass('active');
        $('.contLeft ul').eq(_index).show().siblings().hide();
    });
    // 左边选项卡
    $('.contLeft ul').each(function () {
        $(this).children('li').on('click', function () {
            $(this).addClass('active').siblings().removeClass('active');
        });
    });

    //底部是否固定
    footFixed();
    $(window).on('resize', function () {
        controlsSize();
    });

    // 选项卡
    tab($('.rq_tab'));

    // 文本框提示
    placeHolder($('.placeholder'));

    //初始化下拉框
    if ($(".selectmenu").length > 0) {
        $(".selectmenu").selectmenu();
    }
    //初始化分页
    if($('#pageNav').length>0){
        if(typeof page == 'undefined') page = 1;
        if(typeof totalPage == 'undefined') totalPage = 1;
        if(typeof pageCall == 'undefined') pageCall = '';
        var pager = new PageNav({
            currentPage:page,
            totalPage:totalPage,
            wrapId:'#pageNav',
            callback:pageCall
        })
    }
    //单日历
    if ($('.datepicker').length > 0) {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            showOn: 'button',
            buttonImage: '../../statics/images/calendar.png',
            buttonImageOnly: true,
            buttonText: 'Select date'
        });
    }
    //初始化级联日历
    if ($('#time_start').length > 0) {
        $("#time_start").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            showOn: "both",
            buttonImage: "/statics/images/calendar.png",
            buttonImageOnly: true,
            onClose: function (selectedDate) {
                $("#time_end").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#time_end").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            showOn: "both",
            buttonImageOnly: true,
            buttonImage: "/statics/images/calendar.png",
            onClose: function (selectedDate) {
                $("#time_start").datepicker("option", "maxDate", selectedDate);
            }
        });
    }
    //复选框
    $('.checkList a').on('click', function () {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });
    //呼叫中心-客户信息-创建新客户按钮
    isShow($('#createClient'), $('.createNewClient'));
    // add by zls  2015.12.2
    // 复选框
    $('.mycheckbox').each(function () {
        $(this).find('.checkList a').on('click', function () {
            var arr = [];
            $(this).parent('.checkList').children('a').each(function () {
                if ($(this).hasClass('active')) {
                    arr.push($(this).html());
                }
            });
            $(this).parents('.mycheckbox').find('input').val(arr.join(','));
        });
    });
    // 首页--登录按钮
    $('#loginBtn').on('click', function () {
        if ($('.operationBtn .checkList a').hasClass('active')) {
            var user = $('.loginBox .username input').val();
            var psd = $('.loginBox .psd input').val();
            $.cookie('username', user, {'expires': 7});
            $.cookie('psd', psd, {'expires': 7});
        } else {
            $.cookie('username', {'expires': -1});
            $.cookie('psd', {'expires': -1});
        }
    });
    //在线订气--个人资料--添加电话
    $('#addTel').on('click', function () {
        $('<div class="inputBox placeholder"><input type="text" name="mobile_list[]" style="width:260px;"><i></i></div>').appendTo($(this).siblings('.addDelete'));
    });
    //在线订气--个人资料-添加地址
    $('#addAddress').on('click', function () {
        $('<div class="inputBox  placeholder"><input type="text" name="address_list[]" style="width:435px;"><span>输入详细地址</span><i></i></div>').appendTo($(this).siblings('.addDelete'))
    });

    // 在线订气--个人资料-删除按钮
    $(document).on('click', '.addDelete .inputBox i', function () {
        $(this).parents('.inputBox').remove();
    });
    // 企业动态 安全常识  展开收起
    $('.articalList .isSpread a').on('click', function () {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active').html('展开阅读');
            $(this).parents('li').find('.artical').css({'maxHeight': '84px'});
        } else {
            $(this).addClass('active').html('收起');
            $(this).parents('li').find('.artical').css({'maxHeight': 'inherit'});
        }
    });
    //首页头部导航条
    $('.head_left ul li').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
    });
    // 首页轮播图
    //首页轮播图
    if($('#tab')[0]){
        var oUl=$('#tab ul')[0];
        var aLi=oUl.children;
        var aBtn=$('#tab ol')[0].children;
        //复制内容
        oUl.innerHTML+=oUl.innerHTML;
        //算ul宽度
        oUl.style.width=aLi.length*aLi[0].offsetWidth+'px';
        var W=oUl.offsetWidth/2;
        var iNow=0;
        var timer2=null;
        var data=['国庆节活动','国庆节活动2222','国庆节活动3333','国庆节活动4444','国庆节活动555']
        $('#tab ol').hover(function (){
            clearInterval(timer2);
        },function (){
            next();
        });
        //按钮点击
        for (var i = 0; i < aBtn.length; i++) {
            (function (index) {
                aBtn[i].onclick = function () {
                    iNow = Math.floor(iNow / aBtn.length) * aBtn.length + index;
                    tabImg();
                }
            })(i);
        }
        function tabImg() {
            for (var i = 0; i < aBtn.length; i++) {
                aBtn[i].className = '';
            }
            aBtn[iNow % aBtn.length].className = 'active';
            $('#tab p').html(data[iNow % aBtn.length]);
            startMove(oUl, -iNow * aLi[0].offsetWidth);
        }
        next();
        function next(){
            clearInterval(timer2);
            timer2=setInterval(function (){
                iNow++;
                tabImg();
            },2000);
        }
        //startMove();
        var left=0;
        var timer=null;
        function startMove(obj,iTarget){
            clearInterval(timer);
            var count=Math.floor(700/30);
            var start=left;
            var dis=iTarget-start;
            var n=0;
            timer=setInterval(function(){
                n++;
                var a=1-n/count;
                left=start+dis*(1-Math.pow(a,3));
                
                if(left<0){
                    obj.style.left=left%W+'px'; 
                }else{
                    obj.style.left=(left%W-W)%W+'px';       
                }
                if(n==count){
                    clearInterval(timer);
                }
            },30);
        }

    }
    // 在线订气
    $('.operateList ul li').on('click',function (){
        $(this).addClass('active').siblings().removeClass('active');
    });
    //省市区三级联动
    if($('#loc_province').length>0){
        showLocation();
    }
    function showLocation(province,city,town){
        var loc = new Location();
        var title   = ['省' , '市' , '区'];
        $.each(title , function(k , v) {
            title[k]    = '<option value="">'+v+'</option>';
        });

        $('#loc_province').append(title[0]);
        $('#loc_city').append(title[1]);
        $('#loc_town').append(title[2]);
        //初始化省市区 
        $('#loc_province').selectmenu({
            change:function(){
                $('#loc_city').empty();
                $('#loc_city').append(title[1]);
                loc.fillOption('loc_city' , '0,'+$('#loc_province').val());
                $('#loc_town').empty();
                $('#loc_town').append(title[2]);
                //刷新
                $('#loc_city').selectmenu('refresh');
                $('#loc_town').selectmenu('refresh');
            }
        }).selectmenu( "menuWidget" ).addClass( "overflow" );

        $('#loc_city').selectmenu({
            change:function(){
                $('#loc_town').empty();
                $('#loc_town').append(title[2]);
                loc.fillOption('loc_town' , '0,' + $('#loc_province').val() + ',' + $('#loc_city').val());
                //刷新
                $('#loc_town').selectmenu('refresh');
            }
        }).selectmenu( "menuWidget" ).addClass( "overflow" );
        $('#loc_town').selectmenu({
            change:function(){}
        }).selectmenu( "menuWidget" ).addClass( "overflow" );
        //添加默认省份
        if (province) {
            loc.fillOption('loc_province' , '0' , province);
            
            if (city) {
                loc.fillOption('loc_city' , '0,'+province , city);
                
                if (town) {
                    loc.fillOption('loc_town' , '0,'+province+','+city , town);
                }
            }
            
        } else {
            loc.fillOption('loc_province' , '0');
        }
    }
    
    
    //显示关闭弹出层
    function isShow($btn,$pop){
        $btn.on('click',function (){
            $('.layer').show();
            $pop.show();
        });
        $('.closeBtn').on('click',function (){
            $('.layer').hide();
            $pop.hide();
        });
    }
	function controlsSize(){
		var h=$(window).height();
		$('.wrap').css({'minHeight':h});
	}
	function tab($obj){
        $obj.each(function (){
            var aBtn=$(this).find('.tabBtnbox .tabBtn');
            var aLi=$(this).find('.tabItembox .tabItem');
            aBtn.off('click').on('click',function (){
                var _index=$(this).index();
                $(this).addClass('active').siblings().removeClass('active');
                aLi.eq(_index).show().siblings().hide();
            });
        });
    }
    function placeHolder($obj){
        if ($obj.length < 1) return;
        $obj.each(function () {
            var oInput = $(this).find('input');
            var oSpan = $(this).find('span');

            oInput.off('click').on('click', function () {
                oSpan.hide();
            });
            oSpan.off('click').on('click', function () {
                oSpan.hide();
                oInput.focus();
            });
            oInput.off('blur').on('blur', function () {
                if (!oInput.val()) {
                    oSpan.show();
                }
            });

            if (oInput.val() != '') { //处理浏览器记录重叠bug
                oSpan.hide();
            } else {
                oSpan.show();
            }
        });
    };
    function footFixed(){
        isFixed();
        function isFixed(){
            var h=$(window).height();
            var headH=$('.header').height();
            var footH=$('.footer').height();
            var contH=$('.content').height();
            
            var mih = h-footH-headH;
            if(contH<mih && mih<1030){
                $('.footer').css({'position':'fixed'});
            }else{
                $('.footer').css({'position':'static'});
            }
        }
        $(window).on('resize',function (){
            isFixed();
        });
        
    }
    var pathname = document.location.pathname;
    var nowTag = $('.contLeft').find('a[href="'+pathname+'"]');
    if(nowTag.length == 0) {
        nowTag = $('.contLeft').find('a[href="'+pathname+document.location.search+'"]');
        console.log(nowTag)
        if(nowTag.length == 0) {
            nowTag = $('.contLeft').find('a[href="'+document.referrer.replace(document.location.protocol+'//'+document.location.hostname,'')+'"]');
            if(nowTag.length == 0) {
                var pathname = pathname.split('/');
                nowTag = $('.contLeft').find('a[href="/'+pathname[1]+'/index"]');
                if(nowTag.length == 0) {
                    $('.contLeft ul:first').show();
                    $('.contNav>ul>li:first').addClass('active').siblings().removeClass('active');
                }
            }
        }
    }
    nowTag.parents('ul').show().siblings().hide();
    $('.contNav>ul>li').eq(nowTag.parents('ul').attr('index')-1).addClass('active').siblings().removeClass('active');
});
