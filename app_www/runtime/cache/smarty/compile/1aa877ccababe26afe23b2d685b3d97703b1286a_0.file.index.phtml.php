<?php /* Smarty version 3.1.27, created on 2016-12-06 09:29:10
         compiled from "E:\xampp\htdocs\rq\ruiqi\app_www\views\index\index.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:2454258461466140ed2_71906306%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1aa877ccababe26afe23b2d685b3d97703b1286a' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\views\\index\\index.phtml',
      1 => 1480900568,
      2 => 'file',
    ),
    '23e0e93a93b810a8eb015c72736bad3804d54d68' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\views\\main_new.phtml',
      1 => 1480900568,
      2 => 'file',
    ),
    '65e850fa65d5f54a216ab7fabda242dfb44be92d' => 
    array (
      0 => '65e850fa65d5f54a216ab7fabda242dfb44be92d',
      1 => 0,
      2 => 'string',
    ),
    '25f275ced3ff2bf08c5ad342e5f1ad650f0f993e' => 
    array (
      0 => 'E:\\xampp\\htdocs\\rq\\ruiqi\\app_www\\views\\main_new_dynamic.phtml',
      1 => 1480900568,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2454258461466140ed2_71906306',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_58461466245846_76620250',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_58461466245846_76620250')) {
function content_58461466245846_76620250 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '2454258461466140ed2_71906306';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="renderer" content="webkit">
        <title>瑞气科技</title>
        <link rel="stylesheet" type="text/css" href="/statics/newhome/css/style.css">
        <?php echo '<script'; ?>
 type="text/javascript" src="/statics/js/require.min.js"><?php echo '</script'; ?>
>
    </head>
    <body>
        <div class="wrap">
            <?php echo $_smarty_tpl->getSubTemplate ('main_new_header.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

            <?php
$_smarty_tpl->properties['nocache_hash'] = '2454258461466140ed2_71906306';
?>

<div id="content">
    <!-- 头图 -->
    <div class="banner">
        <div class="detail">
            <h3>智慧燃气平台</h3>
            <p>液化石油气运营综合解决方案</p>
            <ul class="clearfix">
                <li>
                    <span>累计订气数（订单）</span><br>
                    <strong><?php echo $_smarty_tpl->tpl_vars['orderTotal']->value;?>
</strong>
                </li>
                <li>
                    <span>已使用智能钢瓶（个）</span><br>
                    <strong><?php echo $_smarty_tpl->tpl_vars['bottleTotal']->value;?>
</strong>
                </li>
                <li>
                    <span>已开业门店（个）</span><br>
                    <strong><?php echo $_smarty_tpl->tpl_vars['shopTotal']->value;?>
</strong>
                </li>
                <li>
                    <span>已有送气工（人）</span><br>
                    <strong><?php echo $_smarty_tpl->tpl_vars['shipperTotal']->value;?>
</strong>
                </li>
                <li class="last">
                    <span>所有员工数（人）</span><br>
                    <strong><?php echo $_smarty_tpl->tpl_vars['userTotal']->value;?>
</strong>
                </li>
            </ul>
            <a href="/my/home/createorder" class="orangeBtn">立即订气</a>
        </div>
    </div>
    <div class="w1170">
        <!-- 价格动态 -->
        <div class="priceState">
            <div class="title clearfix">
                <h3 class="fl">实时订单</h3>
                <div class="fl position">
                    <span></span>
                    定位&nbsp;河北沧州&nbsp;&nbsp;<a href="javascript:void(0);">[选择区域]</a>
                </div>
                <div class="fr classify">
                    <a href="javascript:void(0);" class="active">订单</a>
                    <a href="javascript:void(0);">5kg</a>
                    <a href="javascript:void(0);">15kg</a>
                    <a href="javascript:void(0);">50kg</a>
                </div>
            </div>
            <div class="detail clearfix">
                <i class="icon" id="icon"></i>
                <div class="fl menu">
                    <a href="javascript:;" class="prevBtn">&lt;</a>
                    <a href="javascript:;" class="nextBtn">&gt;</a>
                    <div class="slide" id="tab">
                        <ul class="clearfix">
                            <?php if ($_smarty_tpl->tpl_vars['shopBottle']->value != '') {?>
                            <?php
$_from = $_smarty_tpl->tpl_vars['shopBottle']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['value'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['value']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
$foreach_value_Sav = $_smarty_tpl->tpl_vars['value'];
?>
                            <li>
                                <h4><?php echo $_smarty_tpl->tpl_vars['shopObject']->value[$_smarty_tpl->tpl_vars['value']->value['shop_id']]['shop_name'];?>
</h4>
                                <div class="price clearfix up">
                                    <span class="fl"><?php echo $_smarty_tpl->tpl_vars['value']->value['fs_name'];?>
</span>
                                    <strong class="fl"><?php echo $_smarty_tpl->tpl_vars['value']->value['fs_num'];?>
</strong>
                                    <i class="fl"></i>
                                </div>
                                <p>气站</p>
                            </li>
                            <?php
$_smarty_tpl->tpl_vars['value'] = $foreach_value_Sav;
}
?>
                            <?php }?>
                        </ul>
                    </div>
                </div>
                <div class="chart fr">
                    <p>订单走势</p>
                    <div id="container" style="width:250px;height:160px;"></div>
                </div>
                <span class="line"></span>
            </div>
        </div>
        <!-- 为什么要用智慧燃气 -->
        <div class="youshi">
            <h3>为什么要用智慧燃气？</h3>
            <ul class="clearfix">
                <li class="clearfix">
                    <div class="pic fl"></div>
                    <div class="fl detail">
                        <h4>多渠道便捷订气</h4>
                        <p>APP订气、微信订气、Web端订气、电话订气，实现客户的随时随地便捷订气，大幅提升用户应用体验。</p>
                    </div>
                </li>
                <li class="clearfix">
                    <div class="pic pic2 fl"></div>
                    <div class="fl detail">
                        <h4>高效智能管理平台</h4>
                        <p>信息化智能管理平台大幅优化液化气灌装站的管理流程和管理效率，精准的数据统计，辅助快速决策，抢占市场先机。</p>
                    </div>
                </li>
                <li class="clearfix">
                    <div class="pic pic3 fl"></div>
                    <div class="fl detail">
                        <h4>物联网+互联网</h4>
                        <p>智能钢瓶的物联网信息交互与互联网应用、管理平台有机结合，更加高效、更加安全</p>
                    </div>
                </li>
                <li class="clearfix">
                    <div class="pic pic4 fl"></div>
                    <div class="fl detail">
                        <h4>优化运营流程</h4>
                        <p>解决灌装站存在的难以解决的核心问题，大幅降低钢瓶流失率、优化运营管理化繁为简、精准实时的账目统计、降低人工成本。</p>
                    </div>
                </li>
                <li class="clearfix">
                    <div class="pic pic5 fl"></div>
                    <div class="fl detail">
                        <h4>先进的管理输出</h4>
                        <p>针对各合作气站提供标准化的管理体系至高效、统一、快速的运营服务。</p>
                    </div>
                </li>
                <li class="clearfix">
                    <div class="pic pic6 fl"></div>
                    <div class="fl detail">
                        <h4>用户的放心选择</h4>
                        <p>100%液化气的灌装生产和到家的专业换装、安全宣讲保障用户安全用气，增加用户的忠诚度。</p>
                    </div>
                </li>
            </ul>
        </div>
        <!-- 加入智慧平台 -->
        <div class="join">
            <h3>加入智慧燃气平台</h3>
            <ul>
                <li class="clearfix">
                    <dl class="fl">
                        <dt>普通用户</dt>
                        <dd>通过手机、网站、微信</dd>
                        <dd>在线订气</dd>
                        <dd>自动匹配最近气站</dd>
                    </dl>	
                    <div class="pic fl"></div>
                    <div class="fl regist">
                        <a href="javascript:;" class="orangeBtn mb24">立即注册</a>
                        <p>手机号直接注册</p>
                    </div>
                </li>
                <li class="clearfix li2">
                    <dl class="fl">
                        <dt>液化气站</dt>
                        <dd>互联网+战略快速升级</dd>
                        <dd>智慧平台工业4.0</dd>
                    </dl>
                    <div class="pic pic2 fl"></div>
                    <div class="fl regist">
                        <a href="javascript:;" class="redBtn mb24">立即申请</a>
                        <p>营业执照</p>
                        <p>燃气经营许可证</p>
                        <p>充装证</p>
                    </div>
                </li>
            </ul>
        </div>
        <!-- 行业动态 安全常识 -->
        <?php /*  Call merged included template "main_new_dynamic.phtml" */
