<td valign="top" align="center">
	<table cellpadding="0" cellspacing="0" class="thumb<?php if($mg2->new) echo ' marknew';?>">
		<tr>
			<td>
			<?php printf('<a href="%s"><img src="%s" width="%d" height="%d" alt="%s" title="%s" class="thumb" /><span style="display:block;margin-top:%dpx">%s</span></a>',
						$mg2->link,
						$mg2->thumb_file,
						$mg2->thumb_width,
						$mg2->thumb_height,
						$mg2->tooltip,
						$mg2->tooltip,
						$mg2->distance,
						$mg2->title
					);
			?> 
			</td>
		</tr>
<?php if ($mg2->foldersetting & 128) { // Click-Counter ?>
		<tr>
			<td class="title">
				<?php printf('%s: %d', $mg2->lang['clicks'], $numberClicks);?>
			</td>
		</tr>
<?php }
		if ($mg2->foldersetting & 256) { // Comment-Counter ?>
		<tr>
			<td class="title">
<?php			if ($numberComments < 1) {
					echo $mg2->lang['nocomments'];
				}
				elseif ($numberComments > 1) {
					echo $mg2->lang['comments'] .': '. $numberComments;
				}
				else {
					echo $mg2->lang['comment'] .': 1';
				}
?> 
			</td>
		</tr>
<?php } ?>
	</table>
</td>
