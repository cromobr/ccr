<?php
		// GET COMMENT FILES
		$workdir  = opendir($this->imagefolder);
		$comments = 0;
		while (false !== ($pointer = readdir($workdir))) {
			if ($pointer{0} === '.')							continue;
			if (strrchr($pointer, '.') !== '.comment')	continue;

			$comments++;	// count comment files
		}
?>
<script language="JavaScript" type="text/javascript">
<!--
function confirmAction(type) {
	if (type=='backup') {
		return (is_checkedBackup())?
				 confirm("<?php echo $this->lang['createbackup'];?>")
				 :
				 false;
	}
	else if (type=='restore_backup') {
		return (is_checkedUsing())?
				 confirm("<?php echo $this->lang['restorebackup'];?>")
				 :
				 false;
	}
	else if (type=='delete_backup') {
		return (is_checkedUsing())?
				 confirm("<?php echo $this->lang['deletebackup'];?>")
				 :
				 false;
	}
	else if (type=='click_import')
		return confirm("<?php echo $this->lang['counterimport'];?>");
	else if (type=='folder_import')
		return confirm("<?php printf('%s MG2 0.5.0/0.5.1 %s kh_mod 0.1.0\n-> %d %s (%s)',
										$this->lang['dbimport'],
										$this->lang['or'],
										$num_folderRC,
										$this->lang['folderdata'],
										$this->lang['records']
									);?>");
	else if (type=='image_import')
		return confirm("<?php printf('%s MG2 0.5.0/0.5.1 %s kh_mod 0.1.0\n-> %d %s (%s)',
										$this->lang['dbimport'],
										$this->lang['or'],
										$num_imageRC,
										$this->lang['imagedata'],
										$this->lang['records']
									);?>");
	else if (type=='convert')
		return confirm("<?php printf("%s (%s)", $this->lang['comments_convert'], $comments);?>");
	else if (type=='dbswitch') {
		var chosenDB = (document.getElementById('mysql').checked)? 'mysql':'flatfile';
		var activeDB = "<?php echo ($this->sqldatabase)? 'mysql':'flatfile';?>";
		if (chosenDB == activeDB) {
			alert("<?php echo $this->lang['dbselected_active'];?>");
			return false;
		}
		else {
			return confirm("<?php echo $this->lang['switchdatabase'];?>");
		}
	}
}

function is_checkedBackup() {
	if (document.getElementById('folder').checked)		 return true;
	else if (document.getElementById('media').checked) return true;
	else if (document.getElementById('counter').checked)	 return true;
	else if (document.getElementById('settings').checked)	 return true;
	else {
		alert("<?php echo $this->lang['nodataselected'];?>");
		return false;
	}
}

function is_checkedUsing() {
	if (document.getElementById('use_folder').checked		 	&& is_date('folder'))	return true;
	else if (document.getElementById('use_media').checked 	&& is_date('media'))		return true;
	else if (document.getElementById('use_counter').checked	&& is_date('counter'))	return true;
	else if (document.getElementById('use_settings').checked	&& is_date('settings'))	return true;
	else {
		alert("<?php echo $this->lang['nodataselected'];?>");
		return false;
	}
}

function is_date(database) {
	var a = document.getElementById('date_' + database);
	return (a[a.selectedIndex].value != -1)?
			 true
			 :
			 false;
}
-->
</script>
<br />
<table lass="table_actions" cellpadding="0" cellspacing="0" style="width:100%">
<tr valign="top">
  <td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['operations'];?></td>
</tr>
<form action="<?php echo ADMIN_INDEX;?>" method="post" onSubmit="return confirmAction('dbswitch');">
<tr class="setupitem">
	<td class="setup_right" width="200"><?php echo $this->lang['switchdatabase'];?></td>
	<td class="setup_noborder">
		<input type="hidden" name="action" value="switchdb" />
		<input type="hidden" name="fID" value="<?php echo $folderID;?>" />
		<input type="hidden" name="page" value="<?php echo $page;?>" />
		<input type="hidden" name="display" value="setup" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
		<input type="radio" style="vertical-align:middle;" name="database" id="flatfile" <?php if(!$this->sqldatabase) echo 'checked="checked"';?> value="flatfile" />
		<label for="flatfile" style="vertical-align:middle;"><?php echo $this->lang['flatfile'];?></label> <span style="vertical-align:middle;">|</span>
		<input type="radio" style="vertical-align:middle;" name="database" id="mysql" <?php if($this->sqldatabase==1) echo 'checked="checked"';?> value="mysql" />
		<label for="mysql" style="vertical-align:middle;">MySQL</label>&nbsp;
		<input type="submit" class="adminbutton" value=" <?php echo $this->lang['go'];?> " title="<?php echo $this->lang['switchdatabase'];?>" />
	</td>
