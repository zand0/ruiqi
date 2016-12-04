require(['../statics/znewhome/js/config.js'], function () {
    require(['jquery', 'utils', 'circliful'], function ($, utils, circliful) {
        var funcs = {};
        funcs.taFbn = function () {
            $('.guige .btn li').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
                $('.tabItem .listItem').eq($(this).index()).show().siblings().hide();
            });
        }
        funcs.init = function () {
            // 双日历
            utils.doubleRiliFn($('#start'), $('#end'));
            // 规格选项卡
            funcs.taFbn();
            // 环形
            // 全部
            $('#myStat').circliful({animationStep: 4.0});
            $('#myStat2').circliful({animationStep: 4.0});
            $('#myStat3').circliful({animationStep: 4.0});
            $('#myStat4').circliful({animationStep: 4.0});
            $('#myStat5').circliful({animationStep: 4.0});
            // 15kg
            $('#myStat6').circliful({animationStep: 4.0});
            $('#myStat7').circliful({animationStep: 4.0});
            $('#myStat8').circliful({animationStep: 4.0});
            $('#myStat9').circliful({animationStep: 4.0});
            $('#myStat10').circliful({animationStep: 4.0});
            
            $('#myStat11').circliful({animationStep: 4.0});
            $('#myStat12').circliful({animationStep: 4.0});
            $('#myStat13').circliful({animationStep: 4.0});
            $('#myStat14').circliful({animationStep: 4.0});
            $('#myStat15').circliful({animationStep: 4.0});
            
            $('#myStat16').circliful({animationStep: 4.0});
            $('#myStat17').circliful({animationStep: 4.0});
            $('#myStat18').circliful({animationStep: 4.0});
            $('#myStat19').circliful({animationStep: 4.0});
            $('#myStat20').circliful({animationStep: 4.0});
            
            $('#myStat21').circliful({animationStep: 4.0});
            $('#myStat22').circliful({animationStep: 4.0});
            $('#myStat23').circliful({animationStep: 4.0});
            $('#myStat24').circliful({animationStep: 4.0});
            $('#myStat25').circliful({animationStep: 4.0});
        }
        funcs.init();
    });
});