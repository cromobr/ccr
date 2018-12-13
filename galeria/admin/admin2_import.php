<script language="JavaScript" type="text/javascript">
<!--
	function checkValues(box) {
		if (box == 'delete' && document.getElementById('delete').checked)
			document.getElementById('medium').checked = true;
		if (box == 'medium' && !document.getElementById('medium').checked)
			document.getElementById('delete').checked = false;
	}

	function setImportFolder(path, max) {
		var start  = path.lastIndexOf(' : ');
		var folder = (start == -1)? path:path.substring(start+3);
		var ldiff  = folder.length - max;
		if (ldiff > 0) folder = '...' + folder.substring(ldiff+3);
		document.getElementById('importdirs').innerHTML = folder;
		document.getElementById('importicon').title = "<?php echo $mg2->lang['import'];?> '" + folder + "'";
	}

	function checkImport() {
		return (document.getElementById('dirstruc').checked)?
			confirm("<?php echo $mg2->lang['importdirs'];?> '" +
						document.getElementById('importdirs').innerHTML +
						"'"
			)
			:
			true;
	}
-->
</script>
<table class="table_actions" cellpadding="0" cellspacing="0">
<form action="<?php echo ADMIN_INDEX;?>" method="post" onSubmit="return checkImport()">
<input type="hidden" name="action" value="import" />
<tr valign="top">
	<td rowspan="5" class="td_actions" width="140" align="center">
		<h3 style="padding:12px"><?php echo $mg2->lang['menutxt_import'];?></h3>
	</td>
	<td rowspan="5" class="td_actions_bottom" width="20">&nbsp;</td>
	<td colspan="3" class="td_actions_right">&nbsp;</td>
</tr>
<tr>
	<td width="160"><?php echo $mg2->lang['sourcefolder'];?></td>
	<td class="td_actions_right">
		<select size="1" name="importfrom" class="admindropdown">
			<option value=""><?php echo $mg2->imagefolder;?></option>
<?php
	// PULLDOWN BOX SUB DIRECTORIES (SERVER)
	$marker = ' style="background-color:#FF9999" title="'.$mg2->lang['writeprotected'].'"';
	foreach ($subdirs as $key=>$dir) {
		printf("\n".'<option value="%s"%s>%s</option>',
				$dir['path'],
				$dir['wrable'] ? '':$marker,
				implode('',$explorer[$key])
		);
	}
?> 
		</select>
	</td>
	<td rowspan="4" class="td_actions" width="240">
		<?php $pixel = sprintf("<a href=\"%s#layout\">%s</a>", $url2setup, $mg2->mediumimage);?>
		<input type="checkbox" id="thumb" name="thumb" value="1" style="vertical-align:middle;" <?php echo $checked['thumb'];?> />
		<label for="thumb" style="vertical-align:middle;"><?php echo $mg2->lang['createthumbs'];?></label>
		<br />
<?php if ((int)$mg2->mediumimage > 0) { ?>
		<input type="checkbox" id="medium" name="medium" value="1" style="vertical-align:middle;" onclick="checkValues('medium')" <?php echo $checked['medium'];?> />
		<label for="medium" style="vertical-align:middle;"><?php printf($mg2->lang['createmediums'],$pixel);?></label>
		<br />&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" />
		<input type="checkbox" id="delete" name="delete" value="1" style="vertical-align:middle;" onclick="checkValues('delete')" <?php echo $checked['delete'];?> />
		<label for="delete" style="vertical-align:middle;"><?php printf($mg2->lang['deloriginals'],$pixel);?></label>
<?php
		} else {
			printf('<div style="margin: 7px 3px 0 5px">'. $mg2->lang['intermediate'] .'</div>',
				sprintf('<a href="%s#layout">%s</a>', $url2setup, $mg2->lang['imgwidth'])
			);
		}
?> 
	</td>
</tr>
<tr>
	<td><?php echo $mg2->lang['import'];?></td>
	<td class="td_actions_right">
		<select size="1" name="fID" class="admindropdown" onchange="setImportFolder(this[selectedIndex].text,50)">
<?php
	foreach ($mg2->sortedfolders as $pathID=>$folderpath) {
	
		// FOLDER STATUS
		$style = array();
		$title = array();
		if ($folderpath[2] & 1) {	// folder published not yet?
			$style[1] = 'background-color: #FFFF99;';
			$title[1] = sprintf('%s %s', $mg2->lang['notpublished'], $mg2->time2date($folderpath[1]));
		}
		if ($folderpath[2] & 2) {	// folder locked?
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

		// DISPLAY FOLDER PATH
		printf('<option style="padding:1px 12px 1px 3px;%s" title="%s" value="%d"%s>%s</option>'."\n",
			implode('', $style),
			implode('; ', $title),
			$pathID,
			$pathID === $folderID ? ' selected="selected"':'',
			$folderpath[0]
		);
	}
	$importdir = sprintf('%s \'<span id="importdirs">%s</span>\'',
						 $mg2->lang['importdirs'],
						 $mg2->mb_shorten($mg2->getFolderName($folderID), -47)
					 );
?>
		</select>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td class="td_actions_right">
		<input type="checkbox" id="dirstruc" name="dirstruc" value="1" style="vertical-align:middle;" />		
		<label for="dirstruc" style="vertical-align:middle;"><?php echo $importdir;?></label>
	</td>
</tr>
<tr valign="top">
	<td class="td_actions_bottom">&nbsp;</td>
	<td class="td_actions_bottom">
<?php
	// CANCEL BUTTON
	printf('<a href="%s?fID=%d&amp;page=%s"><img src="%scancel.gif" width="24" height="24" alt="%5$s" title="%5$s" class="adminpicbutton" /></a>'."\n",
		ADMIN_INDEX,
		$folderID,
		$page,
		ADMIN_IMAGES,
		$mg2->lang['cancel']
	);
	// IMPORT BUTTON
	printf('<input id="importicon" type="image" src="%sok.gif" class="adminpicbutton" alt="%s" title="%s %s" />'."\n",
		ADMIN_IMAGES,
		$mg2->lang['ok'],
		$mg2->lang['import'],
		$mg2->getFolderName($folderID)
	);
?>
	</td>
</tr>
</form>
</table>