</tr>
</form>
<form action="<?php echo ADMIN_INDEX;?>" method="post" onSubmit="return confirmAction('backup')">
<tr class="setupitem">
	<td class="setup" width="200"><?php echo $this->lang['createbackup'];?></td>
	<td class="setup_bottom">
		<input type="hidden" name="action" value="makeBackup" />
		<input type="hidden" name="fID"  value="<?php echo $folderID;?>" />
		<input type="hidden" name="page" value="<?php echo $page;?>" />
		<input type="hidden" name="display" value="setup" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
		<input type="checkbox" style="vertical-align:middle;" name="folder" id="folder" value="1" checked="checked" />
		<label for="folder"><?php echo $this->lang['folderdata'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="media" id="media" value="1" checked="checked" />
		<label for="media"><?php echo $this->lang['imagedata'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="settings" id="settings" value="1" checked="checked" />
		<label for="settings"><?php echo $this->lang['setup'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="counter" id="counter" value="1" checked="checked" />
		<label for="counter"><?php echo $this->lang['counterdata'];?></label>
<!--
		|
		<input type="checkbox" style="vertical-align:middle;" name="comments" id="comments" value="1" checked="checked" />
		<label for="comments"><?php echo $this->lang['commentdata'];?></label>
-->
		&nbsp;
		<input type="submit" class="adminbutton" value=" <?php echo $this->lang['go'];?> " title="<?php echo $this->lang['createbackup'];?>" />
	</td>
</tr>
</form>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0"><?php echo $this->lang['usebackup'];?></td>
</tr>
<form action="<?php echo ADMIN_INDEX;?>" method="post">
<tr class="setupitem">
	<td class="setup_right" width="200"><?php echo $this->lang['folderdata'];?></td>
	<td class="setup_noborder">
		<input type="hidden" name="action" value="useBackup" />
		<input type="hidden" name="fID"  value="<?php echo $folderID;?>" />
		<input type="hidden" name="page" value="<?php echo $page;?>" />
		<input type="hidden" name="display" value="setup" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
<?php
	// CHECKBOX AND SELECT HEAD OF BACKUP DATA
	$select_head = '<input type="checkbox" id="use_%1$s" name="use_%1$s" value="1" class="adminpicbutton" />';
	$select_head.= '<select size="1" id="date_%1$s" name="%1$s" class="admindropdown">';

	// FOLDER DATA
	$selectbox = 'fdatabase';
	printf($select_head, 'folder');
	if (empty($all_backups[$selectbox])) {
		echo '<option value="-1">'. $this->lang['nobackup'] .'</option>';
	}
	else {
		foreach ($all_backups[$selectbox] as $items) {
			echo '<option value="'.$items[1].'">'.$this->time2date($items[1],true,true).'</option>';
		}
	}
	echo '</select>';
?> 
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="200"><?php echo $this->lang['imagedata'];?></td>
	<td class="setup_noborder">
<?php
	// IMAGE DATA
	$selectbox = 'idatabase';
	printf($select_head, 'media');
	if (empty($all_backups[$selectbox])) {
		echo '<option value="-1">'. $this->lang['nobackup'] .'</option>';
	}
	else {
		foreach ($all_backups[$selectbox] as $items) {
			echo '<option value="'.$items[1].'">'.$this->time2date($items[1],true,true).'</option>';
		}
	}
	echo '</select>';
?> 
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="200"><?php echo $this->lang['setup'];?></td>
	<td class="setup_noborder">
<?php
	// SETTINGS
	$selectbox = 'settings';
	printf($select_head, $selectbox);
	if (empty($all_backups[$selectbox])) {
		echo '<option value="-1">'. $this->lang['nobackup'] .'</option>';
	}
	else {
		foreach ($all_backups[$selectbox] as $items) {
			echo '<option value="'.$items[1].'">'.$this->time2date($items[1],true,true).'</option>';
		}
	}
	echo '</select>';
?> 
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="200"><?php echo $this->lang['counterdata'];?></td>
	<td class="setup_noborder">
<?php
	// COUNTER DATA
	$selectbox = 'counter';
	printf($select_head, $selectbox);
	if (empty($all_backups[$selectbox])) {
		echo '<option value="-1">'. $this->lang['nobackup'] .'</option>';
	}
	else {
		foreach ($all_backups[$selectbox] as $items) {
			echo '<option value="'.$items[1].'">'.$this->time2date($items[1],true,true).'</option>';
		}
	}
	echo '</select>';
?> 
	</td>
</tr>
<tr class="setupitem">
	<td class="setup" style="padding-top:3px;padding-bottom:4px;" width="200">&nbsp;</td>
	<td class="setup_bottom" style="padding-top:3px;padding-bottom:4px;">
		&nbsp;&nbsp;
		<img style="vertical-align:top;" src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" />
		<input type="submit" class="adminbutton" name="restBackup" value="<?php echo $this->lang['buttonrestore'];?>" alt="<?php echo $this->lang['ok'];?>" title="<?php echo $this->lang['ok'];?>" onclick="return confirmAction('restore_backup');" />
		<input type="submit" class="adminbutton" name="delBackup"  value="<?php echo $this->lang['buttondelete'];?>"  alt="<?php echo $this->lang['ok'];?>" title="<?php echo $this->lang['ok'];?>" onclick="return confirmAction('delete_backup');" />
		&nbsp;<i class="adminpicbutton"></i>
	</td>
</tr>
</form>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['counterimport'];?></td>
</tr>
<form action="<?php echo ADMIN_INDEX;?>" method="post" onSubmit="return confirmAction('click_import')">
<tr class="setupitem">
	<td class="setup" width="200"><?php echo $this->lang['counterdata'];?></td>
	<td class="setup_bottom">
		<?php if (is_file('database.txt')) { ?>
		<input type="hidden" name="action" value="convert" />
		<input type="hidden" name="item"  value="clickDB" />
		<input type="hidden" name="fID"   value="<?php echo $folderID;?>" />
		<input type="hidden" name="page"  value="<?php echo $page;?>" />
		<input type="hidden" name="display" value="setup" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
		<input type="submit" class="adminbutton" value=" <?php echo $this->lang['go'];?> " title="Import the counter plug-in database" />
		<?php } else { ?>
		<i><?php echo $this->lang['norootfile'];?></i></td>
		<?php } ?>
	</td>
</tr>
</form>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['dbimport'];?> MG2 0.5.0/0.5.1 <?php echo $this->lang['or'];?> kh_mod 0.1.0</td>
</tr>
<form action="<?php echo ADMIN_INDEX;?>" method="post" onSubmit="return confirmAction('folder_import')">
<tr class="setupitem">
	<td class="setup_right" width="200">
		<?php printf('%s (<span title="%s">%d</span>)',
					$this->lang['folderdata'],
					$this->lang['records'],
					$num_folderRC
				);
		?>
	</td>
	<td class="setup_noborder">
		<?php if ($num_folderRC > 0) { ?>
		<input type="hidden" name="action" value="convert" />
		<input type="hidden" name="item"  value="fDB" />
		<input type="hidden" name="fID"   value="<?php echo $folderID;?>" />
		<input type="hidden" name="page"  value="<?php echo $page;?>" />
		<input type="hidden" name="display" value="setup" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
		<input type="submit" class="adminbutton" value=" <?php echo $this->lang['go'];?> " title="Import MG2 0.5.0/0.5.1 or kh_mod 0.1.0 folder database" />
		<?php } else { ?>
		<i><?php echo $this->lang['norootfile'];?></i>
		<?php } ?>
	</td>
</tr>
</form>
<form action="<?php echo ADMIN_INDEX;?>" method="post" onSubmit="return confirmAction('image_import')">
<tr class="setupitem">
	<td class="setup_right" width="200">
		<?php printf('%s (<span title="%s">%d</span>)',
					$this->lang['imagedata'],
					$this->lang['records'],
					$num_imageRC
				);
		?>
	</td>
	<td class="setup_noborder">
		<?php if ($num_imageRC > 0) { ?>
		<input type="hidden" name="action" value="convert" />
		<input type="hidden" name="item"  value="iDB" />
		<input type="hidden" name="fID"   value="<?php echo $folderID;?>" />
		<input type="hidden" name="page"  value="<?php echo $page;?>" />
		<input type="hidden" name="display" value="setup" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
		<input type="submit" class="adminbutton" value=" <?php echo $this->lang['go'];?> " title="Import MG2 0.5.0/0.5.1 or kh_mod 0.1.0 image database" />
		<?php } else { ?>
		<i><?php echo $this->lang['norootfile'];?></i>
		<?php } ?>
	</td>
</tr>
</form>
<form action="<?php echo ADMIN_INDEX;?>" method="post" onSubmit="return confirmAction('convert')">
<tr class="setupitem">
	<td class="setup" width="200"><?php printf("%s (%s)", $this->lang['comments_convert'], $comments);?></td>
	<td class="setup_bottom">
		<?php if ($comments > 0) { ?>
		<input type="hidden" name="action" value="convert" />
		<input type="hidden" name="item"  value="cDB" />
		<input type="hidden" name="fID"   value="<?php echo $folderID;?>" />
		<input type="hidden" name="page"  value="<?php echo $page;?>" />
		<input type="hidden" name="display" value="setup" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
		<input type="submit" class="adminbutton" value=" <?php echo $this->lang['go'];?> " title="Convert MG2 0.5.0/0.5.1 or kh_mod 0.1.0 comment files" />
		<?php } else { ?>
		<i><?php printf("%s '%s'", $this->lang['nocommfiles'], $this->imagefolder);?></i>
		<?php } ?>
	</td>
</tr>
</form>
<form action="<?php echo ADMIN_INDEX;?>" method="post">
<tr>
	<td colspan="2" class="setup_noborder">
		<input type="hidden" name="fID" value="<?php echo $folderID;?>" />
		<input type="hidden" name="page" value="<?php echo $page;?>" />
		<input type="hidden" name="display" value="setup" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
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
		printf("\n".'<input type="image" src="%sreload.gif" class="adminpicbutton" alt="%2$s" title="%2$s" />',
			ADMIN_IMAGES,
			$this->lang['reload']
		);
?> 
		</div>
	</td>
</tr>
</form>
</table>
