<?php /* Smarty version 3.1.27, created on 2016-12-03 11:32:32
         compiled from "F:\wamp\www\rq\app_www\views\main_new_header.phtml" */ ?>
<?php
/*%%SmartyHeaderCode:1197158423cd0b40074_49479291%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b64ae887aa52573d11dd61c20d58cc87901d228d' => 
    array (
      0 => 'F:\\wamp\\www\\rq\\app_www\\views\\main_new_header.phtml',
      1 => 1473386210,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1197158423cd0b40074_49479291',
  'has_nocache_code' => false,
  'version' => '3.1.27',
  'unifunc' => 'content_58423cd0b67177_89714460',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_58423cd0b67177_89714460')) {
function content_58423cd0b67177_89714460 ($_smarty_tpl) {

$_smarty_tpl->properties['nocache_hash'] = '1197158423cd0b40074_49479291';
?>
<div id="header" class="clearfix">
    <h1 class="fl">
        <a href="/index/index">
            <img src="/statics/images/logo.png">
        </a>
    </h1>
    <div class="nav fl">
        <a href="/index/index" class="active">首页<span></span></a>
        <a href="/my/home/createorder">我要订气<span></span></a>
        <a href="/index/deploy">气站门店分布<span></span></a>
        <a href="/ruiqi/login.html">呼叫中心<span></span></a>
        <a href="/index/login">后台管理<span></span></a>
        <a href="#">政府监控<span></span></a>
    </div>
    <div class="fr login"> 
        <?php if ($_SESSION['kehu_info'] != '') {?>
        您好，
        <a href="/my/home/index"><?php echo $_SESSION['kehu_info']['user_name'];?>
</a>
        <span>|</span>
        <a href="/my/passport/logout">退出</a>
        <?php } else { ?>
        <a href="/index/userlogin">登陆</a>
        <?php }?>
    </div>
</div><?php }
}
?>