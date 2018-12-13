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
<body class="mg2body" background="http://www.nae.com.br/pasistemas/images/fundos/branco.jpg">
<table class="table-top" cellspacing="0" cellpadding="0">
<tr>
	<td class="iconbar"><a href="<?php $mg2->output('link');?>"><?php echo $mg2->lang['stopslideshow'];?></a></td>
	<td class="headline"><?php $mg2->output('title');?></td>
</tr>
</table>
<div class="notice"><?php echo $image_total;?></div>
<?php
// CREATE FULLSIZE LINK
if ($mg2->fullsizelink != '') {
	$mg2->fullsizelink = sprintf('
									<a href="%s" target="_blank">
										<img src="skins/%s/images/%s" border="0" width="%d" height="%d" alt="" /></a>',
									$image_file,
									$mg2->activeskin,
									'dir_topright_resized.gif',
									26,
									26
								);
}
?>
<div style="margin: 0 auto 0 auto;width:<?php echo ($mg2->width+52);?>px">
	<div class="dir_topleft"></div>
	<div class="dir_top" style="width:<?php $mg2->output('width');?>px;"></div>
	<div class="dir_topright"><?php $mg2->output('fullsizelink');?></div>
	<div class="dir_left" style="height:<?php $mg2->output('height');?>px"></div>
<?php
	// DISPLAY IMAGE
	printf('
		<div class="viewimage" style="%s">
			<a href="%s" title="%s"><img src="%s" border="0" width="%d" height="%d" alt="%s" /></a></div>',
		$mg2->background,		// image as background
		$mg2->nexturl,			// url of the next image
		$mg2->tooltip,			// tooltip 'next'
		$mg2->imagefile,		// image file
		$mg2->width,			// image width
		$mg2->height,			// image height
		$mg2->alt				// image alt
	);
?> 
	<div class="dir_right" style="height:<?php $mg2->output('height');?>px"></div>
	<div class="dir_bottomleft"></div>
	<div class="dir_bottom" style="width:<?php $mg2->output('width');?>px;"></div>
	<div class="dir_bottomright"></div>
</div>
<div class="description" style="width:<?php echo ($mg2->width+52);?>px">
	<?php $mg2->output('description');?>
</div>
<div class="copyright"><?php $mg2->output('copyright');?></div>
<img style="display:none" src="<?php $mg2->output('nextimage');?>" alt="" />
<br />
<br />
</body>
</html>
