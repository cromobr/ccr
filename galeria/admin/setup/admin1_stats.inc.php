<br />
<table lass="table_actions" cellpadding="0" cellspacing="0" style="width:100%">
<tr>
	<td colspan="2" class="setup_noborder">
		<p>
			&nbsp;<i>Does not work yet!</i>
		</p>
	</td>
</tr>
<tr>
	<td colspan="2" class="setup_noborder">
		<div align="center">
<?php
		// CANCEL BUTTON
		printf("\n".'<a href="%s?fID=%d&amp;page=%s"><img src="%scancel.gif" width="24" height="24" alt="%5$s" title="%5$s" class="adminpicbutton" /></a>',
			ADMIN_INDEX,
			$folderID,
			$page,
			ADMIN_IMAGES,
			$this->lang['cancel']
		);
		// RELOAD BUTTON
		printf("\n".'<a href="%s?display=setup&amp;tab=%d&amp;fID=%d&amp;page=%s"><img src="%sreload.gif" alt="%6$s" title="%6$s" class="adminpicbutton" /></a>',
			ADMIN_INDEX,
			$tab,
			$folderID,
			$page,
			ADMIN_IMAGES,
			$this->lang['reload']
		);
?> 
		</div>
	</td>
</tr>
</table>
