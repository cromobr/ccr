</table>
<input type="hidden" name="selectsize" value="<?php echo $selectsize;?>" />
<input type="hidden" name="fID" value="<?php echo $folderID;?>" />
<table class="table_files" cellpadding="0" cellspacing="0">
<tr>
	<td class="td_div" width="30" align="center">
		<img src="<?php echo ADMIN_IMAGES;?>checkbox_on.gif" width="13" height="13" alt="<?php echo $mg2->lang["checkall"];?>" title="<? echo $mg2->lang["checkall"];?>" onclick="checkAll(<? echo $selectsize;?>,'ctrl')" />
		<img src="<?php echo ADMIN_IMAGES;?>checkbox_off.gif" width="13" height="13" alt="<?php echo $mg2->lang["uncheckall"];?>" title="<? echo $mg2->lang["uncheckall"];?>" onclick="uncheckAll(<? echo $selectsize;?>,'ctrl')" />
	</td>
	<td class="td_files">
	<select size="1" name="moveto" class="admindropdown">
<?php
	foreach ($mg2->sortedfolders as $pathID=>$folderpath) {

		// THE CURRENT PARENT FOLDER
		if ($pathID === $folderID) continue;

		// FOLDER STATUS
		$style = array();
		$title = array();
		if ($folderpath[2] & 1) {				// folder published not yet?
			$style[1] = 'background-color: #FFFF99;';
			$title[1] = sprintf('%s %s', $mg2->lang['notpublished'], $mg2->time2date($folderpath[1]));
		}
		if ($folderpath[2] & 2) {				// folder locked?
			$style[1] = 'background-color: #FF9999;';
			$title[1] = $mg2->lang['nodisplay'];
		}
		if (($folderpath[2] & 12) === 12) {	// folder icon and password set?
			$style[2] = 'background-image: url('. ADMIN_IMAGES .'thumb_lock.gif);';
			$style[3] = 'background-repeat: no-repeat;';
			$style[4] = 'background-position: right;';
			$title[2] = $mg2->lang['presentation'];
			$title[3] = $mg2->lang['thissection'];
		}
		elseif ($folderpath[2] & 4) {			// folder icon set?
			$style[2] = 'background-image: url('. ADMIN_IMAGES .'thumb.gif);';
			$style[3] = 'background-repeat: no-repeat;';
			$style[4] = 'background-position: right;';
			$title[2] = $mg2->lang['presentation'];
		}
		elseif ($folderpath[2] & 8) {			// folder password set?
			$style[2] = 'background-image: url('. ADMIN_IMAGES .'lock.gif);';
			$style[3] = 'background-repeat: no-repeat;';
			$style[4] = 'background-position: right;';
			$title[2] = $mg2->lang['thissection'];
		}

		printf('<option style="padding:1px 12px 1px 3px;%s" title="%s" value="%s">%s</option>'."\n",
			implode('', $style),
			implode('; ', $title),
			$pathID,
			$folderpath[0]
		);
	}
?>
	</select>
<?php
	// MOVE BUTTON
	printf('<input type="submit" name="movefiles" value="%s" class="adminbutton" alt="%2$s" title="%2$s" onclick="return confirmSubmit(%3$d,\'file\',\'move\')" />'."\n",
		$mg2->lang['buttonmove'],
		$mg2->lang['ok'],
		$selectsize
	);
	// DELETE BUTTON
	printf('<input type="submit" name="deletefiles" value="%s" class="adminbutton" alt="%2$s" title="%2$s" onclick="return confirmSubmit(%3$d,\'file\',\'delete\')" />'."\n",
		$mg2->lang['buttondelete'],
		$mg2->lang['ok'],
		$selectsize
	);
?>
	</td>
</tr>
</table>
</form>
