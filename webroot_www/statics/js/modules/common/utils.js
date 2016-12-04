/**
 * 通用函数库.
 */
define(['jquery','modules/common/datepicker','vendors/jqueryUI/selectmenu','artDialog','modules/common/showLocation'],function($,datepicker,selectmenu,dialog,location){
    var rets = {};
    rets.headTab=function (){
        $('.contNav ul li').on('click',function (){
            var _index=$(this).index();
            $(this).addClass('active').siblings().removeClass('active');
            $('.contLeft ul').eq(_index).show().siblings().hide();
        });
        
        // 左边选项卡
        $('.contLeft ul').each(function (){
            $(this).children('li').on('click',function (){
                $(this).addClass('active').siblings().removeClass('active');
            });
        });
    }
    rets.showLog=function (params){
        params = params || {};
         //成功提示
        var _dg = dialog({
            title:params.title || '',
            content:params.html || '',
            width:params.width || '300px',
            height:params.height || '300px',
            skin: 'btns_center',
            zIndex:99,
            fixed:true
        }).showModal();
        return _dg;
    }
    rets.datepickerFn=function (){
        if($('.datepicker').length>0){
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                showOn: 'button',
                buttonImage: '../statics/images/calendar.png',
                buttonImageOnly: true,
                buttonText: 'Select date'
            });
        }
    }
    rets.radioFn=function (){
        if($('.radiobox a').length>0){
            $('.radiobox a').on('click',function (){
                $(this).addClass('active').siblings().removeClass('active');
                $(this).parents('.myradio').find('input').val($(this).data('value'));
            });
        }
    }
    rets.placeHolder=function ($obj){
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
    rets.checkFn=function (){
        $('.checkList a').on('click',function (){
            if($(this).hasClass('active')){
                $(this).removeClass('active');
            }else{
                $(this).addClass('active');
            }
        });
        $('.mycheckbox').each(function (){
            $(this).find('.checkList a').on('click',function (){
                var arr=[];
                $(this).parent('.checkList').children('a').each(function (){
                    if($(this).hasClass('active')){
                        arr.push($(this).data('value'));
                    }
                });
                $(this).parents('.mycheckbox').find('input').val(arr.join(','));
            });
        });
    }
   rets.doubleRiliFn=function (start,end){
        if(start.length>0){
            start.datepicker({
                defaultDate: "+w",
                changeMonth: true,
                showOn: "both",
                buttonImage: "../statics/images/calendar.png",
                buttonImageOnly: true,
                onClose: function( selectedDate ) {
                    end.datepicker( "option", "minDate", selectedDate );
                }
            });
            end.datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                showOn: "both",
                buttonImageOnly: true,
                buttonImage: "../statics/images/calendar.png",
                onClose: function( selectedDate ) {
                    start.datepicker( "option", "maxDate", selectedDate );
                }
            });
        }
    }
    rets.tab=function ($obj){
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
    rets.footFixed=function (){
        isFixed();
        function isFixed(){
            var h=$(window).height();
            var headH=$('.header').height();
            var footH=$('.footer').height();
            var contH=$('.content').innerHeight();
            if(contH<h-footH-headH){
                $('.footer').css({'position':'fixed'});
            }else{
                $('.footer').css({'position':'static'});
            }
        }
        $(window).on('resize',function (){
            isFixed();
        });
    }
    rets.controlSize=function (){
        controlsHeight();
        function controlsHeight(){
            var h=$(window).height();
            $('.wrap').css({'minHeight':h});
            $(window).on('resize',function (){
                var h=$(window).height();
                $('.wrap').css({'minHeight':h});
            });
        }
        $(window).on('resize',function (){
            controlsHeight();
        });
    }
    rets.selectmenuFn=function (){
         if($( ".selectmenu" ).length>0){
            $( ".selectmenu" ).selectmenu();
        }
    }
    rets.init=function (){
        // placeholder
        this.placeHolder($('.placeholder'));
        //初始化下拉框
        this.selectmenuFn();
        // radio单选框
        this.radioFn();
        // 复选框
        this.checkFn();
        //单日历
        this.datepickerFn();
        //初始化级联日历
        this.doubleRiliFn($('#time_start'),$('#time_end'));
        // 公用选项卡
        this.tab($('.rq_tab'));
        //底部是否固定
        this.footFixed();
        // 控制页面高度
        this.controlSize();
        // 头部导航
        this.headTab();
        
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
        
        var url = document.location.href;  //得到链接地址 
        //字符串去空
        String.prototype.trim = function() {
            return this.replace(/(^\s*)|(\s*$)/g, "");
        }
        if (url) {
            $('.show_url').each(function() {
                var myIndex = $(this).attr('href').lastIndexOf("/");
                var href = $(this).attr('href');
                var test = new RegExp(href); //创建正则表达式对象
                var result = url.match(test);
                if (result) {
                    $(this).parents("li.show_active ").addClass('active').siblings().removeClass("active");
                }
            });
        }
    }
    // 调用
    rets.init();

    return rets;
    
});
