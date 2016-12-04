require(['../statics/znewhome/js/config.js'], function() {
    require(['jquery', 'utils'], function($, utils, PageNav) {
        var funcs = {};

        //公司订单分类统计
        funcs.orderData  = function(){
            $(".searchtype").click(function(){
                var typeid = $(this).attr('typeid');
                if (typeid >= 1){
                    var list = '<div class="mb30"><img class="" src="/statics/images/loading.gif" style="width: 32px;height:32px; margin:0 auto;"></div>';
                    $(".listItem").html(list);
                    var testlist = '';
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "/sales/shoporder",
                        data: {'view_type': 'json', 'search_type': typeid},
                        success: function(result) {
                            var preview = eval("(" + result + ")");
                            //第一层数据
                            var orderTotalArr = !(typeof(preview.order_total) == "undefined") ? preview.order_total : '';
                            var orderTypeArr = !(typeof(preview.order_type) == "undefined") ? preview.order_type : '';
                            var orderZyTotal = !(typeof(orderTotalArr[1]) == "undefined") ? orderTotalArr[1] : 0;
                            var orderJmTotal = !(typeof(orderTotalArr[2]) == "undefined") ? orderTotalArr[2] : 0;
                            var orderWcTotal = !(typeof(orderTypeArr[4]) == "undefined") ? orderTypeArr[4]['total'] : 0;
                            var orderTotal = !(typeof(preview.total) == "undefined") ? preview.total : 0;
                            testlist += '<div class="border_count mb30">' +
                                            '<ul class="clearfix">' +
                                                '<li class="on mr20"><strong>订单量合计：' + orderTotal + '</strong><span></span><i></i></li>' +
                                                '<li class="mr20"><strong>自营订单量：' + orderZyTotal + '</strong><span></span><i></i></li>' +
                                                '<li><strong>加盟订单量：' + orderJmTotal + '</strong><span></span><i></i></li>' +
                                            '</ul>' +
                                        '</div>';
                                
                            //第二层数据
                            var orderWcList = !(typeof(orderTypeArr[4]) == "undefined") ? orderTypeArr[4]['list'] : '';
                            var orderGyWcTotal = !(typeof(orderWcList[3]) == "undefined") ? orderWcList[3] : 0;
                            var orderSyWcTotal = !(typeof(orderWcList[2]) == "undefined") ? orderWcList[2] : 0;
                            var orderJmWcTotal = !(typeof(orderWcList[1]) == "undefined") ? orderWcList[1] : 0;
                            var orderTypeArr = !(typeof(preview.order_type) == "undefined") ? preview.order_type : '';
                            var typeList = '';
                            if (orderTypeArr != ''){
                                for (var key in orderTypeArr) {
                                    if (key != 4){
                                        var orderTypeList = orderTypeArr[key]['list'];
                                        var orderGyNum = !(typeof (orderTypeList[3]) == "undefined") ? orderTypeList[3] : 0;
                                        var orderSyNum = !(typeof (orderTypeList[2]) == "undefined") ? orderTypeList[2] : 0;
                                        var orderJmNum = !(typeof (orderTypeList[1]) == "undefined") ? orderTypeList[1] : 0;
                                        typeList += '<li>'+
                                                        '<p>' + preview.orderStatus[key] + '订单</p>' +
                                                        '<strong>' + orderTypeArr[key]['total'] + '</strong>' +
                                                        '<div class="mt6">工业：' + orderGyNum + '</div>' +
                                                        '<div>商业：' + orderSyNum + '</div>' +
                                                        '<div>居民：' + orderJmNum + '</div>' +
                                                    '</li>';
                                    }
                                }
                            }
                            testlist += '<div class="dataMenu dataMenu4 mb30  pl186">' +
                                            '<div class="bgBtn">' +
                                                '<p>已完成订单</p>' +
                                                '<strong>' + orderWcTotal + '</strong>' +
                                                '<div class="mt6">工业：' + orderGyWcTotal + '</div>' +
                                                '<div>商业：' + orderSyWcTotal + '</div>' +
                                                '<div>居民：' + orderJmWcTotal + '</div>' +
                                            '</div>' +
                                            '<ul class="clearfix">' + typeList + '</ul>' +
                                        '</div>';
                                
                            //第三层数据  
                            var tablelist = '';
                            var orderShopArr = !(typeof (preview.order_shop) == "undefined") ? preview.order_shop : '';
                            var tableLength = 0;
                            if (orderShopArr != ''){
                                for (var okey in orderShopArr){
                                    tableLength++;
                                }
                            }
                            if (tableLength > 0) {
                                for (var skey in orderShopArr){
                                    var ii = 1;
                                    var sList = 0;
                                    var oshopArr = orderShopArr[skey]['list'];
                                    for (var mk in oshopArr) {
                                        sList++;
                                    }
                                    sList += 1;
                                    var wc_num = 0;
                                    var ps_num = 0;
                                    var pf_num = 0;
                                    var wt_num = 0;
                                    var gb_num = 0;
                                    
                                    for (var mkey in oshopArr){
                                        if (orderShopArr[skey]['shop_type'] == 1){
                                            var tlist = '<div class="style">自营</div>';
                                        } else{
                                            var tlist = '<div class="style style_2">加盟</div>';
                                        }
                                        var ilist = '';
                                        var ilists = '';
                                        if (ii == 1){
                                            ilist = '<td class="borderRight font_15 textLeft" rowspan="' + sList + '"><strong>' + orderShopArr[skey]['shop_name'] + '</strong>' + tlist + '</td>';
                                            ilists = '<td rowspan="' + sList + '" class="redColor font_15"><strong>' + orderShopArr[skey]['total'] + '</strong></td>';
                                        }
                                        
                                        var qwc_num = !(typeof(oshopArr[mkey][4]) == "undefined") ? oshopArr[mkey][4] : 0;
                                        var qps_num = !(typeof(oshopArr[mkey][2]) == "undefined") ? oshopArr[mkey][2] : 0;
                                        var qpf_num = !(typeof(oshopArr[mkey][1]) == "undefined") ? oshopArr[mkey][1] : 0;
                                        var qwt_num = !(typeof(oshopArr[mkey][5]) == "undefined") ? oshopArr[mkey][5] : 0;
                                        var qgb_num = !(typeof(oshopArr[mkey][-1]) == "undefined") ? oshopArr[mkey][-1] : 0;
                                        
                                        tablelist += '<tr class="border_none">' + ilist +
                                                        '<td><div class="use_style use_style' + mkey + '">' + preview.kehuType[mkey] + '</div></td>' +
                                                        '<td>' + qwc_num + '</td>' +
                                                        '<td>' + qps_num + '</td>' +
                                                        '<td>' + qpf_num + '</td>' +
                                                        '<td>' + qwt_num + '</td>' +
                                                        '<td class="borderRight">' + qgb_num + '</td>' + ilists + '</tr>';
                                                
                                        ii++;
                                        wc_num += parseInt(qwc_num);
                                        ps_num += parseInt(qps_num);
                                        pf_num += parseInt(qpf_num);
                                        wt_num += parseInt(qwt_num);
                                        gb_num += parseInt(qgb_num);
                                    }
                                    tablelist += '<tr>'+
                                                    '<td class="small"><div class="use_style">合计</div></td>' +
                                                    '<td class="small">' + wc_num + '</td>' +
                                                    '<td class="small">' + ps_num + '</td>' +
                                                    '<td class="small">' + pf_num + '</td>' +
                                                    '<td class="small">' + wt_num + '</td>' +
                                                    '<td class="borderRight">' + gb_num + '</td>' +
                                                '</tr>';
                                }
                            } else{
                                tablelist += '<tr><td colspan="8">暂时没有数据</td></tr>';
                            }
                            
                            testlist += '<div class="result mb30">'+
                                            '<div class="title mb10">'+
                                                '<p>订单明细：</p>'+
                                            '</div>'+
                                            '<div class="table2 mb34">'+
                                                '<table>'+
                                                    '<thead>'+
                                                        '<tr>'+
                                                            '<th class="borderRight textLeft">气库名称|类型</th>'+
                                                            '<th></th>'+
                                                            '<th>已完成</th>'+
                                                            '<th>配送中</th>'+
                                                            '<th>未派发</th>'+
                                                            '<th>问题订单</th>'+
                                                            '<th class="borderRight">已关闭</th>'+
                                                            '<th>订单总量</th>'+
                                                        '</tr>'+
                                                    '</thead>'+
                                                    '<tbody>'+tablelist+'</tbody>'+
                                                '</table>'+
                                            '</div>'+
                                        '</div>';
                                
                            $(".listItem").html(testlist);
                        }
                    });
                }
            });
        }

        funcs.init = function() {
            // 双日历
            utils.doubleRiliFn($('#start'), $('#end'));
            // 选项卡 今日  本周
            utils.tabFn();
            
            this.orderData();
        }

        funcs.init();
    });
});
