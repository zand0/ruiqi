/**
 * 封闭日历
 * 
 */
define(['vendors/jqueryUI/datepicker.min','vendors/jqueryUI/language/datepicker-zh-CN'],function(){
    var ret = {};

    //日历本地化设置
    $.datepicker.setDefaults( $.datepicker.regional[ "zh-CN" ] );
    $.datepicker.setDefaults({
        // showOn: "both",
        // buttonImageOnly: true,
        // buttonImage: "/application/views/img/calendar.png",
        // buttonText: "请选择日期",
        // closeText:'确定',
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        yearRange:"-110y:ymd",
        showButtonPanel: true

    });

    /*
     * 日历组件
     * 支持单日历和级联日历
     * selector String (必填) 初始化的元素，可以为标签/class/id
     * */
    ret.datepickerFn= function(){
        var argLen=arguments.length;
        if(argLen<1) return false;
        //匹配只有一个参数，如 ('#id')
        if( argLen == 1 && typeof arguments[0] == 'string' ){
            $(arguments[0]).datepicker();

            //匹配2个参数，如 ('#id',{})
        }else if(argLen == 2 && typeof arguments[0] == 'string' && typeof arguments[1] == 'object'){
            var options = arguments[1]||{};
            $(arguments[0]).datepicker( options );
            //匹配2个参数，如 ('#from','#to')
        }else if(argLen == 2 && typeof arguments[0] == 'string' && typeof arguments[1] == 'string'){
            var start = arguments[0];
            var end = arguments[1];
            $( start ).datepicker({
                changeMonth: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( end ).datepicker( "option", "minDate", selectedDate );
                }
            });
            $( end ).datepicker({
                changeMonth: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( start ).datepicker( "option", "maxDate", selectedDate );
                }
            });
        }

    };

    return ret;
});