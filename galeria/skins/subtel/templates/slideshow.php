<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="refresh" content="<?php $mg2->output('slideshowdelay');?>; url=<?php $mg2->output('nexturl');?>" />
	<meta http-equiv="content-type" content="text/html; charset=<?php $mg2->output('charset');?>" />
	<meta http-equiv="expires" content="0" />
	<title><?php $mg2->output('pagetitle');?></title>
	<meta name="title" content="<?php $mg2->output('pagetitle');?>" />
<?php
if (!empty($mg2->robots))
	printf("\t".'<meta name="robots" content="%s" />'."\n", implode(',',$mg2->robots));
if (!empty($mg2->googlebot))
	printf("\t".'<meta name="googlebot" content="%s" />'."\n", $mg2->googlebot);
?>
	<link href="skins/<?php $mg2->output('activeskin');?>/css/style.css" rel="stylesheet" type="text/css" />
</head>
<body class="mg2body">
<table class="table-top" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td class="notice"><?php echo $image_total;?></td>
</tr>
</table>
<table class="table-headline" cellspacing="0" cellpadding="0">
<tr>
	<td class="iconbar"><a href="<?php $mg2->output('link');?>"><?php echo $mg2->lang['stopslideshow'];?></a>
	</td>
	<td class="headline"><?php $mg2->output('title');?></td></tr>
</table>
<?php
	// DISPLAY IMAGE
	printf('
		<div class="viewimage" style="width:%dpx;margin-top:1.4em;%s">
			<a href="%s" title="%s"><img src="%s" border="0" width="%d" height="%d" alt="%s" /></a></div>',
		$mg2->width,			// image width
		$mg2->background,		// image as background
		$mg2->nexturl,			// url of the next image
		$mg2->tooltip,			// tooltip 'next'
		$mg2->imagefile,		// image file
		$mg2->width,			// image width
		$mg2->height,			// image height
		$mg2->alt				// image alt
	);
?> 
<div class="description" style="width:<?php echo ($mg2->width+2);?>px">
	<?php $mg2->output('description');?>
</div>
<div class="copyright"><?php $mg2->output('copyright');?></div>
<img style="display:none" src="<?php $mg2->output('nextimage');?>" alt="" />
<br />
<br />
</body>
</html>