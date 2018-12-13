<?php if ($display === 'comment') { ?>
<table class="table_files" cellpadding="0" cellspacing="0">
<tr>
	<td class="td_files" colspan="7">&nbsp;
	<?php printf('%s: %s : %s', $this->mg2->lang['navigation'], $this->mg2->adminnavigation($folderID), $fileName);?></td>
</tr>
</table>
<?php } ?>
<table class="table_actions" cellpadding="0" cellspacing="0">
<tr valign="top">
	<td class="td_actions_bottom" width="160" align="center">
<?php
	// PRINT THUMBNAIL
	printf('<img src="%s?%d" width="%d" height="%d" alt="%1$s" title="%1$s" class="thumb" />',
		$thumbFile,
		rand(0,10000),
		$thumbWidth,
		$thumbHeight
	);
?>
	</td>
	<td class="td_actions_bottom" align="center">
		<div style="font-size:9pt;margin-bottom:18px"><?php echo $message;?></div>
		<p>
<?php
	// CANCEL BUTTON
	printf('<a href="%s"><img src="admin/images/cancelar.gif" width="47" height="22" alt="%3$s" title="%3$s" class="adminpicbutton" /></a>'."\n",
		$cancel_href,
		ADMIN_IMAGES,
		$cancel_title
	);
	// OK BUTTON		
	printf('<a href="%s"><img src="admin/images/deletar.gif" width="47" height="22" alt="%3$s" title="%3$s" class="adminpicbutton" /></a>'."\n",
		$ok_href,
		ADMIN_IMAGES,
		$ok_title
	);
?>		
		</p>
	</td>
	<td class="td_actions" width="160">&nbsp;</td>
</tr>
</table>
<?php if ($display === 'comment') { ?>
<table class="table_actions" cellpadding="0" cellspacing="0">
<tr>
	<td class="headline" width="30">&nbsp;</td>
	<td class="headline" width="115"><?php echo $this->mg2->lang['date'];?></td>
	<td class="headline" width="146"><?php echo $this->mg2->lang['from'];?></td>
	<td class="headline"><?php echo $this->mg2->lang['comment'];?></td>
</tr>
<tr>
	<td class="td_files" align="center"><img src="<?php echo ADMIN_IMAGES;?>checkbox_on.gif" width="13" height="13" alt=""></td>
	<td class="td_files"><?php echo $comment['date'];?></td>
	<td class="td_files"><a href="mailto:<?php echo $comment['email'];?>"><?php echo $comment['name'];?></a></td>
	<td class="td_files"><?php echo $comment['body'];?></td>
</tr>
</table>
<?php } ?>