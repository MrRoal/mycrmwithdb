<?php /* Smarty version Smarty-3.1.7, created on 2018-06-28 12:14:04
         compiled from "/home/ilogix/Workspace/freshvtiger/rgcrm/includes/runtime/../../layouts/vlayout/modules/Mycrm/SideBar.tpl" */ ?>
<?php /*%%SmartyHeaderCode:12756645385b34d10c2a0e20-08774073%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ea9b25dca3dc02cb3356cf8b81651a83c3e4e3b2' => 
    array (
      0 => '/home/ilogix/Workspace/freshvtiger/rgcrm/includes/runtime/../../layouts/vlayout/modules/Mycrm/SideBar.tpl',
      1 => 1530184368,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '12756645385b34d10c2a0e20-08774073',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5b34d10c2ae57',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5b34d10c2ae57')) {function content_5b34d10c2ae57($_smarty_tpl) {?>
<div class="sideBarContents"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('SideBarLinks.tpl',$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<div class="clearfix"></div><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('SideBarWidgets.tpl',$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div><?php }} ?>