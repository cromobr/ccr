<td valign="top" align="center">
	<table cellpadding="0" cellspacing="0" class="<?php $mg2->output('subfolder_class'); if($mg2->new) echo ' marknew';?>">
		<tr>
			<td>
			<?php printf('<a href="%s" target="%s"><img src="%s" height="%d" width="%d" alt="" class="thumb" /><span style="display:block;margin-top:%dpx">%s</span></a>',
						$mg2->link,
						$mg2->target,
						$mg2->thumbfile,
						$mg2->height,
						$mg2->width,
						$mg2->distance,
						$mg2->foldername
					);
			?> 
			</td>
		</tr>
	</table>
</td>
