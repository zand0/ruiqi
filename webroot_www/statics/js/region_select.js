/* 
 * 区域js
 * author:zxg
 */
(function ($, window) {
    var regionClass = function (config) {
        
        this.v1_select = $(config.v1_select);
        this.v2_select = $(config.v2_select);
        this.v3_select = $(config.v3_select);
        this.v4_select = $(config.v4_select);
        
        this.v1_select_value = config.v1_select_value;
        this.v2_select_value = config.v2_select_value;
        this.v3_select_value = config.v3_select_value;
        this.v4_select_value = config.v4_select_value;
        
        var request_param = '';
        if(config.request_param){
            for(key in config.request_param){
                request_param += "&"+key+'='+config.request_param[key]
            }
        }
        this.request_param = request_param;
        var sheng = 0;
        var shi = 0;
        var shi_name = '';
        var xian = 0;
        var xian_name = '';
        var qu = 0;
        var qu_name = '';
        var jd = 0;
        var jd_name = '';
        if(config.r_param){
            sheng = config.r_param['sheng'];
            shi = config.r_param['shi'];
            shi_name = config.r_param['shi_name'];
            xian = config.r_param['xian'];
            xian_name = config.r_param['xian_name'];
            qu = config.r_param['qu'];
            qu_name = config.r_param['qu_name'];
            jd = config.r_param['jd'];
            jd_name = config.r_param['jd_name'];
        }
        
        this.sheng = sheng;
        this.shi = shi;
        this.shi_name = shi_name;
        this.xian = xian;
        this.xian_name = xian_name;
        this.qu = qu;
        this.qu_name = qu_name;
        this.jd = jd;
        this.jd_name = jd_name;
        this.init();
    }
    regionClass.prototype = {
        init: function () {
            this._initOption();
        },
        _initOption: function () {
            var that = this;
            var v1_option_html = that.__fill_option(that.v1_select, 10, 140);
            var v2_option_html = that.__fill_option(that.v2_select, 140, this.xian);
            var v3_option_html = that.__fill_option(that.v3_select, this.xian, this.qu);
            var v4_option_html = that.__fill_option(that.v4_select, this.qu, this.jd);
            that.v2_select.html(v2_option_html);
            that.v3_select.html(v3_option_html);
            that.v4_select.html(v4_option_html);
            that.v1_select.bind('change', function () {
                that.v1_change_handle();
            });
            that.v2_select.bind('change', function () {
                that.v2_change_handle();
            });
            that.v3_select.bind('change', function () {
                that.v3_change_handle();
            });
            that.v4_select.bind('change', function () {
                that.v4_change_handle();
            });
            that._initSelect();
        },
        _initSelect:function(){
            var that = this;
            if(that.v1_select_value>0){ 
                that.set_select(1,that.v1_select_value);
            }
            if(that.v2_select_value>0){
                that.set_select(2,that.v2_select_value);
            }
            if(that.v3_select_value>0){
                that.set_select(3,that.v3_select_value);
            }
            if(that.v4_select_value>0){
                that.set_select(4,that.v4_select_value);
            }
        }, 
        __fill_option: function (select_obj, id, select_index) {
            var that = this;
            var request_param = that.request_param;
            $.ajax({
                type: "POST",
                url: "/region/regionList",
                data: "id=" + id +request_param,
                dataType: "json",
                async: false ,
                success: function (data) {
                    if (data['status'] == 200) {
                        var option_arr = data['data'];
                        var option_html = '<option value="0">请选择</option>';
                        if ($.isArray(option_arr)) {
                            for (var key in option_arr) {
                                if (option_arr[key]['region_id'] == select_index) {
                                    option_html += '<option value="' + option_arr[key]['region_id'] + '" selected=selected>' + option_arr[key]['region_name'] + '</option>';
                                } else {
                                    option_html += '<option value="' + option_arr[key]['region_id'] + '">' + option_arr[key]['region_name'] + '</option>';
                                }
                            }
                        }
                        select_obj.html(option_html);
                        return true;
                    }
                }
            });
        },
        v1_change_handle: function () {
            var that = this;
            var id = that.v1_select.find("option:selected").val();
            if (id == 0) {
                var option_html = '<option value="0">请选择</option>';
                that.v2_select.html(option_html);
            } else {
                that.__fill_option(that.v2_select, id);
            }
            that.v2_change_handle();
        },
        v2_change_handle: function () {
            var that = this;
            var id = that.v2_select.find("option:selected").val();
            if (id == 0) {
                var option_html = '<option value="0">请选择</option>';
                that.v3_select.html(option_html);
            } else {
                that.__fill_option(that.v3_select, id);
            }
            that.v3_change_handle();
        },
        v3_change_handle: function () {
            var that = this;
            var id = that.v3_select.find("option:selected").val();
            if (id == 0) {
                var option_html = '<option value="0">请选择</option>';
                that.v4_select.html(option_html);
            } else {
                that.__fill_option(that.v4_select, id);
            }
            that.v4_change_handle();
        },
        set_select:function(selectInex,selectValue){
            var that=this; window.console.log(selectInex+'x')
            switch(selectInex){
                case 1: 
                    that.v1_select[0].selectedIndex = selectValue;
                    that.v1_change_handle();
                    break;
                case 2:
                    that.v2_select.find("option[value="+selectValue+"]").attr("selected",true);
                    that.v2_change_handle();
                    break;
                case 3:
                    that.v3_select.find("option[value=" + selectValue + "]").attr("selected", true);
                    that.v3_change_handle();
                    break;
            }     
        },
        set_all_select: function (v1SelectedKey, v2SelectedKey, v3SelectedKey) {
            var v1_index = this.v1_select.find("option[value='" + v1SelectedKey + "']").index();
            this.v1_select[0].selectedIndex = v1_index;
            this.v1_change_handle();

            var v2_index = this.v2_select.find("option[value='" + v2SelectedKey + "']").index();
            this.v2_select[0].selectedIndex = v2_index;
            this.v2_change_handle();

            var v3_index = this.v3_select.find("option[value='" + v3SelectedKey + "']").index();
            this.v3_select[0].selectedIndex = v3_index;
            this.v3_change_handle();
            
            return this;
        },
    }
    var region_util = function (v1_select, v2_select, v3_select) {
        return new regionClass(v1_select, v2_select, v3_select);
    }
    window.region_util = region_util;
}($, window))