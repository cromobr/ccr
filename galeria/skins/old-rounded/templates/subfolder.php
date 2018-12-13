<td valign="top" width="<?php echo $mg2->thumbMaxWidth+52+20;?>" align="center">
	<div class="subfolder" style="width:<?php echo ($mg2->width+52);?>px">
		<div class="dir_topleft"></div>
		<div class="dir_top" style="width:<?php $mg2->output('width');?>px;"></div>
		<div class="dir_topright"></div>
		<div class="dir_left" style="height:<?php $mg2->output('height');?>px"></div>
<?php
		// DISPLAY FOLDER ICON
		printf('
			<div class="viewimage">
				<a href="%s" target="%s"><img src="%s" border="0" height="%d" width="%d" alt="" /></a></div>',
			$mg2->link,
			$mg2->target,
			$mg2->thumbfile,
			$mg2->height,
			$mg2->width
		);
?> 
		<div class="dir_right" style="height:<?php $mg2->output('height');?>px"></div>
		<div class="dir_bottomleft"></div>
		<div class="dir_bottom" style="width:<?php $mg2->output('width');?>px;"></div>
		<div class="dir_bottomright"></div>
	</div>
	<div class="subfolder-title" style="max-width:<?php echo $mg2->thumbMaxWidth*2;?>px">
		<?php $mg2->output('foldername');?>
	</div>
</td>
