/**
 * 省市区
 * 
 */
define(['vendors/location/location'],function(){
    var ret = {};
    ret.init=function (json){
        var json=json||{};
        // province = '河南省';
        // city = '信阳市';
        // town = '潢川县';
        json.sheng = json.sheng || 'loc_province';
        json.shi = json.shi || 'loc_city';
        json.qu = json.qu || 'loc_town';

        var sheng=$('#'+json.sheng);
        var shi=$('#'+json.shi);
        var qu=$('#'+json.qu);
        var loc = new Location();
        var title   = ['省' , '市' , '区'];
        $.each(title , function(k , v) {
            title[k]    = '<option value="">'+v+'</option>';
        });

        sheng.append(title[0]);
        shi.append(title[1]);
        qu.append(title[2]);
        //添加默认省份
        if (json.province) {
            //设置默认值
            loc.setDefaults(json.sheng,json.shi,json.qu,json.province,json.city,json.town);
        } else {
            loc.fillOption(json.sheng , '0');
        }

        //初始化省市区 
        sheng.selectmenu({
            change:function(){
                shi.empty();
                shi.append(title[1]);
                loc.fillOption(json.shi , '0,'+sheng.val());
                qu.empty();
                qu.append(title[2]);
                //刷新
                shi.selectmenu('refresh');
                qu.selectmenu('refresh');
            }
        }).selectmenu( "menuWidget" ).addClass( "overflow" );

        shi.selectmenu({
            change:function(){
                qu.empty();
                qu.append(title[2]);
                loc.fillOption(json.qu , '0,' + sheng.val() + ',' + shi.val());
                //刷新
                qu.selectmenu('refresh');
            }
        }).selectmenu( "menuWidget" ).addClass( "overflow" );
        qu.selectmenu({
            change:function(){}
        }).selectmenu( "menuWidget" ).addClass( "overflow" );
    }
    return ret;
});