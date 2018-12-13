<script language="JavaScript" type="text/javascript">
<!--
	function uncheckBox() {
		if (document.getElementById('incsubs').checked)
			document.getElementById('incsubs').checked = false;
	}

	function checkRadio(ID) {
		if (!document.getElementById(ID).checked)
			document.getElementById(ID).checked = true;
	}

	function callSorting() {
		document.location.href = "<?php printf('%s?fID=%d&page=%s&isort=1', ADMIN_INDEX, $folderID, $page);?>";
	}
-->
</script>
<form name="editfolder" action="<?php echo ADMIN_INDEX;?>" method="post">
<input type="hidden" name="fID" value="<?php echo $folderID;?>" />
<input type="hidden" name="page" value="<?php echo $page;?>" />
<input type="hidden" name="action" value="updatefolder" />
<?php	$rowspan = ($folderID === 1)? 4:5;?>
<table class="table_actions" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
	<td class="headline" colspan="4">
<?php
		echo $this->lang['editfolder'];
		if ($folderID === 1)	echo '&nbsp;('. $this->lang['root'] .')';
?>
	</td>
</tr>
<tr valign="top">
	<td class="td_actions_right"><?php echo $this->lang['presentation'];?></td>
	<td class="td_actions_right" width="100"><?php echo $this->lang['foldername'];?></td>
	<td class="td_actions_right">
<?php
	echo '<input type="text" name="name" value="'.$foldername.'" size="60" class="admintext" />';
	if (!empty($folderRC[8]))
		printf('&nbsp;<img src="%slock.gif" width="15" height="15" alt="%2$s" title="%2$s" class="adminpicbutton" />',
			ADMIN_IMAGES,
			$this->lang['thissection']
		);
?> 
	</td>
	<td class="td_actions_right" rowspan="<?php echo $rowspan;?>" valign="top">
		<table class="td_actions_noborder" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td class="td_actions_right" width="100">
<?php
					echo ($this->foldersetting & 15)?
						  $this->lang['sortby'] . '<sup>**</sup>'
						  :
						  $this->lang['sortby'];
?> 
				</td>
				<td class="td_actions_noborder">
				<?php /*
					Pulldownmenu für die Sortierung von Bildern und Ordnern. Die Werte von 'value' bzw. $folder entsprechen der Position der Einträge in
					den Bilddatensätzen (Array 'all_images') nach denen die Datensätze sortiert werden sollen. Die Datensätze werden mit Hilfe der Funktion
					'readdb()' in 'mg2_functions.php' in das Array 'all_images' eingelesen. Die Änderung der, in dieser Pulldownbox ausgewählten Optionen
					erfolgt mittels der Funktion editID() in 'mg2admin_functions.php'. 
				*/ ?>
					<select size="8" name="sortby" class="admindropdown">
						<option value="6"	 <?php if(($folderRC[7] & 15) ===  6)	echo 'selected="selected"';?>><?php echo $this->lang['name'];?></option>
						<option value="5"  <?php if(($folderRC[7] & 15) ===  5)	echo 'selected="selected"';?>><?php echo $this->lang['position'];?></option>					<!-- kh_mod 0.1.0, add-->
						<option value="4"  <?php if(($folderRC[7] & 15) ===  4)	echo 'selected="selected"';?>><?php echo $this->lang['date'];?></option>
						<option value="2"  <?php if(($folderRC[7] & 15) ===  2)	echo 'selected="selected"';?>><?php echo $this->lang['title'];?><sup>*</sup></option>		<!-- kh_mod 0.1.0, add-->
						<option value="3"  <?php if(($folderRC[7] & 15) ===  3)	echo 'selected="selected"';?>><?php echo $this->lang['description'];?></option>
						<option value="12" <?php if(($folderRC[7] & 15) === 12)	echo 'selected="selected"';?>><?php echo $this->lang['filesize'];?><sup>*</sup></option>
						<option value="8"  <?php if(($folderRC[7] & 15) ===  8)	echo 'selected="selected"';?>><?php echo $this->lang['width'];?><sup>*</sup></option>
						<option value="9"  <?php if(($folderRC[7] & 15) ===  9)	echo 'selected="selected"';?>><?php echo $this->lang['height'];?><sup>*</sup></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="td_actions_right"><?php echo $this->lang['direction'];?></td>
				<td class="td_actions_noborder">
					<select size="2" name="direction" class="admindropdown">
						<option value="0" <?php echo ($folderRC[7] & 16)? '':'selected="selected"';?>><?php echo $this->lang['ascending'];?></option>
						<option value="1" <?php echo ($folderRC[7] & 16)? 'selected="selected"':'';?>><?php echo $this->lang['descending'];?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="td_actions_right" style="padding:4px 0 0 3px" title="Generate position numbers automat.">
					<label for="generate_iPos"><?php echo $this->lang['setimagepositions'];?></label>
				</td>
				<td class="td_actions_noborder" style="padding-bottom:0">
					<input type="checkbox" name="generate_iPos" id="generate_iPos" value="ok">
				</td>
			</tr>
			<tr>
				<td class="td_actions_right" style="padding-top:1px;" title="Generate position numbers automat.">
					<label for="generate_fPos"><?php echo $this->lang['setfolderpositions'];?></label>
				</td>
				<td class="td_actions_noborder" style="padding-top:0">
					<input type="checkbox" name="generate_fPos" id="generate_fPos" value="ok">
				</td>
			</tr>
			<tr>
				<td colspan="2" class="td_actions_noborder">
					<br />
