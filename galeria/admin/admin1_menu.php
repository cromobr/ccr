<table class="table_menu" cellpadding="0" cellspacing="0">
  <tr align="center"> 
    <td width="706"><img src="admin/images/logo.gif" alt="" width="600" height="100" border="0" align="center" title="" /> 
    </td>
    <td width="1" align="center"><p>&nbsp;</p>
      </td>
  </tr>
  <tr valign="middle"> 
    <td height="25" colspan="2" align="right" valign="bottom"><div align="center"><hr noshade color="#808080" size="1">
<?php
	// NEW FOLDER BUTTON
	printf('<a href="%s?newfolder=%d&amp;page=%s"><img src="admin/images/menu_newfolder.gif" width="145" height="62" alt="%5$s" title="%5$s" border="0" /></a>',
		ADMIN_INDEX,
		$folderID,
		$page,
		ADMIN_IMAGES,
		$mg2->lang['newfolder']
	);
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
	// UPLOAD BUTTON
	printf('<a href="%s?startupload=%d&amp;page=%s"><img src="admin/images/menu_upload.gif" width="145" height="62" alt="%s" title="%s" border="0" /></a>',
		ADMIN_INDEX,
		$folderID,
		$page,
		ADMIN_IMAGES,
		$mg2->lang['menutxt_upload'],
		$mg2->lang['menutxt_upload_tt']
	);
?>
</div></td>
  </tr>
</table>
