<?php
/////////////////////////////////////////////////////////
//	SORTING IMAGES PER DRAG 'N' DROP (ADMIN)    //
/////////////////////////////////////////////////////////

	// SET FOLDER ID
	$folderID = (empty($_REQUEST['fID']))? 1:(int)$_REQUEST['fID'];
	if (!$mg2->getFolderSettings($folderID)) {
		$mg2->displaystatus($mg2->lang['nofolderid'] .' #'. $folderID, 3);
		return false;
	}

	// GET IMAGE RECORDS
	$images = $mg2->select($folderID, $mg2->all_images, 1, $mg2->images_sortmode);

	// ONLY ONE IMAGE?
	if (($cimages = count($images)) < 2) {
		$mg2->displaystatus($mg2->lang['noimagestosort'], 1);
		return false;
	}

	// (RE)SET SORTING PARAMETERS
	$_SESSION[GALLERY_ID]['sortstart'] = true;	// sorting start true
	$_SESSION[GALLERY_ID]['sorting']   = false;	// sorting array empty

	// SORT TABLE HEAD
	if ($mg2->folder_position < 0) {
		$tableHead = sprintf(' bgcolor="#FFCFCF" title="%s (%s %d)"',
							 $mg2->lang['nodisplay'],
							 $mg2->lang['position'],
							 $mg2->folder_position
						 );
	}
	elseif ($mg2->folder_publish > time()) {
		$tableHead = sprintf(' bgcolor="#FFFF99" title="%s %s"',
							 $mg2->lang['notpublished'],
							 $mg2->time2date($mg2->folder_publish)
						 );
	}
	else {
		$tableHead = '';
	}
	$navigation = $mg2->lang['navigation'] .': '. $mg2->adminnavigation($folderID);
	$navigation.= ' : '. count($images) .'&nbsp;'. $mg2->lang['images'];
	$navigation.= $mg2->adminpagenavigation($folderID, $npages, $page);
	$class		= 'table_files';
?>
<!-- GET JAVASCRIPT LIBRARIES -->
<script src="admin/sorting/prototype.js" type="text/javascript"></script>
<script src="admin/sorting/scriptaculous.js" type="text/javascript"></script>

<!-- CSS FOR DRAG AND DROP -->
<style type="text/css">
<!--
	ul#sortlist li {
		height: <?php echo $mg2->thumbMaxHeight;?>px;
		float: left;
		list-style-type: none;
		margin: 2px;
		padding: 3px;
		font-size: 9pt;
		color: white;
		cursor: move;
	}
	ul#sortlist li.red {
		background: #FCA4A4;
	}
	ul#sortlist li.yellow {
		background: #EFEE73;
	}
	ul#sortlist li.green {
		background: lime;
	}
//-->
</style>
<span id="ajaxresult"></span>
<form name="fileform" action="<?php echo ADMIN_INDEX;?>" method="post">
<input type="hidden" name="action" value="savesorting" />
<input type="hidden" name="editfolder" value="<?php echo $folderID;?>" />
<input type="hidden" name="page"	value="<?php echo $page;?>" />
<table class="<?php echo $class;?>" cellpadding="0" cellspacing="0">
	<tr<?php echo $tableHead;?>>
		<td class="td_navigation">
<?php echo $navigation;
		// PASSWORD SET
		if (!empty($mg2->all_folders[$folderID][8]))
			printf('&nbsp;&nbsp;<img src="%slock.gif" width="15" height="15" alt="%2$s" title="%2$s" style="vertical-align:text-bottom" />',
				ADMIN_IMAGES,
				$mg2->lang['thissection']
			);
?> 
		</td>
	</tr>
	<tr><td class="td_actions_right" align="center">
		<div>
<?php
		// CANCEL BUTTON
		printf('<a href="%s?editfolder=%d&amp;page=%s"><img src="%scancel.gif" width="24" height="24" alt="%5$s" title="%5$s" class="adminpicbutton" /></a>'."\n",
			ADMIN_INDEX,
			$folderID,
			$page,
			ADMIN_IMAGES,
			$mg2->lang['cancel']
		);
		// OK BUTTON
		printf('<input type="image" src="%sok.gif" width="24" height="24" alt="%2$s" title="%2$s" class="adminpicbutton" />'."\n",
			ADMIN_IMAGES,
			$mg2->lang['setpositions']
		);
?>
		</div>
	</td></tr>
	<tr><td class="td_actions_right">
		<ul id="sortlist">

<?php
	// DISPLAY ALL THUMBNAILS OF FOLDER
	for ($i=0; $i < $cimages; $i++) {

		// GET IMAGE VALUES
		$imageID		  = $images[$i][0];
		$imagename	  = $images[$i][6];
		$subdir		  = $images[$i][7];
		$imagefile	  = $mg2->get_path($imagename, $subdir);

		// GET CONTENT TYPE
		$contentType = $mg2->getContentType($imageID);

		// GET THUMB FILE
		list($thumbID,
			  $thumbfile,
			  $thumbWidth,
			  $thumbHeight) = $mg2->getFileIcon($imageID, $contentType, true);

		// THUMBNAIL DISPLAY TYPE
		if ($images[$i][5] < 0) {
			$class = 'red';
		}
		elseif ($images[$i][4] > time()) {
			$class = 'yellow';
		}
		else {
			$class = 'green';
		}
		// DISPLAY THUMBNAIL ITEM
		printf('<li id="item_%d" class="%s">
					 <img src="%s" width="%d" height="%d" alt="%6$s" title="%6$s" />
				  </li>
				 ',
				 $imageID,
				 $class,
				 $thumbfile,
				 $thumbWidth,
				 $thumbHeight,
				 $imagefile
		);
	}

	// CREATE SORTING QUERY
	$sortquery = '&galleryid='. substr(GALLERY_ID, 3) .'&pwd='. $mg2->adminpwd;
?>
		</ul>
		<script type="text/javascript">
		<!--
			Sortable.create
			(
				'sortlist',
				{
					constraint: false,
					overlap: 'horizontal',
					scroll: window,
					onUpdate: function()
					{
						new Ajax.Updater
						(
							'ajaxresult', 'admin/sorting/savesorting.php',
							{ postBody: Sortable.serialize('sortlist') + '<?php echo $sortquery;?>'}
						);
					}
				}
			);
		//-->
		</script>
	</td></tr>

	<!-- DISPLAY BUTTONS -->
	<tr><td class="td_navigation" align="center">
		<div style="margin: 12px 0 12px 0">
<?php
		// CANCEL BUTTON
		printf('<a href="%s?editfolder=%d&amp;page=%s"><img src="%scancel.gif" width="24" height="24" alt="%5$s" title="%5$s" class="adminpicbutton" /></a>'."\n",
			ADMIN_INDEX,
			$folderID,
			$page,
			ADMIN_IMAGES,
			$mg2->lang['cancel']
		);
		// OK BUTTON
		printf('<input type="image" src="%sok.gif" width="24" height="24" alt="%2$s" title="%2$s" class="adminpicbutton" />'."\n",
			ADMIN_IMAGES,
			$mg2->lang['setpositions']
		);
?>
		</div>
	</td></tr>
</table>
</form>