<?php
					if ($this->foldersetting & 15) {
						if (($this->foldersetting & 15) === 6) $element = $this->lang['name'];
						if (($this->foldersetting & 15) === 5) $element = $this->lang['position'];
						if (($this->foldersetting & 15) === 4) $element = $this->lang['date'];
						if (($this->foldersetting & 15) === 3) $element = $this->lang['description'];
						printf('<sup>**</sup><a href="%s?display=setup&amp;fID=%d&amp;page=%s#thumbs">%s</a>: %s \'%s\'',
							ADMIN_INDEX,
							$folderID,
							$page,
							$this->lang['menutxt_setup'],
							$this->lang['foldersort'],
							$element
						);
					}
					else
						echo $this->lang['sortfolder'];
?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<?php
if ($folderID > 0) {
	echo '
		<tr>
		<td rowspan="'. $rowspan .'" class="td_files" style="border-bottom:0" width="170" align="center">
	';
		// SETUP: FORCE FOLDER ICONS?
		if ($folderID > 1 && $this->foldersetting & 16) {
			printf('<div style="%s"><a href="%s?display=setup&amp;fID=%d&amp;page=%s#thumbs">%s</a>: %s</div>',
				'margin-bottom:18px',
				ADMIN_INDEX,
				$folderID,
				$page,
				$this->lang['menutxt_setup'],
				$this->lang['foldericons']
			);
			$margin_bottom = '42px';
		}
		else {
			$margin_bottom = '12px';
		}
?> 
		<a href="<?php echo $this->getGalleryLink(array('fID'=>$folderID,'page'=>$page,'user'=>GALLERY_ID));?>" target="_blank">
		<img src="<?php echo $icon['path'];?>" <?php echo $icon['attrb'];?> alt="<?php echo $this->lang['viewfolder'];?>" title="<?php echo $this->lang['viewfolder'];?>" border="0" /></a>
		<div style="margin-top:12px;margin-bottom:<?php echo $margin_bottom;?>">
<?php
// FOLDER ICON (EXCEPT ROOT FOLDER)
if ($folderID > 1) {
	echo '<table cellpadding="0" cellspacing="0" border="0">';
	$first_image	= '';
	$inc_subfoldrs	= '';
	$folder_images = '';
	$default_icon	= '';
	// use set image as folder icon?
	if ($icon['id'] > 0) {
		$thumbfile = basename($icon['thumb']);
		$thumbfile = $this->mb_shorten($thumbfile, 20);
		if ($this->all_images[$icon['id']][5] < 0) {
			$thumbtitle = sprintf('%s: %s', $this->lang['nodisplay'], $icon['thumb']);
			$thumbcolor = ' style="background-color: #FCA4A4;"';	// original #FFCFCF;
		} elseif ($this->all_images[$icon['id']][4] > time()) {
			$thumbtitle = sprintf('%s %s; %s',
									$this->lang['notpublished'],
									$this->time2date($this->all_images[$icon['id']][4]),
									$icon['thumb']
							  );
			$thumbcolor = ' style="background-color:#EFEE73;"';	// original #FFFF99;
		} else {
			$thumbtitle = $icon['thumb'];
			$thumbcolor = '';
		}
		echo '
		<tr><td class="td_actions_noborder"'.$thumbcolor.' align="left">
			<input type="radio" name="icon" id="imageicon" value="'. $icon['id'] .'" class="adminpicbutton" onclick="uncheckBox()" checked="checked" />
			<label for="imageicon" style="vertical-align:middle;" title="'. $thumbtitle .'">'. $thumbfile .'</label>
		</td></tr>
		';
	}
	// use random, first, or latest image as folder icon?
	elseif (abs($icon['id']) & 7) {
		// use an image as folder icon
		$folder_images  = 'checked="checked"';
		// first image?
		if (abs($icon['id']) & 2)	$first_image	= ' selected="selected"';
		// latest image?
		if (abs($icon['id']) & 4)	$latest_image	= ' selected="selected"';
		// inc. images from sub folders
		if (abs($icon['id']) & 8)	$inc_subfoldrs	= 'checked="checked"';
	}
	// use default icon
	else {
		$default_icon = 'checked="checked"';
	}
	echo '
		<tr>
			<td class="td_actions_noborder" align="left">
				<input type="radio" name="icon" id="defaulticon" value="" class="adminpicbutton" onclick="uncheckBox()" '. $default_icon .' />
				<label for="defaulticon" style="vertical-align:middle;">'. $this->lang['defaulticon'] .'</label>
			</td>
		</tr><tr>
			<td class="td_actions_noborder" align="left">
				<input type="radio" name="icon" id="folderimages" value="-1" class="adminpicbutton" '. $folder_images .' />
				<select name="select_icon" size="1" class="admindropdown" onChange="checkRadio(\'folderimages\')">
					<option value="1">'. $this->lang['randomimage'] .'</option>
					<option value="2"'. $first_image .'>'. $this->lang['firstimage']. '</option>
					<option value="4"'. $latest_image .'>'. $this->lang['latestimage']. '</option>
				</select>
			</td>
		</tr><tr>
			<td class="td_actions_noborder" align="left">
				&nbsp;&nbsp;
				<img src="'. ADMIN_IMAGES .'corner.gif" width="8" height="10" alt="" />
				<input type="checkbox" id="incsubs" name="incsubs" value="1" style="vertical-align:top;" onclick="checkRadio(\'folderimages\')" '. $inc_subfoldrs.' />
				<label for="incsubs" style="vertical-align:middle;">'. $this->lang['incsubfolders'] .'</label>
			</td>
		</tr>
	</table>
	</div>
	';
}
echo '
		</td>
		<td class="td_actions_right" width="100">
			<span title="'. $this->lang['negative_pos'] .'">'. $this->lang['position'] .'</span>
		</td>
		<td class="td_actions_right">
			<input type="text" name="position" value="'.$position.'" size="26" class="admintext" />
			<img src="'. ADMIN_IMAGES .'sorting.gif" width="17" height="15" alt="'.$this->lang['sorting'].'" title="'.$this->lang['sorting'].'" class="adminpicbutton" onClick="callSorting()" />
		</td>
	</tr>
	';
}
?>
<tr>
	<td class="td_actions_right" width="100"><?php echo $this->lang['publish'];?></td>
	<td class="td_actions_right">
		<input type="text" name="publish" id="publish" value="<?php echo $publish;?>" size="26" class="admintext" />
