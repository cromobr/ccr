<td valign="top" width="<?php echo $mg2->thumbMaxWidth+26+20;?>" align="center">
	<div class="thumb" style="width:<?php echo ($mg2->thumb_width+26);?>px">
		<div class="img_topleft"></div>
		<div class="img_top" style="width:<?php $mg2->output('thumb_width');?>px;"></div>
		<div class="img_topright"></div>
		<div class="img_left" style="height:<?php $mg2->output('thumb_height');?>px"></div>

<?php
		// DISPLAY THUMB ICON
		printf('
			<div class="viewimage">
				<a href="%s" target="_blank" rel="lyteframe" border="0" rev="width: 800px; height: 600px"><img src="%s" border="0" width="%d" height="%d" alt="%5$s" title="%5$s" /></a></div>',
				$mg2->link,
				$mg2->thumb_file,
				$mg2->thumb_width,
				$mg2->thumb_height,
				$mg2->tooltip
		);
?> 
		<div class="img_right" style="height:<?php $mg2->output('thumb_height');?>px"></div>
		<div class="img_bottomleft"></div>
		<div class="img_bottom" style="width:<?php $mg2->output('thumb_width');?>px;"></div>
		<div class="img_bottomright<?php if ($numberComments) echo '_comment';?>"></div>
	</div>
	<div class="thumb-title "><?php $mg2->output('title');?></div>
<?php
	// CLICK COUNTER
	if($mg2->foldersetting & 128) {
		printf("<div class=\"thumb-title \">%s: %d</div>\n", $mg2->lang['clicks'], $numberClicks);
	}
	// COMMENT COUNTER
	if($mg2->foldersetting & 256) {
		if ($numberComments < 1) {
			printf("<div class=\"thumb-title \">%s</div>\n", $mg2->lang['nocomments']);
		}
		elseif ($numberComments > 1) {
			printf("<div class=\"thumb-title \">%s: %d</div>\n", $mg2->lang['comments'], $numberComments);
		}
		else {
			printf("<div class=\"thumb-title \">%s: 1</div>\n", $mg2->lang['comment']);
		}
	}
?> 
</td>
