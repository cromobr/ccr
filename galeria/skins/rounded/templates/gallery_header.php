<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?
if(isset($fID))
{
?>
        <script language="javascript" type="text/javascript" src="lytebox.js"></script>
<?
}
?>
	<meta http-equiv="content-type" content="text/html; charset=<?php $mg2->output('charset');?>" />
	<title><?php $mg2->output('pagetitle');?></title>
	<meta name="title" content="<?php $mg2->output('pagetitle');?>" />
<?php
if (!empty($mg2->robots))
	printf("\t".'<meta name="robots" content="%s" />'."\n", implode(', ', $mg2->robots));
if (!empty($mg2->googlebot))
	printf("\t".'<meta name="googlebot" content="%s" />'."\n", $mg2->googlebot);
?>
	<link href="skins/<?php $mg2->output('activeskin');?>/css/style.css" rel="stylesheet" type="text/css" />
</head>
<BODY  bgColor=transparent
leftMargin=0 topMargin=0 ALLOWTRANSPARENCY="true" marginheight="0"
marginwidth="0">
<table class="table-top" cellspacing="0" cellpadding="0">
<tr>
</tr>
</table>
