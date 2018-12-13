<table class="minithumb" cellspacing="0" cellpadding="0" border="0" align="center">
<tr>
	<td>&nbsp;<?php $mg2->output('nav_first');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_prev');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_this');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_next');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_last');?>&nbsp;</td>
</tr>
</table>
<div class="fullsizelink"><?php echo $mg2->fullsizelink;?></div>
<?php
	// DISPLAY IMAGE
	printf('
		<div class="viewimage" style="width:%dpx;%s">
			<img src="%s" border="0" width="%d" height="%d" alt="%s" usemap="#imgmap" /></div>',
		$mg2->width,					// image width
		$mg2->background,				// image as background
		$mg2->imagefile,				// image file
		$mg2->width,					// image width
		$mg2->height,					// image height
		$mg2->alt						// image alt
	);
?> 
<map id="imgmap" name="imgmap"><?php echo implode("\n",$areas);?></map>
<div class="description" style="width:<?php echo ($mg2->width+2);?>px">
	<?php $mg2->output('description');?>
</div>
<div class="copyright"><?php $mg2->output('copyright');?></div>