echo $_smarty_tpl->getInlineSubTemplate('main_new_dynamic.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, '306775846146622b2f7_48528443', 'content_5846146622a071_77820462');
/*  End of included template "main_new_dynamic.phtml" */?>

    </div>
</div>

            <?php echo $_smarty_tpl->getSubTemplate ('main_new_footer.phtml', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

        </div>
        <?php echo '<script'; ?>
 type="text/javascript" src="/statics/js/controllers/indexCtrl.js"><?php echo '</script'; ?>
>
    </body>
</html><?php }
}
?><?php
/*%%SmartyHeaderCode:306775846146622b2f7_48528443%%*/
if ($_valid && !is_callable('content_5846146622a071_77820462')) {
function content_5846146622a071_77820462 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '306775846146622b2f7_48528443';
?>
<div class="bottom clearfix">
    <div class="fl leftTxt">
        <a href="#">服务协议</a><br>
        <a href="#">公司公告</a><br>
        <a href="#">服务协议</a><br>
        <a href="#">关于我们</a><br>
        <a href="#">联系我们</a>
    </div>
    <ul class="fl saoma">
        <li>
            <div class="code">
                <img src="/statics/upload/custom.png">
            </div>	
            <p>客户端APP</p>
            <p>扫一扫关注</p>    
        </li>
        <li>
            <div class="code">
                <img src="/statics/upload/shipper.png">
            </div>	
            <p>送气工端APP</p>
            <p>扫一扫下载</p>    
        </li>
        <li>
            <div class="code">
                <img src="/statics/upload/general.png">
            </div>	
            <p>管理端APP</p>
            <p>扫一扫下载</p>    
        </li>
    </ul>
    <dl class="fl">
        <dt>行业动态</dt>
        <dd><a href="#">河北沧州智能燃气平台已开通！</a></dd>
        <dd><a href="#">智慧燃气App 2.0发布！</a></dd>
        <dd><a href="#">交易量突破1000KG！</a></dd>
    </dl>
    <dl class="fr">
        <dt>安全常识</dt>
        <dd><a href="#">液化石油气安全使用常识</a></dd>
        <dd><a href="#">安全使用液化气的方法</a></dd>
        <dd><a href="#">安全使用液化气的消防安全知识</a></dd>
        <dd><a href="#">液化气站安全距离</a></dd>
    </dl>
</div><?php
/*/%%SmartyNocache:306775846146622b2f7_48528443%%*/
}
}
?>