<?php
		if (is_a($this->Calendar, 'MG2Calendar')) {
			echo '&nbsp;'.
			$this->Calendar->_make_calendar(
				// calendar options go here; see the documentation and/or calendar-setup.js
				array('date'			=> $publish,							// CALENDAR START DATE
						'ifFormat'		=> $this->dateformat .', %H:%M',	// CALENDAR FORMAT
						'inputField'	=> 'publish')							// INPUT FIELD ID
			);
		}
?>
	</td>
</tr>
<?php
	// SELECT BOX MOVE FOLDER
	if ($folderID > 1) {
		echo '
			<tr>
			<td class="td_actions_right">'.$this->lang['moveto'].'</td>
			<td class="td_actions_right">
			<select size="1" name="moveto" class="admindropdown">
		';
		$option = '<option style="padding:1px 12px 1px 3px;%s" title="%s" value="%s">%s</option>'."\n";
		printf($option,
			'',
			'',
			$parentID,
			'-- '.$this->lang['nofolderselected'].' --'
		);
		foreach ($this->sortedfolders as $pathID=>$folderpath) {

			// THE CURRENT PARENT FOLDER
			if ($pathID === $parentID) continue;

			// FOLDER STATUS
			$style = array();
			$title = array();
			if ($folderpath[2] & 1) {				// folder published not yet?
				$style[1] = 'background-color: #FFFF99;';
				$title[1] = sprintf('%s %s', $this->lang['notpublished'], $this->time2date($folderpath[1]));
			}
			if ($folderpath[2] & 2) {				// folder locked?
				$style[1] = 'background-color: #FF9999;';
				$title[1] = $this->lang['nodisplay'];
			}
			if (($folderpath[2] & 12) === 12) {	// folder icon and password set?
				$style[2] = 'background-image: url('. ADMIN_IMAGES .'thumb_lock.gif);';
				$style[3] = 'background-repeat: no-repeat;';
				$style[4] = 'background-position: right;';
				$title[2] = $this->lang['presentation'];
				$title[3] = $this->lang['thissection'];
			}
			elseif ($folderpath[2] & 4) {			// folder icon set?
				$style[2] = 'background-image: url('. ADMIN_IMAGES .'thumb.gif);';
				$style[3] = 'background-repeat: no-repeat;';
				$style[4] = 'background-position: right;';
				$title[2] = $this->lang['presentation'];
			}
			elseif ($folderpath[2] & 8) {			// folder password set?
				$style[2] = 'background-image: url('. ADMIN_IMAGES .'lock.gif);';
				$style[3] = 'background-repeat: no-repeat;';
				$style[4] = 'background-position: right;';
				$title[2] = $this->lang['thissection'];
			}

			// NO CIRCLE LINK?
			if (~$folderpath[2] & 16) {
				printf($option,
					implode('', $style),
					implode('; ', $title),
					$pathID,
					$folderpath[0]
				);
			}
		}
		echo '
			</select>
			</td></tr>
		';
	}
