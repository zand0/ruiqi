<?php /* Smarty version 3.1.27, created on 2016-12-04 18:56:36
         compiled from "F:\wamp\www\rq\ruiqi\app_www\modules\My\views\layouts\header.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:85375843f664e0ea85_50272433%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'deeddea8ab604d6f57fd2a530ff2833a5c746f44' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\ruiqi\\app_www\\modules\\My\\views\\layouts\\header.phtml',
      1 => 1450064218,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '85375843f664e0ea85_50272433',
  'variables' => 
  array (
    '_PUBLIC' => 0,
  ),
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_5843f664e45596_41924491',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_5843f664e45596_41924491')) {
function content_5843f664e45596_41924491 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '85375843f664e0ea85_50272433';
?>
<div class="header header2">
    <div class="headCont clearfix">
        
        <div class="fl head_left">
            <a href="/index/index" class="fl rqLogo"><img src="<?php echo $_smarty_tpl->tpl_vars['_PUBLIC']->value;?>
/images/logo.png"></a>
            <ul class="fl">
                <li>
                    <a href="/index/index">首页</a>
                </li>
                <li>
                    <a href="/about/index">关于我们</a>
                </li>
                <li class="active">
                    <a href="zaixiandingqi.html">在线订气</a>
                </li>
                <li>
                    <a href="/dynamic/index">企业动态</a>
                </li>
                <li>
                    <a href="/safety/index">安全常识</a>
                </li>
                <li>
                    <a href="/contact/index">联系我们</a>
                </li>
                <li>
                    <a href="/index/login">后台登陆</a>
                </li>
            </ul>
        </div>
        <?php if ($_SESSION['kehu_info'] != '') {?>
        <div class="fr head_right head_right2">
            <p>Hi! 
                <a href="/my/home/index"><?php echo $_SESSION['kehu_info']['user_name'];?>
</a>
                <span>|</span>
                <a href="/my/passport/logout" class="exit">退出</a>
            </p>
        </div>
        <?php } else { ?>
        <div class="fr head_right">
            <div class="inputarea placeholder">
                <input type="text">
                <span>站内搜索</span>
                <a href="javascript:;"></a>
            </div>
        </div>
        <?php }?>
    </div>
</div><?php }
}
?>