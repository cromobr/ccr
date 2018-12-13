	<form name="fileform" action="<?php echo ADMIN_INDEX;?>" method="post">
	<table class="table_files" cellpadding="0" cellspacing="0">
	<tr<?php echo $tableHead;?>>
	<td class="td_navigation" colspan="8">
<?php echo $navigation;
		// PASSWORD SET
		if (!empty($mg2->all_folders[$folderID][8]))
			printf('&nbsp;&nbsp;<img src="%slock.gif" width="15" height="15" alt="%2$s" title="%2$s" style="vertical-align:text-bottom" />',
				ADMIN_IMAGES,
				$mg2->lang['thissection']
			);
?> 
	</td>
	<td class="td_div">
<?php
	// REBUILD BUTTON
	printf('<a href="%s?rebuildfolder=%d&amp;page=%s" onclick="return confirmRebuilt(\'%s\')"><img src="%sreload.gif" width="24" height="24" alt="%6$s" title="%6$s" border="0" /></a>',
		ADMIN_INDEX,
		$folderID,
		$currentPage,
		$mg2->getFolderName($folderID),
		ADMIN_IMAGES,
		$mg2->lang['rebuildimages']
	);
?> 
	</td>
	<td class="td_div">
<?php
	// EDIT BUTTON
	printf('<a href="%s?editfolder=%d&amp;page=%s"><img src="%sedit.gif" width="24" height="24" alt="%5$s" title="%5$s" border="0" /></a>',
		ADMIN_INDEX,
		$folderID,
		$currentPage,
		ADMIN_IMAGES,
		$mg2->lang['editcurrentfolder']
	);
?> 
	</td>
	<td class="td_div">
<?php
if ($folderID > 1) {
	// DELETE BUTTON
	printf('<a href="%s?deletefolder=%d&amp;page=%s"><img src="%sdelete.gif" width="24" height="24" alt="%5$s" title="%5$s" border="0" /></a>',
		ADMIN_INDEX,
		$folderID,
		$currentPage,
		ADMIN_IMAGES,
		$mg2->lang['deletecurrentfolder']
	);
} else echo '&nbsp;';
?> 
		</td>
	</tr>
	<tr>
		<td class="headline" width="30">&nbsp;</td>
		<td class="headline" width="40" align="center"><?php echo $mg2->lang['thumb'];?></td>
		<td class="headline" colspan="5"><?php echo $mg2->lang['filename'];?></td>
		<td class="headline" width="140" align="center"><?php echo $mg2->lang['position'];?></td>
		<td class="headline" width="100" align="center"><?php echo $mg2->lang['dateadded'];?></td>
		<td class="headline" width="40" align="center"><?php echo $mg2->lang['edit'];?></td>
		<td class="headline" width="40" align="center"><?php echo $mg2->lang['delete'];?></td>
	</tr>
