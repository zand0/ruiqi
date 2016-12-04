require(['/statics/js/config.js'], function () {
    require(['jquery', 'utils', 'modules/common/pages', 'vendors/jqueryUI/selectmenu'], function ($, utils, PageNav, selectmenu) {
        var funcs = {};

        funcs.pageFn = function () {
            var url = window.location.protocol;  //得到链接地址

            var curPage = parseInt($('.currentPage').val() || 1);
            var totalPage = parseInt($('.totalPage').val() || 1);
            var getparamlist = $(".paramlist").val();
            var pager = new PageNav({
                currentPage: curPage,
                totalPage: totalPage,
                wrapId: '#pageNav',
                callback: function (curPage) {
                    url += '?page=' + curPage;
                    if (getparamlist != '' && getparamlist != undefined) {
                        url += '&' + getparamlist;
                    }
                    location.href = url;
                }
            });
        }

        // 初始化
        funcs.init = function () {
            $("#chk_all").live('click', function () {
                var c = $(this).attr("checked");
                $("input[name='rules[]']").attr("checked", c == 'checked' ? true : false);
            });
            $(".dd-item").live('click', function () {
                var pid = $(this).attr('pid');
                var c = $(this).find('input').attr('checked');
                $('#rules').find('li[pid=' + $(this).attr('rid') + ']>input').attr('checked', c == 'checked' ? true : false);
                return true;
            });

            $("#typelist").live('selectmenuchange', function () {
                var dataVal = $(this).val();
                $.get('/supplier/ajaxdata', {dataVal: dataVal}, function (data) {
                    var option_html = '<option value="">请选择</option>';
                    if (data) {
                        if (dataVal == 1) {
                            for (var key in data) {
                                option_html += '<option value="' + data[key].gid + '">' + data[key].gas_name + '</option>';
                            }
                        } else if (dataVal == 2) {
                            for (var key in data) {
                                option_html += '<option value="' + data[key].id + '">' + data[key].bottle_name + '</option>';
                            }
                        } else {
                            for (var key in data) {
                                option_html += '<option value="' + data[key].id + '">' + data[key].products_name + '</option>';
                            }
                        }
                    }
                    $('#goods_type').empty().append(option_html).selectmenu('refresh');
                    return true;
                }, "json");
            });
            
            $("#shop_level").live('selectmenuchange',function () {
                $.get('/shop/getshopbylevel',{level:$(this).val()},function(data){
                  var option_html = '<option value="0">请选择</option>';
                  if (data) {
                      for (var key in data) {
                          option_html += '<option value="' + data[key].shop_id + '">' + data[key].shop_name + '</option>';
                      }
                  }
                  $('#shop_id').empty().append(option_html).selectmenu('refresh');
                  return true;
                },"json"); 
            });

            $("#object_id").live('selectmenuchange', function () {
                var dataVal = $(this).val();
                $.get('/news/ajaxdata', {dataVal: dataVal}, function (data) {
                    var option_html = '<option value="">请选择</option>';
                    if (data) {
                        if (dataVal == 1) {
                            option_html += data;
                        } else if (dataVal == 2) {
                            for (var key in data) {
                                option_html += '<option value="' + data[key].shop_id + '">' + data[key].shop_name + '</option>';
                            }
                        } else {
                            for (var key in data) {
                                option_html += '<option value="' + data[key].id + '">' + data[key].products_name + '</option>';
                            }
                        }
                    }
                    $('#deparment_id').empty().append(option_html).selectmenu('refresh');
                    return true;
                }, "json");
            });

            $("#deparment_id").live('selectmenuchange', function () {
                var pDataVal = $("#object_id").val();
                var dataVal = $(this).val();
                $.get('/news/ajaxdata', {dataVal: pDataVal, dVal: dataVal}, function (data) {
                    var option_html = '<option value="">请选择</option>';
                    if (pDataVal == 1) {
                        for (var key in data) {
                            option_html += '<option value="' + data[key].id + '">' + data[key].title + '</option>';
                        }
                    } else {
                        for (var key in data) {
                            option_html += '<option value="' + data[key].user_id + '">' + data[key].username + '</option>';
                        }
                    }
                    $('#user_id').empty().append(option_html).selectmenu('refresh');
                    return true;
                }, "json");
            });

            $("#yhtype").live('selectmenuchange', function () {
                var dataVal = $(this).val();
                if(dataVal == 2){
                    $("#zkproduct").show();
                }else{
                    $("#zkproduct").hide();
                }
            });
            
            $(".del_btn").click(function () {
                var order_id = $(this).attr('order_id');
                var that = $(this);
                if (confirm('确认删除？')) {
                    $.post('/orderwx/delorder', {order_id: order_id}, function (data) {
                        if(data == 1){
                            that.parents('tr').remove();
                        }
                        return true;
                    }, "json");
                }
            });
            
            // 分页
            this.pageFn();
        }
        funcs.init();
    });
});