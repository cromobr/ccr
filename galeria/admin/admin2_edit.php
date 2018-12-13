<?php if (!isset($contentType['image'])) {

		// GET DEFAULT ICON
		list(	$defaultID,	$defaultIcon, $defaultWidth, $defaultHeight) = ($thumbID === 0)?
			array(0, $thumbfile, $thumbWidth, $thumbHeight)
			:
			$this->getFileIcon(0, $contentType);

		// GET VALUES FOR THE DEFAULT OPTION TAG
		$valuesOptionTags   = array();
		$valuesOptionTags[] = array('',									// css options
											 0,									// icon id
											 $defaultIcon,						// icon path
											 $defaultWidth,					// icon width
											 $defaultHeight,					// icon height
											 $defaultIcon,						// tooltip
											 '',									// selected attribute
											 $this->lang['defaulticon']	// option text
									 );

		// GET SPLASH DATA
		$Splashes = $this->getInstance('MG2Splashes');
		list(	$thumbID,
				$splashID,		
				$backgrID,
				$spColor,
				$bgColor) = $this->$Splashes->getSplashRecord($editID);

		// SET CURRENT THUMB, BG AND SPLASH PICTURE ON TOP
		$curr_time = time();
		if (isset($this->all_images[$thumbID])) {
			$this->all_images[$thumbID][15] = $curr_time;
		}
		if (isset($this->all_images[$splashID])) {
			$this->all_images[$splashID][15] = $curr_time - 1;
		}
		if (isset($this->all_images[$backgrID])) {
			$this->all_images[$backgrID][15] = $curr_time - 2;
		}

		// SELECT ALL BOOKMARKED IMAGES AND SORT BY DATE
		foreach($this->select(true, $this->all_images, 15, 31) as $markedRC) {

			// GET IMAGE AND ICON FILE PATH
			$filepath = $this->get_path($markedRC[6], $markedRC[7]);
			$iconpath = $this->get_path($markedRC[6], $markedRC[7], 'thumb');

			// IMAGE LOCKED?
			if ($markedRC[5] < 0) {
				$marked	= 'background-color:#FF9999;';
				$tooltip	= sprintf('%s: %s', $this->lang['nodisplay'], $filepath);
			}
			// IMAGE NOT YET PUBLISHED?
			elseif ($markedRC[4] > time()) {
				$marked	= 'background-color:#FFFF99;';
				$tooltip	= sprintf('%s %s; %s',
									$this->lang['notpublished'],
									$this->time2date($markedRC[4]),
									$filepath
							  );
			}
			else {
				$marked	= '';
				$tooltip	= $filepath;
			}

			// GET VALUES FOR OPTION TAGS
			$valuesOptionTags[] = array($marked,
												$markedRC[0],
												$iconpath,
												$markedRC[10],
												$markedRC[11],
												$tooltip,
												$thumbID === (int)$markedRC[0] ? 'selected="selected"':'',
												$this->mb_shorten($markedRC[6], 20)
										 );
		}

		// DIVISOR FOR THUMB SIZE
		$divisor = 5;
	}
?>
<script language="JavaScript" type="text/javascript">
<!--
var tt_width  = new Array();					// tooltip width
var tt_height = new Array();					// tooltip height
var selected  = new Array();					// selected items
var divisor   = <?php echo $divisor;?>;	// divisor for thumbs

function initIcon(domId) {
	icon					 = document.getElementById(domId);
	selected['domId']	 = domId;
	selected['src']	 = icon.src;
	selected['width']	 = icon.width;
	selected['height'] = icon.height;
}

function changeIcon(optionTag) {
	var optionValues = optionTag.value.split(';');
	icon.src	  = optionValues[1];
	var width  = optionValues[2];
	var height = optionValues[3];
	if (selected['domId'] == "fileicon") {
		icon.width  = width;
		icon.height = height;
	}
	else {
		icon.width	= Math.max(Math.round(width/divisor), 5);
		icon.height = Math.max(Math.round(height/divisor),5);
	}
}

function restoreIcon() {
	icon.src		= selected['src'];
	icon.width	= selected['width'];
	icon.height = selected['height'];
}

