<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
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
<body class="mg2body">
<div class="status-top"><?php $mg2->output('status');?></div>
<table class="table-top" cellspacing="0" cellpadding="0">
<tr>
	<td class="iconbar"><?php $mg2->gallerynavigation(' | ');?></td>
</tr>
</table>
<table class="table-headline" cellspacing="0" cellpadding="0">
<tr>
	<td class="iconbar"><?php $mg2->displaySlideshowIcon();?></td>
	<td class="headline"><?php $mg2->output('headline');?></td>
</tr>
</table>
