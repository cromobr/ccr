<?php
	$max_upload  = get_cfg_var('upload_max_filesize');
	$mg2->status = $mg2->lang['maxupload'] .': ';
	$mg2->status.= preg_replace('/(\d+)M$/','${1} MByte',$max_upload);
	$mg2->displaystatus();
?>
<script language="JavaScript" type="text/javascript">
<!--
	function checkValues(box) {
		if (box == 'delete' && document.getElementById('delete').checked)
			document.getElementById('medium').checked = true;
		if (box == 'medium' && !document.getElementById('medium').checked)
			document.getElementById('delete').checked = false;
	}
-->
</script>
<form name="uploadform" action="<?php echo ADMIN_INDEX;?>?fID=<?php echo $folderID;?>&amp;loading=1" method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="upload" />
<table class="table_actions" cellpadding="0" cellspacing="0">
<tr valign="top">
  <td colspan="3">&nbsp;</td>
</tr>
<tr>
  <td class="td_actions_right" width="4">&nbsp;</td>
	<td class="td_actions_noborder" width="240">
		<?php	$pixel = sprintf($mg2->mediumimage);?>
		<input type="checkbox" id="thumb" name="thumb" value="1" style="vertical-align:middle;" <?php echo $checked['thumb'];?> />
		<label for="thumb" style="vertical-align:middle;" title=""><?php echo $mg2->lang['createthumbs'];?></label>
		<br />
<?php if ((int)$mg2->mediumimage > 0) { ?>
		<input type="checkbox" id="medium" name="medium" value="1" style="vertical-align:middle;" onclick="checkValues('medium')" <?php echo $checked['medium'];?> />
		<label for="medium" style="vertical-align:middle;" title=""><?php printf($mg2->lang['createmediums'],$pixel);?></label>
		<br />&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" />
		<input type="checkbox" id="delete" name="delete" value="1" style="vertical-align:middle;" onclick="checkValues('delete')" <?php echo $checked['delete'];?> />
		<label for="delete" style="vertical-align:middle;" title=""><?php printf($mg2->lang['deloriginals'],$pixel);?></label>
<?php	} else { ?>
		<div style="margin:3px 3px 0 5px">
		</div>
<?php	} ?> 		
	</td>
</tr>
<tr>
	<td colspan="3" class="td_actions_bottom">&nbsp;</td>
</tr>
</table>
<table class="table_actions" cellpadding="0" cellspacing="0">
<tr>
	<td width="25" class="headline">&nbsp;</td>
	<?php
		$regexp = "/([a-z0-9]+)(,?)/ie";
		$ersatz = "'*.\\1'. (('\\2')? ', ':'')";
		$media  = preg_replace($regexp, $ersatz, $mg2->extensions);
		?> 
	<td width="450" class="headline"><?php printf('%s (%s)', $mg2->lang['image'], $media);?></td>
	<td class="headline"><?php echo $mg2->lang['import'];?></td>
	<td width="96" class="headline" align="center"><?php echo $mg2->lang['overwrite'];?></td>
</tr>
<?php
	// BUILD OPTION TAGS FOR FOLDER SELECT
	$option_tags = "\n";
	foreach ($mg2->sortedfolders as $pathID=>$folderpath) {

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

		// BUILD TAG
		$option_tags.= sprintf('<option style="padding:1px 12px 1px 3px;%s" title="%s" value="%d"%s>%s</option>'."\n",
								implode('', $style),
								implode('; ', $title),
								$pathID,
								$pathID === $folderID ? ' selected="selected"':'',
								$folderpath[0]
		);
	}
	for ($x = 0; $x < 10; $x++) { ?>
<tr class="admintdleft">
	<td class="td_actions" align="right"><?php echo $x+1;?>&nbsp;</td>
	<td class="td_actions"><input type="file" name="file[<?php echo $x;?>]" size="60" class="adminbutton" /></td>
	<td class="td_actions">
		<select size="1" name="uploadto[<?php echo $x;?>]" class="admindropdown">
			<?php echo $option_tags;?>
		</select>
	</td>
	<td class="headline" align="center"><input type="checkbox" name="overwrite<?php echo $x;?>" /></td>
</tr>
<? } ?> 
<tr>
	<td class="td_actions_bottom">&nbsp;</td>
	<td class="td_actions" align="center" colspan="2">
<?php
	// CANCEL BUTTON
	printf("\n".'<a href="%s?fID=%d&amp;page=%s"><img src="admin/images/cancelar-envio.gif" class="adminpicbutton" width="62" height="43" alt="%5$s" title="%5$s" /></a>',
		ADMIN_INDEX,
		$folderID,
		$page,
		ADMIN_IMAGES,
		$mg2->lang['cancel']
	);
	// UPLOAD BUTTON
	printf("\n".'<input type="image" src="admin/images/enviar-imagens.gif" class="adminpicbutton" alt="%2$s" title="%2$s" />',
		ADMIN_IMAGES,
		$mg2->lang['upload']
	);
?> 
	</td>
	<td class="td_files" align="center">
<?php
	// CHECK ALL BUTTON
	printf("\n".'<img src="%scheckbox_on.gif" width="13" height="13" alt="%2$s" title="%2$s" onclick="%3$s" />',
		ADMIN_IMAGES,
		$mg2->lang['checkall'],
		"checkAll(10,'upld')"
	);
	// UNCHECK ALL BUTTON
	printf("\n".'<img src="%scheckbox_off.gif" width="13" height="13" alt="%2$s" title="%2$s" onclick="%3$s" />',
		ADMIN_IMAGES,
		$mg2->lang["uncheckall"],
		"uncheckAll(10,'upld')"
	);
?> 
	</td>
</tr>
</table>
</form>