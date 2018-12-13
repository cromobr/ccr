<table class="table_actions" cellpadding="0" cellspacing="0">
<tr valign="top">
	<td class="td_actions_bottom" width="160" align="center">
<?php
	// PRINT THUMBNAIL
	printf('<img src="%s?%d" %s alt="%1$s" title="%1$s" />',
		$icon['path'],
		rand(0,10000),
		$icon['attrb']
	);
?>
	</td>
	<td class="td_actions_bottom" align="center">
		<div style="font-size:9pt;margin-bottom:18px">
			<?php printf($this->lang['deletefolder'], $this->getFolderName($delfolder));?>
		</div>
		<p>
<?php
	// CANCEL BUTTON
	printf('<a href="%s"><img src="admin/images/cancelar.gif" width="47" height="22" alt="%3$s" title="%3$s" class="adminpicbutton" /></a>'."\n",
		$cancel,
		ADMIN_IMAGES,
		$mg2->lang['cancel']
	);
	// OK BUTTON
	printf('<a href="%s"><img src="admin/images/deletar.gif" width="47" height="22" alt="%3$s" title="%3$s" class="adminpicbutton" /></a>'."\n",
		$href_ok,
		ADMIN_IMAGES,
		$mg2->lang['ok']
	);
?>
		</p>
	</td>
	<td class="td_actions" width="160">&nbsp;</td>
</tr>
</table>
