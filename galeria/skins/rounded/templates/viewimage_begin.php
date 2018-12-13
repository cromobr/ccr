<table class="minithumb" cellspacing="0" cellpadding="0" border="0" align="center">
<tr>
	<td>&nbsp;<?php $mg2->output('nav_first');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_prev');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_this');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_next');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_last');?>&nbsp;</td>
</tr>
</table>
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
			<img src="%s" border="0" width="%d" height="%d" alt="%s" usemap="#imgmap" /></div>',
		$mg2->background,				// image as background
		$mg2->imagefile,				// image file
		$mg2->width,					// image width
		$mg2->height,					// image height
		$mg2->alt						// image alt
	);
?> 
	<div class="dir_right" style="height:<?php $mg2->output('height');?>px"></div>
	<div class="dir_bottomleft"></div>
	<div class="dir_bottom" style="width:<?php $mg2->output('width');?>px;"></div>
	<div class="dir_bottomright"></div>
</div>
<map id="imgmap" name="imgmap"><?php echo implode("\n",$areas);?></map>
<div class="description" style="width:<?php echo ($mg2->width+52);?>px">
	<?php $mg2->output('description');?>
</div>