function setIcon(selectTag) {
	var optionValues = selectTag.options[selectTag.selectedIndex].value.split(';');
	selected['src']  = optionValues[1];
	if (selected['domId'] == "fileicon") {
		selected['width']  = optionValues[2];
		selected['height'] = optionValues[3];
	}
	else {
		selected['width']  = Math.max(Math.round(optionValues[2]/divisor),5);
		selected['height'] = Math.max(Math.round(optionValues[3]/divisor),5);
	}
	restoreIcon();

	tt_width[selected['domId']]  = parseInt(optionValues[2]);
	tt_height[selected['domId']] = parseInt(optionValues[3]);
}

function initTip(id, width, height) {
	if (typeof tt_width[id]  == 'number') width  = tt_width[id];
	if (typeof tt_height[id] == 'number') height = tt_height[id];
	var icon = document.getElementById(id);
	Tip("<img src='"+ icon.src +"' width='"+ width +"' height='"+ height +"' alt='' />");
}

function setColor(id, color) {
	document.getElementById(id).style.backgroundColor = color;
}
-->
</script>
<form name="editfolder" action="<?php echo ADMIN_INDEX;?>" method="post">
<input type="hidden" name="action" value="updateID" />
<input type="hidden" name="nextID" value="<?php echo $nextID;?>" />
<input type="hidden" name="iID" value="<?php echo $editID;?>" />
<input type="hidden" name="page" value="<?php echo $currentPage;?>" />
<table class="table_actions" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
	<td class="headline" colspan="2" width="310" style="border-right:0">
<?php
	$edititem = 'editimage';
	if		 (isset($contentType['video']))	$edititem = 'editvideo';
	elseif (isset($contentType['audio']))	$edititem = 'editaudio';
	echo $this->lang[$edititem];
?>
	</td>
	<td class="headline" colspan="2">
		<table class="minithumb" cellspacing="0" cellpadding="0">
			<tr>
				<td>&nbsp;<?php $this->output('nav_first');?>&nbsp;</td>
				<td>&nbsp;<?php $this->output('nav_prev');?>&nbsp;</td>
				<td>&nbsp;<?php $this->output('nav_this');?>&nbsp;</td>
				<td>&nbsp;<?php $this->output('nav_next');?>&nbsp;</td>
				<td>&nbsp;<?php $this->output('nav_last');?>&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>
<tr valign="top">
	<td class="td_actions_right" width="180"><?php echo $this->lang['itemicon'];?></td>
	<td class="td_actions_right" width="130"><?php echo $this->lang['filename'];?></td>
	<td class="td_actions_right">
		<input type="text" name="filename" value="<?php echo $filename;?>" size="80" class="admintext" />
	</td>
	<td class="td_actions" rowspan="7">&nbsp;</td>
</tr>
<tr>
	<td rowspan="5" class="td_actions_right" style="vertical-align:middle;" width="170" align="center">
		<a href="<?php echo $this->getGalleryLink(array('iID'=>$editID,'user'=>GALLERY_ID));?>" target="_blank">
		<img id="fileicon" src="<?php echo $thumbfile;?>" <?php echo $thumbsize;?> alt="<?php echo $this->lang['viewimage'];?>" title="<?php echo $this->lang['viewimage'];?>" class="thumb" /></a>
		<div style="margin:1em 0 4em 0;">
<?php
	// IF IMAGE CONTENT
	if (isset($contentType['image'])) {
		// IMAGE ROTATE BUTTONS (LEFT/RIGHT)
		$button = '<a href="%s?rotate=%d&amp;direction=%s">';
		$button.= '<img src="%s%s" width="24" height="24" alt="%6$s" title="%6$s" class="adminpicbutton" />';
		$button.= '</a>';
		printf("\n". $button,
			ADMIN_INDEX,
			$editID,
			'left',
			ADMIN_IMAGES,
			'rotateleft.gif',
			$this->lang['rotateleft']
		);
		printf("\n". $button,
			ADMIN_INDEX,
			$editID,
			'right',
			ADMIN_IMAGES,
			'rotateright.gif',
			$this->lang['rotateright']
		);
	}
?> 
		</div>
	</td>
	<td class="td_actions_right">
		<?php	printf('<span title="%s">%s</span>', $this->lang['negative_pos'],	$this->lang['position']);?>
	</td>
	<td class="td_actions_right">
		<input type="text" name="position" value="<?php echo $position;?>" size="26" class="admintext" />
	</td>
