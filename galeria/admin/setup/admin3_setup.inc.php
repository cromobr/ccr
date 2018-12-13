<script language="JavaScript" type="text/javascript">
<!--
	function checkRadio(ID) {
		if (!document.getElementById(ID).checked)
			document.getElementById(ID).checked = true;
	}
-->
</script>
<form action="<?php echo ADMIN_INDEX;?>" method="post">
<input type="hidden" name="action" value="writesetup" />
<input type="hidden" name="fID" value="<?php echo $folderID;?>" />
<input type="hidden" name="page" value="<?php echo $page;?>" />
<table lass="table_actions" cellpadding="0" cellspacing="0" style="width:100%">
<tr>
	<td colspan="2" class="actions_bottom">
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
		// OK BUTTON
		printf("\n".'<input type="image" src="%sok.gif" class="adminpicbutton" alt="%2$s" title="%2$s" />',
			ADMIN_IMAGES,
			$this->lang['ok']
		);
?> 
		</div>
	</td>
</tr>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['general'];?></td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['gallerytitle'];?></td>
	<td class="setup_noborder">
		<input type="text" name="gallerytitle" value="<?php echo htmlspecialchars($this->gallerytitle);?>" size="80" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['adminemail'];?></td>
	<td class="setup_noborder">
		<input type="text" name="adminemail" value="<?php echo $this->adminemail;?>" size="80" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['x-robots'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" style="vertical-align:middle;" name="http_index"	 id="http_index"		<?php if($this->metasetting & 1024) echo 'checked="checked"';?> value="1" />
		<label for="http_index" title="header('X-Robots-Tag: noindex')">noindex</label> |
		<input type="checkbox" style="vertical-align:middle;" name="http_follow"  id="http_follow"	<?php if($this->metasetting & 2048) echo 'checked="checked"';?> value="1" />
		<label for="http_follow" title="header('X-Robots-Tag: nofollow')">nofollow</label> |
		<input type="checkbox" style="vertical-align:middle;" name="http_archive" id="http_archive"	<?php if($this->metasetting & 4096) echo 'checked="checked"';?> value="1" />
		<label for="http_archive" title="header('X-Robots-Tag: noarchive')">noarchive</label> |
		<input type="checkbox" style="vertical-align:middle;" name="http_snippet" id="http_snippet"	<?php if($this->metasetting & 8192) echo 'checked="checked"';?> value="1" />
		<label for="http_snippet" title="header('X-Robots-Tag: nosnippet')">nosnippet</label>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php printf($this->lang['metatags'], 'Robots');?></td>
	<td class="setup_noborder">
		<input type="checkbox" style="vertical-align:middle;" name="meta_index" id="meta_index" <?php if($this->metasetting & 48) echo 'checked="checked"';?> value="1" />
		<select size="1" name="_index" class="admindropdown" onchange="checkRadio('meta_index')">
			<option value="1"<?php if($this->metasetting & 1<<4) echo ' selected="selected"';?> title="&lt;meta name=&quot;robots&quot; content=&quot;noindex&quot; /&gt;">noindex</option>
			<option value="2"<?php if($this->metasetting & 2<<4) echo ' selected="selected"';?> title="&lt;meta name=&quot;robots&quot; content=&quot;index&quot; /&gt;">index</option>
		</select>&nbsp;&nbsp;|
		<input type="checkbox" style="vertical-align:middle;" name="meta_follow" id="meta_follow" <?php if($this->metasetting & 192) echo 'checked="checked"';?> value="1" />
		<select size="1" name="_follow" class="admindropdown" onchange="checkRadio('meta_follow')">
			<option value="1"<?php if($this->metasetting & 1<<6) echo ' selected="selected"';?> title="&lt;meta name=&quot;robots&quot; content=&quot;nofollow&quot; /&gt;">nofollow</option>
			<option value="2"<?php if($this->metasetting & 2<<6) echo ' selected="selected"';?> title="&lt;meta name=&quot;robots&quot; content=&quot;follow&quot; /&gt;">follow</option>
		</select>&nbsp;&nbsp;|
		<input type="checkbox" style="vertical-align:middle;" name="meta_archive" id="meta_archive" <?php if($this->metasetting & 256) echo 'checked="checked"';?> value="1" />
		<label for="meta_archive" title="&lt;meta name=&quot;robots&quot; content=&quot;noarchive&quot; /&gt;">noarchive</label> |
		<input type="checkbox" style="vertical-align:middle;" name="meta_snippet" id="meta_snippet" <?php if($this->metasetting & 512) echo 'checked="checked"';?> value="1" />
		<label for="meta_snippet" title="&lt;meta name=&quot;googlebot&quot; content=&quot;nosnippet&quot; /&gt;">nosnippet</label>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php printf($this->lang['metatags'], 'Title');?></td>
	<td class="setup_noborder">
		<input type="checkbox" style="vertical-align:middle;" name="_gallery"	 id="_gallery"		<?php if($this->metasetting & 1) echo 'checked="checked"';?> value="1" />
		<label for="_gallery" title="&lt;meta name=&quot;title&quot; content=&quot;%gallerytitle%&quot; /&gt;"><?php echo $this->lang['gallerytitle'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_foldername" id="_foldername" <?php if($this->metasetting & 2) echo 'checked="checked"';?> value="1" />
		<label for="_foldername" title="&lt;meta name=&quot;title&quot; content=&quot;%foldername%&quot; /&gt;"><?php echo $this->lang['foldername'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_imagename"	 id="_imagename"	<?php if($this->metasetting & 4) echo 'checked="checked"';?> value="1" />
		<label for="_imagename" title="&lt;meta name=&quot;title&quot; content=&quot;%filename%&quot; /&gt;"><?php echo $this->lang['filename'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_imagetitle" id="_imagetitle"	<?php if($this->metasetting & 8) echo 'checked="checked"';?> value="1" />
		<label for="_imagetitle" title="&lt;meta name=&quot;title&quot; content=&quot;%imagetitle%&quot; /&gt;"><?php echo $this->lang['imagetitle'];?></label>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['language'];?></td>
	<td class="setup_noborder">
		<select size="1" name="defaultlang" class="admindropdown">
<?php
	// kh_mod 0.1.0 b3, changed
	for ($i=0; $i < count($lang); $i++){
		echo '<option '.$lang[$i][0].'>'.$lang[$i][1].'</option>';
	}
	// end
?>
    </select> <?php echo '&nbsp;('.$this->charset.')';?>
  </td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['skin'];?></td>
	<td class="setup_noborder">
		<select size="1" name="activeskin" class="admindropdown">
<?php
	// kh_mod 0.1.0 b3, changed
	for ($i=0; $i < count($skins); $i++) {
		echo '<option '.$skins[$i][0].'>'.$skins[$i][1].'</option>';
	}
	// end
?>
		</select>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['slideshowdelay'];?></td>
	<td class="setup_noborder">
		<input type="text" name="slideshowdelay" value="<?php echo $this->slideshowdelay;?>" size="5" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['dateformat'];?></td>
	<td class="setup_noborder">
		<select size="1" name="dateformat" class="admindropdown">
		<option value="1"<?php	if($this->dateformat === $this->getDateFormat(1))	echo ' selected="selected"';?>>DD.MM.YYYY (27.11.2009)</option>
		<option value="2"<?php	if($this->dateformat === $this->getDateFormat(2))	echo ' selected="selected"';?>>DD. MMM., YYYY (03. Nov., 2009)</option>
		<option value="3"<?php	if($this->dateformat === $this->getDateFormat(3))	echo ' selected="selected"';?>>DD. MMM. YYYY (03. Nov. 2009)</option>
		<option value="4"<?php	if($this->dateformat === $this->getDateFormat(4))	echo ' selected="selected"';?>>MMM. DD, YYYY (Nov. 03, 2009)</option>
		<option value="5"<?php	if($this->dateformat === $this->getDateFormat(5))	echo ' selected="selected"';?>>MMM. D, YYYY (Nov. 3, 2009)</option>
		<option value="6"<?php	if($this->dateformat === $this->getDateFormat(6))	echo ' selected="selected"';?>>D.MM.YY (3.11.08)</option>
		<option value="7"<?php	if($this->dateformat === $this->getDateFormat(7))	echo ' selected="selected"';?>>MM.D.YY (11.3.08)</option>
		<option value="8"<?php	if($this->dateformat === $this->getDateFormat(8))	echo ' selected="selected"';?>>YYMMDD (081127)</option>
		<option value="9"<?php	if($this->dateformat === $this->getDateFormat(9))	echo ' selected="selected"';?>>YYYYMMDD (20091127)</option>
		<option value="10"<?php	if($this->dateformat === $this->getDateFormat(10))	echo ' selected="selected"';?>>YYYY-MM-DD (2009-11-27)</option>
		<option value="11"<?php	if($this->dateformat === $this->getDateFormat(11))	echo ' selected="selected"';?>>DD-MM-YYYY (27-11-2009)</option>
		<option value="12"<?php	if($this->dateformat === $this->getDateFormat(12))	echo ' selected="selected"';?>>DD-MM-YYYY (11-27-2009)</option>
		<option value="13"<?php	if($this->dateformat === $this->getDateFormat(13))	echo ' selected="selected"';?>>DD-MMM-YYYY (27-Nov-2009)</option>
		<option value="14"<?php	if($this->dateformat === $this->getDateFormat(14))	echo ' selected="selected"';?>>MMM-DD-YYYY (Nov-27-2009)</option>
		<option value="15"<?php	if($this->dateformat === $this->getDateFormat(15))	echo ' selected="selected"';?>>DD/MM/YYYY (27/11/2009)</option>
		<option value="16"<?php	if($this->dateformat === $this->getDateFormat(16))	echo ' selected="selected"';?>>MM/DD/YYYY (11/27/2009)</option>
		<option value="17"<?php	if($this->dateformat === $this->getDateFormat(17))	echo ' selected="selected"';?>>D/M/YY (3/11/08)</option>
		<option value="18"<?php	if($this->dateformat === $this->getDateFormat(18))	echo ' selected="selected"';?>>M/D/YY (11/3/08)</option>
		<option value="19"<?php	if($this->dateformat === $this->getDateFormat(19))	echo ' selected="selected"';?>>DD/MMM/YYYY (27/Nov/2009)</option>
		<option value="20"<?php	if($this->dateformat === $this->getDateFormat(20))	echo ' selected="selected"';?>>MMM/DD/YYYY (Nov/27/2009)</option>
		<option value="21"<?php	if($this->dateformat === $this->getDateFormat(21))	echo ' selected="selected"';?>>DD/MM YYYY (27/03 2009)</option>
		<option value="22"<?php	if($this->dateformat === $this->getDateFormat(22))	echo ' selected="selected"';?>>MM/DD YYYY (03/27 2009)</option>
		<option value="23"<?php	if($this->dateformat === $this->getDateFormat(23))	echo ' selected="selected"';?>>DD/MMM YYYY (27/Nov 2009)</option>
		<option value="24"<?php	if($this->dateformat === $this->getDateFormat(24))	echo ' selected="selected"';?>>MMM/DD YYYY (Nov/27 2009)</option>
		<option value="25"<?php	if($this->dateformat === $this->getDateFormat(25))	echo ' selected="selected"';?>>M/D YYYY (11/3 2009)</option>
		<option value="26"<?php	if($this->dateformat === $this->getDateFormat(26))	echo ' selected="selected"';?>>D/M YYYY (3/11 2009)</option>
		<option value="27"<?php	if($this->dateformat === $this->getDateFormat(27))	echo ' selected="selected"';?>>D/MM YYYY (3/11 2009)</option>
		<option value="28"<?php	if($this->dateformat === $this->getDateFormat(28))	echo ' selected="selected"';?>>M/DD YYYY (11/03 2009)</option>
		</select>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['timeformat'];?></td>
	<td class="setup_noborder">
		<select size="1" name="timeformat" class="admindropdown">
			<option value=""> -- <?php echo $this->lang['nodisplay'];?> -- </option>
			<option value="1"<?php	if($this->timeformat === $this->getTimeFormat(1))	echo ' selected="selected"';?>>HH:MM (<?php echo $this->lang['range00to23'];?>)</option>
			<option value="2"<?php	if($this->timeformat === $this->getTimeFormat(2))	echo ' selected="selected"';?>>HH:MM:SS (<?php echo $this->lang['range00to23'];?>)</option>
			<option value="3"<?php	if($this->timeformat === $this->getTimeFormat(3))	echo ' selected="selected"';?>> H:MM (<?php echo $this->lang['range_1to12AM_PM'];?>)</option>
			<option value="4"<?php	if($this->timeformat === $this->getTimeFormat(4))	echo ' selected="selected"';?>> H:MM:SS (<?php echo $this->lang['range_1to12AM_PM'];?>)</option>
			<option value="5"<?php	if($this->timeformat === $this->getTimeFormat(5))	echo ' selected="selected"';?>> H:MM (<?php echo $this->lang['range_1to12am_pm'];?>)</option>
			<option value="6"<?php	if($this->timeformat === $this->getTimeFormat(6))	echo ' selected="selected"';?>> H:MM:SS (<?php echo $this->lang['range_1to12am_pm'];?>)</option>
			<option value="7"<?php	if($this->timeformat === $this->getTimeFormat(7))	echo ' selected="selected"';?>>HH:MM (<?php echo $this->lang['range01to12AM_PM'];?>)</option>
			<option value="8"<?php	if($this->timeformat === $this->getTimeFormat(8))	echo ' selected="selected"';?>>HH:MM:SS (<?php echo $this->lang['range01to12AM_PM'];?>)</option>
			<option value="9"<?php	if($this->timeformat === $this->getTimeFormat(9))	echo ' selected="selected"';?>>HH:MM (<?php echo $this->lang['range01to12am_pm'];?>)</option>
			<option value="10"<?php	if($this->timeformat === $this->getTimeFormat(10))	echo ' selected="selected"';?>>HH:MM:SS (<?php echo $this->lang['range01to12am_pm'];?>)</option>
		</select>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['formatfilenames'];?></td>
	<td class="setup_noborder">
		<select size="1" name="fileformat" class="admindropdown">
			<option value="0"><?php echo $this->lang['original'];?></option>
			<option value="1"<?php if($this->layoutsetting & 1<<6) echo ' selected="selected"';?>><?php echo $this->lang['orig1stcapital'];?></option>
			<option value="2"<?php if($this->layoutsetting & 2<<6) echo ' selected="selected"';?>><?php echo $this->lang['only1stcapital'];?></option>
			<option value="4"<?php if($this->layoutsetting & 4<<6) echo ' selected="selected"';?>><?php echo $this->lang['alluppercase'];?></option>
			<option value="8"<?php if($this->layoutsetting & 8<<6) echo ' selected="selected"';?>><?php echo $this->lang['alllowercase'];?></option>
		</select>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300">
		&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" style="vertical-align:top" />
		<?php echo $this->lang['showextension'];?>
	</td>
	<td class="setup_noborder">
		<input type="checkbox" name="withextion" <?php if($this->layoutsetting & 1<<10) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['websitelink'];?></td>
	<td class="setup_noborder">
		<input type="text" name="websitelink" value="<?php echo $this->websitelink;?>" size="80" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup" width="300"><?php echo $this->lang['websitetext'];?></td>
	<td class="setup_bottom">
		<input type="text" name="websitetext" value="<?php echo $this->websitetext;?>" size="80" class="admintext" />
	</td>
</tr>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><a name="thumbs" /><?php echo $this->lang['foldercontent'];?></td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['introwidth'] . $this->lang['disable'];?></td>
	<td class="setup_noborder">
		<?php if (strpos($this->introwidth, '%') > 0) $introselected=' selected="selected"'?>
		<input type="text" name="introwidth" value="<?php echo (int)$this->introwidth;?>" size="5" class="admintext" />
		<select size="1" name="introunit" class="admindropdown" style="vertical-align:top">
			<option value="px" style="padding:1px 5px 2px 0"><?php echo $this->lang['pixel'];?></option>
			<option value="%" style="padding:1px 5px 2px 0"<?php echo $introselected;?>><?php echo $this->lang['percent'];?></option>
		</select>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['foldersort'];?></td>
	<td class="setup_noborder">
	<select size="1" name="foldersort" class="admindropdown" style="vertical-align:top">
		<option value=""	style="padding:1px 5px 1px 0" <?php if(($this->foldersetting & 15) === 0)	echo 'selected="selected"';?>><?php echo $this->lang['foldersetup'];?></option>
		<option value="6"	style="padding:1px 5px 1px 0" <?php if(($this->foldersetting & 15) === 6)	echo 'selected="selected"';?>><?php echo $this->lang['name'];?></option>
		<option value="5"	style="padding:1px 5px 1px 0" <?php if(($this->foldersetting & 15) === 5)	echo 'selected="selected"';?>><?php echo $this->lang['position'];?></option>
		<option value="4"	style="padding:1px 5px 1px 0" <?php if(($this->foldersetting & 15) === 4)	echo 'selected="selected"';?>><?php echo $this->lang['date'];?></option>
		<option value="3"	style="padding:1px 5px 1px 0" <?php if(($this->foldersetting & 15) === 3)	echo 'selected="selected"';?>><?php echo $this->lang['description'];?></option>
	</select>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['foldericons'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="foldericons" <?php if($this->foldersetting & 16) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['displaycats'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="categories" <?php if($this->foldersetting & 1024) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300">
		&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>fork.gif" width="8" height="17" alt="" style="vertical-align:top" />
		<?php echo $this->lang['incfoldericon'];?>
	</td>
	<td class="setup_noborder">
		<input type="checkbox" name="categories_icon" <?php if($this->foldersetting & 2048) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<!--
- mit Passpartoue
- mit Schlagschatten
- Nur Thumbnail
<tr class="setupitem">
	<td class="setup_right" width="300">
		&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>pipe.gif" width="8" height="17" alt="" style="vertical-align:top" />
		&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" style="vertical-align:top" />
		<?php echo 'Folder icon with shadow';?>
	</td>
	<td class="setup_noborder">
		<input type="checkbox" name="categories_shad" <?php if($this->foldersetting & 4096) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
-->
<tr class="setupitem">
	<td class="setup_right" width="300">
		&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>fork.gif" width="8" height="17" alt="" style="vertical-align:top" />
		<?php echo $this->lang['incintrotext'];?>
	</td>
	<td class="setup_noborder">
		<?php $description = (int)$this->foldersetting >> 13 & 3;?>
		<input type="checkbox" style="vertical-align:middle;" name="categories_desc" <?php if($description) echo 'checked="checked"';?> value="1" />
		<select size="1" name="categories_align" class="admindropdown">
			<option value="1"<?php if($description === 1) echo ' selected="selected"';?>><?php echo $this->lang['original'];?></option>
			<option value="2"<?php if($description === 2) echo ' selected="selected"';?>><?php echo $this->lang['forcealignleft'];?></option>
			<option value="3"<?php if($description === 3) echo ' selected="selected"';?>><?php echo $this->lang['forcejustify'];?></option>
		</select>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300">
		&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" style="vertical-align:top" />
		<?php echo $this->lang['incsubfolders'];?>
	</td>
	<td class="setup_noborder">
		<input type="checkbox" name="categories_subs" <?php if($this->foldersetting & 32768) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['thumbmaxwidth'];?><br /></td>
	<td class="setup_noborder">
		<input type="text" name="thumbMaxWidth" value="<?php echo $this->thumbMaxWidth;?>" size="5" class="admintext" /><br />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['thumbmaxheight'];?><br /></td>
	<td class="setup_noborder">
		<input type="text" name="thumbMaxHeight" value="<?php echo $this->thumbMaxHeight;?>" size="5" class="admintext" /><br />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['thumbquality'];?><br />
	(for PNG pictures the compression level)
	</td>
	<td class="setup_noborder">
		<input type="text" name="thumbquality" value="<?php echo $this->thumbquality;?>" size="5" class="admintext" /><br />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['imagecols'];?></td>
	<td class="setup_noborder">
		<input type="text" name="imagecols" value="<?php echo $this->imagecols;?>" size="5" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['imagerows'];?></td>
	<td class="setup_noborder">
		<input type="text" name="imagerows" value="<?php echo $this->imagerows;?>" size="5" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['marknew'] . $this->lang['disable'];?></td>
	<td class="setup_noborder">
		<input type="text" name="marknew" value="<?php echo $this->marknew;?>" size="5" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['underthumbs'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" style="vertical-align:middle;" name="displayheadline" id="displayheadline"	<?php if($this->foldersetting & 32) echo 'checked="checked"';?> value="1" />
		<label for="displayheadline"><?php echo $this->lang['imagetitle'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="displayfile"	 id="displayfile"	<?php if($this->foldersetting & 64) echo 'checked="checked"';?> value="1" />
		<label for="displayfile"><?php echo $this->lang['filename'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="clickcounter"	 id="clickcounter"	<?php if($this->foldersetting & 128) echo 'checked="checked"';?> value="1" />
		<label for="clickcounter"><?php echo $this->lang['clickcounter'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="commentcounter" id="commentcounter"	<?php if($this->foldersetting & 256) echo 'checked="checked"';?> value="1" />
		<label for="commentcounter"><?php echo $this->lang['commentcounter'];?></label>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['thumbstooltip'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" style="vertical-align:middle;" name="thumbtooltip" <?php if($this->foldersetting & 512) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup" width="300"><?php echo $this->lang['justify_thumbs'];?><i>&nbsp;&nbsp;&nbsp;Does not work yet!</i></td>
	<td class="setup_bottom">
		<select size="1" name="justify_thumbs" class="admindropdown">
			<!--<option value="1"<?php if($justify_thumbs === 1) echo ' selected="selected"';?>><?php echo $this->lang['alignleft'];?></option>-->
			<!--<option value="2"<?php if($justify_thumbs === 2) echo ' selected="selected"';?>><?php echo $this->lang['alignright'];?></option>-->
			<option value="3"<?php if($justify_thumbs === 3) echo ' selected="selected"';?>><?php echo $this->lang['aligncenter'];?></option>
			<!--<option value="4"<?php if($justify_thumbs === 4) echo ' selected="selected"';?>><?php echo $this->lang['justify'];?></option>-->
			<!--<option value="5"<?php if($justify_thumbs === 5) echo ' selected="selected"';?>><?php echo $this->lang['flowting'];?></option>-->
		</select>		
	</td>
</tr>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><a name="layout" /><?php echo $this->lang['layout'];?></td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['imgwidth'] . $this->lang['disable'];?></td>
	<td class="setup_noborder">
		<?php if (strpos($this->mediumimage, '%') > 0) $mediumselected=' selected="selected"'?>
		<input type="text" name="mediumimage" value="<?php echo (int)$this->mediumimage;?>" size="5" class="admintext" />
<!--	
		<select size="1" name="mediumunit" class="admindropdown" style="vertical-align: top">
			<option value="px" style="padding:1px 7px 2px 0">Pixel</option>
			<option value="%" style="padding:1px 7px 2px 0"<?php echo $mediumselected;?>>Prozent</option>
		</select>
-->
	</td>
</tr>
<tr class="setupitem">
  <td class="setup_right" width="300"><?php echo $this->lang['navtype'];?></td>
  <td class="setup_noborder">
    <select size="1" name="navtype" class="admindropdown">
	 	<option value="0"<?php if($this->navtype===0) echo ' selected="selected"';?>><?php echo $this->lang['imgno'];?></option>
		<option value="1"<?php if($this->navtype===1) echo ' selected="selected"';?>><?php echo $this->lang['imgno'] .', '. $this->lang['text'];?></option>
		<option value="2"<?php if($this->navtype===2) echo ' selected="selected"';?>><?php echo $this->lang['imgno'] .', '. $this->lang['icons'];?></option>
		<option value="4"<?php if($this->navtype===4) echo ' selected="selected"';?>><?php echo $this->lang['thumbs'];?></option>
		<option value="5"<?php if($this->navtype===5) echo ' selected="selected"';?>><?php echo $this->lang['thumbs'] .', '. $this->lang['text'];?></option>
		<option value="6"<?php if($this->navtype===6) echo ' selected="selected"';?>><?php echo $this->lang['thumbs'] .', '. $this->lang['icons'];?></option>
		<!--<option value="6"<?php if($this->navtype===7) echo ' selected="selected"';?> style="text-decoration:line-through;background-color:red" title="Does not work yet!"><?php echo $this->lang['thumbs'] .', '. $this->lang['thumbs'];?></option>-->
		<!--<option value="6"<?php if($this->navtype===8) echo ' selected="selected"';?> style="text-decoration:line-through;background-color:red" title="Does not work yet!"><?php echo $this->lang['imgno'] .', '. $this->lang['thumbs'];?></option>-->
    </select>
  </td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['withmouseclick'];?></td>
	<td class="setup_noborder" style="padding-left:1px;">
		<?php $flagM = (int)$this->layoutsetting & 3; ?>
		<input type="radio" style="vertical-align:middle;" name="clickonimage" id="noaction"  <?php if($flagM===0) echo 'checked="checked"';?> value="0" />
		<label for="noaction" style="vertical-align:middle;"><?php echo $this->lang['noaction'];?></label> <span style="vertical-align:middle;">|</span>
		<input type="radio" style="vertical-align:middle;" name="clickonimage" id="overview"  <?php if($flagM===1) echo 'checked="checked"';?> value="1" />
		<label for="overview" style="vertical-align:middle;"><?php echo $this->lang['thumbsoverview'];?></label> <span style="vertical-align:middle;">|</span>
		<input type="radio" style="vertical-align:middle;" name="clickonimage" id="nextimage" <?php if($flagM===2)  echo 'checked="checked"';?> value="2" />
		<label for="nextimage" style="vertical-align:middle;"><?php echo $this->lang['nextimage'];?></label> <span style="vertical-align:middle;">|</span>
		<input type="radio" style="vertical-align:middle;" name="clickonimage" id="imagemap" <?php if($flagM===3)  echo 'checked="checked"';?> value="3" />
		<label for="imagemap" style="vertical-align:middle;"><span onmouseover="Tip('<?php echo $mapInfo;?>', FONTFACE, 'Arial, Helvetica, sans-serif')" onmouseout="UnTip()"><?php echo $this->lang['useimagemap'];?></span></label>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['usetransgif'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="transgif" <?php if($this->layoutsetting & 1<<2) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['fileashead'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="fileashead" <?php if($this->layoutsetting & 1<<3) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['headline4alt'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="altattrib" <?php if($this->layoutsetting & 1<<4) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['copyright'];?><br /></td>
	<td class="setup_noborder">
		<input type="text" name="copyright" value="<?php echo htmlspecialchars($this->copyright);?>" size="80" class="admintext" /><br />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup" width="300"><?php echo $this->lang['exif_info'];?></td>
	<td class="setup_bottom">
		<input type="checkbox" style="vertical-align:middle;" name="_make" id="_make" <?php if($this->showexif & 1) echo 'checked="checked"';?> value="1" />
		<label for="_make" title="Make"><?php echo $this->lang['make'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_model" id="_model" <?php if($this->showexif & 1<<1) echo 'checked="checked"';?> value="1" />
		<label for="_model" title="Model"><?php echo $this->lang['model'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_expotime" id="_expotime" <?php if($this->showexif & 1<<2) echo 'checked="checked"';?> value="1" />
		<label for="_expotime" title="ExposureTime"><?php echo $this->lang['shutter'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_expocomp" id="_expocomp" <?php if($this->showexif & 1<<3) echo 'checked="checked"';?> value="1" />
		<label for="_expocomp" title="ExposureBias"><?php echo $this->lang['exposurecomp'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_aperture" id="_aperture" <?php if($this->showexif & 1<<4) echo 'checked="checked"';?> value="1" />
		<label for="_aperture" title="FNumber"><?php echo $this->lang['aperture'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_focallen" id="_focallen" <?php if($this->showexif & 1<<5) echo 'checked="checked"';?> value="1" />
		<label for="_focallen" title="FocalLength"><?php echo $this->lang['focallength'];?></label>
		<br />
		<input type="checkbox" style="vertical-align:middle;" name="_iso" id="_iso" <?php if($this->showexif & 1<<6) echo 'checked="checked"';?> value="1" />
		<label for="_iso" title="ISOSpeedRating"><?php echo $this->lang['iso'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_flash" id="_flash" <?php if($this->showexif & 1<<7) echo 'checked="checked"';?> value="1" />
		<label for="_flash" title="Flash"><?php echo $this->lang['flash'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_original" id="_original" <?php if($this->showexif & 1<<8) echo 'checked="checked"';?> value="1" />
		<label for="_original" title="DTOpticalCapture"><?php echo $this->lang['original'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_software" id="_software" <?php if($this->showexif & 1<<9) echo 'checked="checked"';?> value="1" />
		<label for="_software" title="Software"><?php echo $this->lang['software'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_datetime" id="_datetime" <?php if($this->showexif & 1<<10) echo 'checked="checked"';?> value="1" />
		<label for="_datetime" title="DateTime"><?php echo $this->lang['datetime'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_colorspace" id="_colorspace" <?php if($this->showexif & 1<<11) echo 'checked="checked"';?> value="1" />
		<label for="_colorspace" title="ColorSpace"><?php echo $this->lang['colorspace'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_artist" id="_artist" <?php if($this->showexif & 1<<12) echo 'checked="checked"';?> value="1" />
		<label for="_artist" title="Artist or Copyright"><?php echo $this->lang['photographer'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="_gps" id="_gps" <?php if($this->showexif & 1<<13) echo 'checked="checked"';?> value="1" />
		<label for="_gps" title="GPS Data">GPS</label>
	</td>
</tr>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['comments'];?></td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['showcomments'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" style="vertical-align:middle;" name="showcomments" <?php if($this->commentsets & 1) echo 'checked="checked"';?> value="1" />
		<select size="1" name="commentmode" class="admindropdown">
			<option value="0"><?php echo $this->lang['ascending'];?></option>
			<option value="1"<?php if($this->commentsets & 2) echo ' selected="selected"';?>><?php echo $this->lang['descending'];?></option>
		</select>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['showmail'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="showmail" <?php if($this->commentsets & 4) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['allowcomments'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="allowcomments" <?php if($this->commentsets & 8) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300">
		&nbsp;&nbsp;
		<img src="<?php echo ADMIN_IMAGES;?>corner.gif" width="8" height="10" alt="" style="vertical-align:top" />
		<?php echo $this->lang['locknewcomments'];?>
	</td>
	<td class="setup_noborder">
		<input type="checkbox" name="lockcomments" <?php if($this->commentsets & 32) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['sendmail'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="sendmail" <?php if($this->commentsets & 64) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['hidecommform'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="hidecommform" <?php if($this->commentsets & 16) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['validate'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" style="vertical-align:middle;" name="jsvalidate" id="jsvalidate" <?php if($this->commentsets & 128) echo 'checked="checked"';?> value="1" />
		<label for="jsvalidate"><?php echo $this->lang['javascript'];?></label> |
		<input type="checkbox" style="vertical-align:middle;" name="cpvalidate" id="cpvalidate" <?php if($this->commentsets & 256) echo 'checked="checked"';?> value="1" />
		<label for="cpvalidate"><?php echo $this->lang['captcha'];?>*</label> |
		<input type="checkbox" style="vertical-align:middle;" name="whitelist" id="whitelist" <?php if($this->commentsets & 512) echo 'checked="checked"';?> value="1" />
		<label for="whitelist"><?php echo $this->lang['whitelist'];?>**</label> |
		<input type="checkbox" style="vertical-align:middle;" name="blacklist" id="blacklist" <?php if($this->commentsets & 1024) echo 'checked="checked"';?> value="1" />
		<label for="blacklist"><?php echo $this->lang['blacklist'];?>**</label>
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['logip'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="logip" <?php if($this->commentsets & 2048) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr>
	<td class="setup" colspan="2" style="border-right:0px">
		<?php printf('<sup>*</sup> '. $this->lang['worksonly'],
			'<a href="http://www.tangata.de/kh_mod/index.php?aktion=3ln=en#addon">Captcha</a>',
			'/skins/_global_/captcha/'
		);?>
		<br />
		<?php printf('<sup>**</sup>'. $this->lang['needsfile'],
			'\'whitelist.php\' resp. \'blacklist.php\'',
			'/skins/_global_/checklists/'
		);?>
	</td>
</tr>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['passwordchange'];?></td>
</tr>
<tr class="setupitem">
  <td class="setup_right" width="300"><?php echo $this->lang['oldpasswordsetup'];?></td>
  <td class="setup_noborder">
    <input type="password" name="oldpassword" value="" size="30" class="admintext" autocomplete="off" />
  </td>
</tr>
<tr class="setupitem">
  <td class="setup_right" width="300"><?php echo $this->lang['newpasswordsetup'];?></td>
  <td class="setup_noborder">
    <input type="password" name="password" value="" size="30" class="admintext" autocomplete="off" />
  </td>
</tr>
<tr class="setupitem">
  <td class="setup_right" width="300"><?php echo $this->lang['newpasswordsetupconfirm'];?></td>
  <td class="setup_noborder">
    <input type="password" name="passwordconfirm" value="" size="30" class="admintext" autocomplete="off" />
  </td>
</tr>
<tr class="setupitem">
  <td class="setup" width="300"><?php echo $this->lang['inactivetime'];?></td>
  <td class="setup_bottom">
    <input type="text" name="inactivetime" value="<?php echo $this->inactivetime;?>" size="5" class="admintext" />
  </td>
</tr>
<tr valign="top">
  <td class="headline" colspan="2" style="border-right:0px"><a name="advanced" /><?php echo $this->lang['advanced'];?></td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['indexfile'];?></td>
	<td class="setup_noborder">
		<input type="text" name="indexfile" value="<?php echo $this->indexfile;?>" size="30" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['imagesroot'];?></td>
	<td class="setup_noborder">
		<input type="text" name="imagefolder" value="<?php echo $this->imagefolder;?>" size="30" class="admintext" />
	</td>
</tr>
<tr  class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['allowedextensions'];?></td>
	<td class="setup_noborder">
		<input type="text" name="extensions" value="<?php echo str_replace(',',', ',$this->extensions);?>" size="30" class="admintext" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['pwdrecursiv'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="pwdrecursiv" <?php if(!($this->extendedset & 1)) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['samefolders'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="samefolders" <?php if($this->extendedset & 2) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['htmlarea'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="htmlarea" <?php if($this->extendedset & 4) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['tooltips'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="tooltips" <?php if($this->extendedset & 8) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['calendar'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="calendar" <?php if($this->extendedset & 16) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['useadminmode'];?></td>
	<td class="setup_noborder">
		<input type="checkbox" name="adminmode" <?php if($this->extendedset & 128) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300"><?php echo $this->lang['seolinks'];?>*</td>
	<td class="setup_noborder">
		<input type="checkbox" name="seolink" <?php if($this->extendedset & 32) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr class="setupitem">
	<td class="setup_right" width="300">
		<span title="<?php printf('setlocale(LC_CTYPE, %s.%s)', $this->defaultlang, $this->charset);?>"><?php echo $this->lang['setserverchars'];?></span>
	</td>
	<td class="setup_noborder">
		<input type="checkbox" name="ctype" <?php if($this->extendedset & 64) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
<tr>
	<td class="setup" colspan="2" style="border-right:0px">
		<?php printf('<sup>*</sup>'. $this->lang['needsfile'],
			'<a href="http://www.tangata.de/kh_mod/index.php?aktion=3ln=en#addon">.htaccess</a>',
			$this->lang['galleryroot']
		);?>
		</td>
</tr>
<!--
<tr class="setupitem">
	<td class="setup_right" width="300">Delete no more used temp files <i>Does not work yet!</i></td>
	<td class="setup_noborder">
		<input type="checkbox" name="tempfiles" <?php if($this->extendedset & 128) echo 'checked="checked"';?> value="1" />
	</td>
</tr>
-->
<tr>
	<td colspan="2" class="setup_noborder">
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
		// OK BUTTON
		printf("\n".'<input type="image" src="%sok.gif" class="adminpicbutton" alt="%2$s" title="%2$s" />',
			ADMIN_IMAGES,
			$this->lang['ok']
		);
?> 
		</div>
	</td>
</tr>
</table>
</form>
