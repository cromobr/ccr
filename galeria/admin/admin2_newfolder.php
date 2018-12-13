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
-->
</script>
<form name="editfolder" action="<?php echo ADMIN_INDEX;?>" method="post">
<input type="hidden" name="fID" value="<?php echo $folderID;?>" />
<input type="hidden" name="page" value="<?php echo $page;?>" />
<input type="hidden" name="action" value="newfolder" />
<table class="table_actions" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
	<td class="headline" colspan="4"><?php echo $mg2->lang['newfolder'];?></td>
</tr>
<tr valign="top">
	<td class="td_actions_right"><?php echo $mg2->lang['presentation'];?></td>
	<td class="td_actions_right" width="100"><?php echo $mg2->lang['foldername'];?></td>
	<td class="td_actions_right">
		<input type="text" name="name" value="<?php echo $folder;?>" size="60" class="admintext" />
	</td>
	<td class="td_actions_right" rowspan="4">
		<table class="td_actions_noborder" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td class="td_actions_right" width="100">
<?php
					echo ($mg2->foldersetting & 15)?
						  $mg2->lang['sortby'] . '<sup>**</sup>'
						  :
						  $mg2->lang['sortby'];
?> 
				</td>
				<td class="td_actions_noborder">
					<select size="8" name="sortby" class="admindropdown">
						<option value="6" selected="selected"><?php echo $mg2->lang['name'];?></option>
						<option value="5"><?php echo $mg2->lang['position'];?></option>				<!-- kh_mod 0.1.0, add-->
						<option value="4"><?php echo $mg2->lang['date'];?></option>
						<option value="2"><?php echo $mg2->lang['title'];?><sup>*</sup></option>	<!-- kh_mod 0.1.0, add-->
						<option value="3"><?php echo $mg2->lang['description'];?></option>
						<option value="12"><?php echo $mg2->lang['filesize'];?><sup>*</sup></option>
						<option value="8"><?php echo $mg2->lang['width'];?><sup>*</sup></option>
						<option value="9"><?php echo $mg2->lang['height'];?><sup>*</sup></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="td_actions_right" width="100"><?php echo $mg2->lang['direction'];?></td>
				<td class="td_actions_noborder">
					<select size="2" name="direction" class="admindropdown">
						<option value="0" selected="selected"><?php echo $mg2->lang['ascending'];?></option>
						<option value="1"><?php echo $mg2->lang['descending'];?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="td_actions_noborder">
					<br />
<?php
					if ($mg2->foldersetting & 15) {
						if (($mg2->foldersetting & 15) === 6) $element = $mg2->lang['name'];
						if (($mg2->foldersetting & 15) === 5) $element = $mg2->lang['position'];
						if (($mg2->foldersetting & 15) === 4) $element = $mg2->lang['date'];
						if (($mg2->foldersetting & 15) === 3) $element = $mg2->lang['description'];
						printf('<sup>**</sup><a href="%s?display=setup&amp;fID=%d&amp;page=%s#thumbs">%s</a>: %s \'%s\'',
							ADMIN_INDEX,
							$folderID,
							$page,
							$mg2->lang['menutxt_setup'],
							$mg2->lang['foldersort'],
							$element
						);
					}
					else
						echo $mg2->lang['sortfolder'];
?> 
				</td>
			</tr>
		</table>
  </td>
</tr>
<tr>
	<td rowspan="4" class="td_files" style="border-bottom:0" width="170" align="center">
<?php
		// SETUP: FORCE FOLDER ICONS?
		if ($mg2->foldersetting & 16) {
			printf('<div style="%s"><a href="%s?display=setup&amp;fID=%d&amp;page=%s#thumbs">%s</a>: %s</div>',
				'margin-bottom:18px',
				ADMIN_INDEX,
				$folderID,
				$page,
				$mg2->lang['menutxt_setup'],
				$mg2->lang['foldericons']
			);
			$margin_bottom = '25px';
		}
		else {
			$margin_bottom = '12px';
		}
?> 
	<img src="<?php echo ADMIN_IMAGES;?>folder.gif" width="150" height="100" alt="">
	<div style="margin-top:12px;margin-bottom:<?php echo $margin_bottom;?>">
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="td_actions_noborder" align="left">
				<input type="radio" name="icon" id="defaulticon" value="" class="adminpicbutton" onclick="uncheckBox()" />
				<label for="defaulticon" style="vertical-align:middle;"><?php echo $mg2->lang['defaulticon'];?></label>
			</td>
		</tr>
		<tr>
			<td class="td_actions_noborder" align="left">
				<input type="radio" name="icon" id="folderimages" value="-1" class="adminpicbutton" checked="checked" />
				<select name="select_icon" size="1" class="admindropdown" onChange="checkRadio('folderimages')">
					<option value="1"><?php echo $mg2->lang['randomimage'];?></option>
					<option value="2"><?php echo $mg2->lang['firstimage'];?></option>
					<option value="4"><?php echo $mg2->lang['latestimage'];?></option>
				</select>
			</td>
		</tr><tr>
			<td class="td_actions_noborder" align="left">
				&nbsp;&nbsp;
				<img src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" />
				<input type="checkbox" id="incsubs" name="incsubs" value="1" style="vertical-align:top;" onclick="checkRadio('folderimages')" />
				<label for="incsubs" style="vertical-align:middle;"><?php echo $mg2->lang['incsubfolders'];?></label>
			</td>
		</tr>
	</table>
	</div>
	</td>
	<td class="td_actions_right">