</tr>
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
<tr>
	<td class="td_actions_right"><?php echo $this->lang['title'];?></td>
	<td class="td_actions_right">
		<input type="text" name="title" value="<?php echo $title;?>" size="80" class="admintext" />
	</td>
</tr>
<tr>
	<td class="td_actions_right"><?php echo $this->lang['description'];?></td>
	<td class="td_actions_right">
		<div class="wysiwyg_editor" style="padding:3px;width:706px;">
			<textarea id="editor" name="description" cols="78" rows="10" class="admindropdown" style="padding:2px;width:700px;height:200px;"><?php echo $description;?></textarea>
		</div>
	</td>
</tr>
<tr>
</tr>
<?php if (!isset($contentType['image'])) { ?>
<tr>
	<td class="td_actions" align="center">
		<div style="margin: 0 auto 0 auto;">
		<select name="image4icon" size="1" class="admindropdown" onfocus="initIcon('fileicon')" onchange="setIcon(this)">
<?php
		$optionTag = '<option style="padding:1px 12px 1px 3px;%s" onmouseover="changeIcon(this)" onmouseout="restoreIcon()" value="%d;%s;%d;%d" title="%s" %s>%s</option>';
		foreach ($valuesOptionTags as $valuesOptionTag) {
			vprintf("\n". $optionTag, $valuesOptionTag);
		}
?> 
		</select>
		</div>
	</td>
	<td class="td_actions"><?php echo $this->lang['canvassize'];?></td>
	<td class="td_actions">
		<?php echo $this->lang['width'];?>:  <input name="canvas_width"  type="text" size="20" value="<?php echo (int)$imageRC[8];?>" />px
		&nbsp;&nbsp;
		<?php echo $this->lang['height'];?>: <input name="canvas_height" type="text" size="20" value="<?php echo (int)$imageRC[9];?>" />px
	</td>
</tr>
<tr>
	<td class="td_actions" colspan="4">
	<table cellpadding="0" cellspacing="0">
	<tr>
		<td class="headline" style="border-right:0" colspan="5"><?php echo $this->lang['flash_options'];?></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="headline" width="90">&nbsp;</td>
		<td class="headline" width="60"  align="center"><?php echo $this->lang['selection'];?></td>
		<td class="headline" width="110" align="center">&nbsp;</td>
		<td class="headline" width="60"  align="center"><?php echo $this->lang['selection'];?></td>
		<td class="headline" width="40"  align="center" title="<?php echo $this->lang['color_syntax'];?>"><?php echo $this->lang['color'];?></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="setup_right"><?php echo $this->lang['splash_image'];?></td>
		<td class="setup_right" align="center">
<?php
		// SPLASH SETTINGS, kh_mod 0.4.0 b3 changed
		if (isset($this->all_images[$splashID])																					&&
			 ($splashURL = $this->get_path($this->all_images[$splashID][6], $this->all_images[$splashID][7]))	&&
			 is_file($splashURL))
		{
			$icon_width  = $this->all_images[$splashID][10];
			$icon_height = $this->all_images[$splashID][11];
		}
		else
		{
			$splashURL   = ADMIN_IMAGES .'1x1.gif';
			$icon_width  = 1;
			$icon_height = 1;
		}
		printf('<img id="splashID" src="%s" width="%d" height="%d" alt="" onmouseover="initTip(\'splashID\', %4$d, %5$d)" onmouseout="UnTip()" />',
			$splashURL,
			max(round($icon_width/$divisor,  0), 5),
			max(round($icon_height/$divisor, 0), 5),
			$icon_width,
			$icon_height
		);
?>	
		</td>
		<td class="setup_right">
		<div style="margin: 0 auto 0 auto;">
		<select name="image4splash" size="1" class="admindropdown" onfocus="initIcon('splashID')" onchange="setIcon(this)">
<?php
		$valuesOptionTags[0] = array('', 0, ADMIN_IMAGES .'1x1.gif', 1, 1, '', '', ' -- '. $this->lang['noimage'] .' -- ');
		foreach ($valuesOptionTags as $valuesOptionTag) {
			$valuesOptionTag[6] = ($splashID === (int)$valuesOptionTag[1])? 'selected="selected"':'';
			vprintf("\n". $optionTag, $valuesOptionTag);
		}
?>
		</select>
		</div>
		</td>
		<td class="setup_right" align="center">
<?php
			printf('<div id="splashColorPad" style="width:%dpx;height:%dpx;background-color:%s;">&nbsp;</div>',
				max(round($this->thumbMaxWidth/$divisor,  0), 5),
				max(round($this->thumbMaxHeight/$divisor, 0), 5),
				$spColor
			);
?> 
		</td>
		<td class="setup_right">
			<input type="text" name="spColor" value="<?php echo $spColor;?>" onblur="setColor('splashColorPad', this.value)" class="admintext" />
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="setup"><?php echo $this->lang['background'];?></td>
		<td class="setup" align="center">
<?php
		// BACKGROUND SETTINGS
		if (isset($this->all_images[$backgrID])) {
			$flwp_bgURL  = $this->get_path($this->all_images[$backgrID][6], $this->all_images[$backgrID][7]);
			$icon_width  = $this->all_images[$backgrID][10];
			$icon_height = $this->all_images[$backgrID][11];
		}
		else {
			$flwp_bgURL  = ADMIN_IMAGES .'1x1.gif';
			$icon_width  = 1;
			$icon_height = 1;
		}
		printf('<img id="backgrID" src="%s" width="%d" height="%d" alt="" onmouseover="initTip(\'backgrID\', %4$d, %5$d)" onmouseout="UnTip()" />',
			$flwp_bgURL,
			max(round($icon_width/$divisor,  0), 5),
			max(round($icon_height/$divisor, 0), 5),
			$icon_width,
			$icon_height
		);
?>			
		</td>
		<td class="setup">
		<div style="margin: 0 auto 0 auto;">
		<select name="image4bg" size="1" class="admindropdown" onfocus="initIcon('backgrID')" onchange="setIcon(this)">
<?php
		foreach ($valuesOptionTags as $valuesOptionTag) {
			$valuesOptionTag[6] = ($backgrID === (int)$valuesOptionTag[1])? 'selected="selected"':'';
			vprintf("\n". $optionTag, $valuesOptionTag);
		}
?>	
		</select>
		</div>
		</td>
		<td class="setup" align="center">
<?php
			printf('<div id="bgColorPad" style="width:%dpx;height:%dpx;background-color:%s;">&nbsp;</div>',
				max(round($this->thumbMaxWidth/$divisor,  0), 5),
				max(round($this->thumbMaxHeight/$divisor, 0), 5),
				$bgColor
			);
?> 
		</td>
		<td class="setup">
			<input type="text" name="bgColor" value="<?php echo $bgColor;?>" onblur="setColor('bgColorPad', this.value)" class="admintext" />
		</td>
		<td>&nbsp;</td>
	</tr>
	</table
	></td>
</tr>
<?php } else { ?>
	<td class="td_actions"><?php echo $this->lang['setasthumb'];?></td>
	<td class="td_actions">
		<select size="1" name="setthumb" class="admindropdown">
<?php
		// LIST TO SET FOLDER ICON
		$option = '<option style="padding:1px 12px 1px 3px;%s" title="%s" value="%s">%s</option>'."\n";
		printf($option,
			'',
			'',
			'',
			'-- '. $this->lang['nofolderselected'] .' --'
		);
		foreach ($this->sortedfolders as $pathID=>$folderpath) {

			// ROOT FOLDER
			if ($pathID === 1) continue;

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

			// DISPLAY FOLDER PATH
			printf($option,
				implode('', $style),
				implode('; ', $title),
				$pathID,
				$folderpath[0]
			);
		}
?>
		</select>
	</td>
</tr>
<? } ?>
<tr>
	<td class="td_actions" colspan="4" align="center">
<?php
	// CANCEL BUTTON
	printf("\n".'<a href="%s?fID=%d&amp;page=%s"><img src="admin/images/cancelar.gif" width="47" height="22" alt="%5$s" title="%5$s" class="adminpicbutton" /></a>	',
		ADMIN_INDEX,
		$folderID,
		$currentPage,
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