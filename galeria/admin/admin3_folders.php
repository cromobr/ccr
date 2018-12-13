<?php
	// FOLDER LOCKED?
	if ($folders[$i][5] < 0)
		$marked = sprintf(' bgcolor="#FFCFCF" title="%s (%s %d)"',
						 $mg2->lang['nodisplay'],
						 $mg2->lang['position'],
						 $folders[$i][5]
					 );
	// DATE IN FUTURE?
	elseif ($folders[$i][4] > time())
		$marked = sprintf(' bgcolor="#FFFF99" title="%s %s"',
						 $mg2->lang['notpublished'],
						 $publishdate
					 );
	else
		$marked = '';
?>
<tr<?php echo $marked;?>>
	<td class="td_div" width="30">&nbsp;</td>
	<td class="td_div" width="50">
<?php
	// FOLDER ICON
	printf('<a href="%s?editfolder=%d"><img src="%s" width="30" height="21" alt="%4$s" title="%4$s" border="0" onmouseover="%5$s" onmouseout="UnTip()" /></a>',
		ADMIN_INDEX,
		$folders[$i][0],
		$small_icon,
		$mg2->lang['editfolder'],
		$folderThumb ? 'Tip(\''.$folderThumb.'\')':''
	);
?> 
	</td>
	<td class="td_files" colspan="5">
		<a href="<?php echo ADMIN_INDEX;?>?fID=<?php echo $folders[$i][0];?>"><?php echo $folders[$i][2];?></a>
	</td>
	<td class="td_files" width="100" align="center">
		<div style="text-align:right;width:40px" title="<?php echo $mg2->lang['incsubfolders'];?>"><?php echo $n_pictures[(int)$folders[$i][0]];?></div>
	</td>
	<td class="td_files" width="100" align="center"><span title="<?php echo $publishtime;?>"><?php echo $publishdate;?></span></td>
	<td class="td_div" width="40">
<?php
	// EDIT BUTTON
	printf('<a href="%s?editfolder=%d"><img src="%sedit.gif" width="24" height="24" alt="%4$s" title="%4$s" border="0" /></a>',
		ADMIN_INDEX,
		$folders[$i][0],
		ADMIN_IMAGES,
		$mg2->lang['edit']
	);
?> 
	</td>
	<td class="td_div" width="40">
<?php
	// DELETE BUTTON
	printf('<a href="%s?deletefolder=%d&amp;fID=%d&amp;page=%s"><img src="%sdelete.gif" width="24" height="24" alt="%6$s" title="%6$s" border="0" /></a>',
		ADMIN_INDEX,
		$folders[$i][0],
		$folders[$i][1],
		$currentPage,
		ADMIN_IMAGES,
		$mg2->lang['delete']
	);
?> 
	</td>
</tr>