<?php
	printf('<span title="%s">%s</span>',
		$mg2->lang['negative_pos'],
		$mg2->lang['position']
	);
?>	
	</td>
	<td class="td_actions_right">
		<input type="text" name="position" value="1" size="26" class="admintext" />
	</td>
</tr>
<tr>
	<td class="td_actions_right"><?php echo $mg2->lang['publish'];?></td>
	<td class="td_actions_right">
	<input type="text" name="publish" id="publish" value="<?php echo $mg2->time2date('', true);?>" size="26" class="admintext" autocomplete="off" />
<?php
		if (is_a($mg2->Calendar, 'MG2Calendar')) {
			echo '&nbsp;'.
			$mg2->Calendar->_make_calendar(
				// calendar options go here; see the documentation and/or calendar-setup.js
				array('date'			=> $mg2->time2date('', true),		// CALENDAR START DATE
						'ifFormat'		=> $mg2->dateformat .', %H:%M',	// CALENDAR FORMAT
						'inputField'	=> 'publish')							// INPUT FIELD ID
			);
		}
?> 
	</td>
</tr>
<tr>
	<td class="td_actions_right"><?php echo $mg2->lang['introtext'];?></td>
	<td class="td_actions_right"><table class="wysiwyg_editor" width="484"><tr><td>
		<textarea id="editor" cols="62" rows="11" name="introtext" class="admindropdown" style="padding:2px;width:480px;height:200px;"><?php echo $introtext;?></textarea>
   </td></tr></table></td>
</tr>
<tr>
	<td class="td_actions_right"><?php echo $mg2->lang['password'];?>
	<td class="td_actions_right">
		<input type="password" name="password" value="" size="26" class="admintext" autocomplete="off" />
	</td>
	<td class="td_actions" rowspan="2" style="vertical-align:middle;">
<?php
	// THUMBNAILS PER PAGE
	$globalsetting = sprintf('<a href="%s?display=setup&amp;fID=%d&amp;page=%s#thumbs">%d x %d</a>',
								ADMIN_INDEX,
								$folderID,
								$page,
								$mg2->imagecols,
								$mg2->imagerows
						  );
?> 
	<input type="radio" name="thumbgrid" id="globalthumbs" value="1" style="vertical-align:middle;" checked="checked" />
	<label for="globalthumbs" style="vertical-align:middle;"><?php echo $mg2->lang['globalthumbset'];?></label>
	<br />
	<input type="radio" name="thumbgrid" id="localthumbs" value="2" style="vertical-align:middle;" />
	<label for="localthumbs" style="vertical-align:middle;"><?php echo $mg2->lang['localthumbset'];?></label>
	<br />
	&nbsp;&nbsp;
	<img src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" />
	&nbsp;<?php echo $mg2->lang['cols'];?>:
	<input type="text" name="cols" value="" size="5" onClick="checkRadio('localthumbs')" />
	&nbsp;<?php echo $mg2->lang['rows'];?>:
	<input type="text" name="rows" value="" size="5" onClick="checkRadio('localthumbs')" />
  </td>
</tr>
<tr>
	<td class="td_actions">&nbsp;</td>
	<td class="td_actions"><?php echo $mg2->lang['confirm'];?></td>
	<td class="td_actions">
		<input type="password" name="confirm" value="" size="26" class="admintext" autocomplete="off" />
	</td>
</tr>
<tr>
	<td colspan="4" align="center" class="td_actions">
<?php
	// CANCEL BUTTON
	printf('<a href="%s?fID=%d&amp;page=%s"><img src="admin/images/cancelar.gif" width="47" height="22" alt="%5$s" title="%5$s" class="adminpicbutton" /></a>',
		ADMIN_INDEX,
		$folderID,
		$page,
		ADMIN_IMAGES,
		$mg2->lang['cancel']
	);
?> 
		<input type="image" src="<?php echo ADMIN_IMAGES;?>aplicar.gif" class="adminpicbutton" alt="<?php echo $mg2->lang['ok'];?>" title="<?php echo $mg2->lang['ok'];?>" />
	</td>
</tr>
</table>
</form>