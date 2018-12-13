<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=<?php echo $charset;?>" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<title>Sistema - Galeria</title>
<meta name="title" content="<?php echo $pagetitle;?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="googlebot" content="noarchive,nofollow" />
<link href="<?php echo ADMIN_FOLDER;?>css/admin.css" rel="stylesheet" type="text/css" />
</head>
<body>
<table class="table_menu" cellpadding="0" cellspacing="0">
  <tr align="center"> 
    <td width="706"><img src="admin/images/logo.gif" alt="" width="600" height="100" border="0" align="center" title="" /> 
    </td>
    <td width="1" align="center"><p>&nbsp;</p>
      </td>
  </tr>
  <tr valign="middle"> 
    <td height="25" colspan="2" align="right" valign="bottom"><div align="center"><hr noshade color="#808080" size="1">
<?php if ($select === 1) { ?>
	<tr>
		<td class="td_div">
			<script language="JavaScript" type="text/javascript">
			<!--
				window.onload = function() {document.login.password.focus();}
			-->
			</script>
			<div style="margin-bottom:2em">
			<form name="login" action="<?php echo ADMIN_INDEX;?>" method="post">
				<p><b><?php echo $pwdheadline;?></b></p>
				<p><?php echo $this->lang['thissection'];?></p>
				<p><input type="password" name="password" class="admintext" /></p>
				<p><input type="image" src="<?php echo ADMIN_IMAGES;?>entrar.gif" class="adminpicbutton" alt="<?php echo $this->lang['ok'];?>" title="<?php echo $this->lang['ok'];?>" /></p>
				<a href="<?php echo $this->getGalleryLink();?>"><?php $this->lang['viewgallery'];?></a>
			</form>
			</div>
		</td>
	</tr>
<?php
}
elseif ($select === 2) {
?>
	<tr>
		<td class="td_div"><h1><?php echo $this->lang['securitylogoff'];?></h1>
			<p><?php printf($this->lang['autologoff'], $this->inactivetime);?></p>
			<a href="<?php echo $this->getGalleryLink();?>"><?php echo $this->lang['viewgallery'];?></a> | <a href="<?php echo ADMIN_INDEX;?>"><?php echo $this->lang['loginagain'];?></a>
			<div style="margin-bottom:1em">&nbsp;</div>
		</td>
	</tr>
<?php
}
elseif ($select === 3) {
?>
	<tr>
		<td class="td_div"><h1><?php echo $this->lang['logoff'];?></h1>
			<p><?php echo $this->lang['forsecurity'];?></p>
			<a href="<?php echo $this->getGalleryLink();?>"><?php echo $this->lang['viewgallery'];?></a> | <a href="<?php echo ADMIN_INDEX;?>"><?php echo $this->lang['loginagain'];?></a>
			<div style="margin-bottom:1em">&nbsp;</div>
		</td>
	</tr>
<?php
	include(ADMIN_FOLDER .'admin_donate_hint.php');
}
?>