?>
<tr>
	<td class="td_actions_right" width="100"><?php echo $this->lang['introtext'];?></td>
	<td class="td_actions_right"><table class="wysiwyg_editor" width="484"><tr><td>
		<textarea id="editor" cols="62" rows="11" name="introtext" class="admindropdown" style="padding:2px;width:480px;height:200px;"><?php echo $introtext;?></textarea>
	</td>
</tr>
</table></td>
</tr>
<tr>
  <td class="td_actions_right" width="100"><?php echo $this->lang['newpassword'];?></td>
  <td class="td_actions_right">
    <input type="password" name="password" value="" size="26" class="admintext" autocomplete="off" />
  </td>
  <td class="td_actions" rowspan="2" style="vertical-align:middle;">
<?php
	// Thumbnails per page
	$globalsetting = sprintf('<a href="%s?display=setup&amp;fID=%d&amp;page=%s#thumbs">%d x %d</a>',
								ADMIN_INDEX,
								$folderID,
								$page,
								$this->imagecols,
								$this->imagerows
						  );
?><div>
		&nbsp;
	</div>
  </td>
</tr>
<tr>
	<td class="td_actions">&nbsp;</td>
	<td class="td_actions" width="100">
		<label for="deletepassword"><?php echo $this->lang['deletepassword'];?></label>
	</td>
	<td class="td_actions">
		<input type="checkbox" name="deletepassword" id="deletepassword" value="1" />
	</td>
</tr>
<tr>
	<td colspan="4" align="center" class="td_actions">
<?php
		// CANCEL BUTTON
		printf("\n".'<a href="%s?fID=%d&amp;page=%s"><img src="admin/images/cancelar.gif" width="47" height="22" alt="%5$s" title="%5$s" class="adminpicbutton"  /></a>',
			ADMIN_INDEX,
			$folderID,
			$page,
			ADMIN_IMAGES,
			$this->lang['cancel']
		);
		// OK BUTTON
		printf("\n".'<input type="image" src="admin/images/aplicar.gif" class="adminpicbutton" alt="%2$s" title="%2$s" />',
			ADMIN_IMAGES,
			$this->lang['ok']
		);
?> 
	</td>
</tr>
</table>
</form>