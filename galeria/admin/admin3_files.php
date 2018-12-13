<?php
	// IMAGE LOCKED?
	if ($images[$i][5] < 0)
		$marked = sprintf(' bgcolor="#FFCFCF" title="%s (%s %d)"',
						 $mg2->lang['nodisplay'],
						 $mg2->lang['position'],
						 $images[$i][5]
					 );
	// DATE IN FUTURE?
	elseif ($images[$i][4] > time())
		$marked = sprintf(' bgcolor="#FFFF99" title="%s %s"',
						 $mg2->lang['notpublished'],
						 $publishdate
					 );
	else
		$marked = '';
?>
<tr<?php echo $marked;?>>
	<td class="td_div" width="30"><input type="checkbox" name="selectfile<?php echo $num;?>" value="<?php echo $imageID;?>" /></td>
	<td class="td_div" width="50" title="">
<?php
	// DISPLAY ITEM ICON
	printf('<a href="%s?editID=%d"><img src="%s" width="%d" height="%d" alt="" class="thumb" onmouseover="Tip(\'%s\')" onmouseout="UnTip()" /></a>',
		ADMIN_INDEX,
		$imageID,
		$thumbFile,
		$miniThumbWidth,
		$miniThumbHeigth,
		$thumbInfo
	);
?> 
	</td>
	<td class="td_files" colspan="5"><span title="<?php echo $imagefile;?>"><?php echo $imagename;?></span></td>
	<td class="td_files" width="140" align="center"><?php echo $images[$i][5];?>&nbsp;&nbsp;</td>
	<td class="td_files" width="100" align="center"><span title="<?php echo $publishtime;?>"><?php echo $publishdate;?></span></td>
	<td class="td_div" width="40">
<?php
	// EDIT BUTTON
	$edit_icon = (empty($images[$i][2]) && empty($images[$i][3]))? 'edit_dimmed':'edit';
	printf('<a href="%s?editID=%d"><img src="%s%s.gif" width="24" height="24" alt="%5$s" title="%5$s" border="0" /></a>',
		ADMIN_INDEX,
		$imageID,
		ADMIN_IMAGES,
		$edit_icon,
		$mg2->lang['edit']
	);

?> 
	</td>
	<td class="td_div" width="40">
<?php
	// DELETE BUTTON
	printf('<a href="%s?deleteID=%d&amp;page=%s"><img src="%sdelete.gif" width="24" height="24" alt="%5$s" title="%5$s" border="0" /></a>',
		ADMIN_INDEX,
		$imageID,
		$currentPage,
		ADMIN_IMAGES,
		$mg2->lang['delete']
	);
?> 
	</td>
</tr>
