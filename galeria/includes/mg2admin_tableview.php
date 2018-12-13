<?php
////////////////////////////////////////////////////////
//	DISPLAY FOLDER AND IMAGE TABLE (ADMIN)	//
////////////////////////////////////////////////////////

	// GET CURRENT PAGE AND IMAGE SUFFIX
	$currentPage = $page;
	$isuffix		 = '';
	if (isset($_GET['rotate'])) {
		$mg2->getMemoryStatus();
		$mg2->rotateImage((int)$_GET['rotate']);
		$callItems = $mg2->editID((int)$_GET['rotate'], $updatedComment);
		if (is_array($callItems)) list($currentPage, $isuffix) = $callItems;
	}
	elseif (isset($_REQUEST['editID'])) {
		$callItems = $mg2->editID((int)$_REQUEST['editID'], $updatedComment);
		if (is_array($callItems)) list($currentPage, $isuffix) = $callItems;
	}

	// GET FOLDER ID
	$folderID = (empty($_REQUEST['fID']))? 1:max((int)$_REQUEST['fID'], 1);
	if (!$mg2->getFolderSettings($folderID)) {
		$mg2->displaystatus($mg2->lang['nofolderid'] .' #'. $folderID, 3);
		if ($folderID !== 1) {
			$folderID = 1;
			$mg2->getFolderSettings($folderID);
		}
	}

	// GET CONTAINED FOLDERS AND IMAGES
	$folders  = $mg2->select($folderID, $mg2->all_folders,1, $mg2->getFolderSortMode());// kh_mod 0.3.0, changed
	$images   = $mg2->select($folderID, $mg2->all_images, 1, $mg2->images_sortmode);		// kh_mod 0.3.0, changed
	$cfolders = count($folders);																			// kh_mod 0.1.0, add
	$cimages  = count($images);																			// kh_mod 0.1.0, add

	// GET COLS AND ROWS PER PAGE
	$imagecols = ((int)$mg2->all_folders[$folderID][9] < 1)?
					 $mg2->imagecols									// global setting
					 :
					 (int)$mg2->all_folders[$folderID][9];		// folder setting
	$imagerows = ((int)$mg2->all_folders[$folderID][10] < 1)?
					 $mg2->imagerows									// global setting
					 :
					 (int)$mg2->all_folders[$folderID][10];	// folder setting

	//CALCULATE ICONS PER PAGE AND NUMBER OF PAGES
	$numicons = max($imagecols * $imagerows, 1);				// number of thumbs and folder icons per page
	$numpages = max(ceil($cimages / $numicons), 1);			// number of pages

	// CALCULATE FIRST AND LAST INDEX OF PAGE
	if ($currentPage === 'all') {
		$first = 0;
		$last  = $cimages;
	}
	else {
		$currentPage = min($currentPage, $numpages);
		$first = $numicons * ($currentPage - 1);
		$last  = min($first + $numicons, $cimages);
	}

	// DISPLAY TABLE HEADER
	$tableHead = '';
	if ($mg2->folder_position < 0) {				// folder locked?
		$tableHead = ' bgcolor="#FFCFCF" title="'. $mg2->lang['nodisplay'];
		$tableHead.= ' ('. $mg2->lang['position'] .' '. $mg2->folder_position .')"';
	}
	elseif ($mg2->folder_publish > time()) {	// folder published not yet?
		$tableHead = ' bgcolor="#FFFF99" title="'. $mg2->lang['notpublished'];
		$tableHead.= ' '. $mg2->time2date($mg2->folder_publish) .'"';
	}
	$navigation = sprintf('%s: %s : %d&nbsp;%s%s',
							$mg2->lang['navigation'],
							$mg2->adminnavigation($folderID),
							count($images),
							$mg2->lang['images'],
							$mg2->adminpagenavigation($folderID, $numpages, $currentPage)
					  );
	include(ADMIN_FOLDER .'admin_table_start.php');

	//
	// COUNT PICTURES INCL. ALL SUBFOLDERS
	// kh_mod 0.3.2, add
	$subfolders = $mg2->getValidSubfolders($folderID);		// get all sub folders
	$n_pictures	= $mg2->countFolderPictures($subfolders);	// count all pictures

	//
	// LIST FOLDERS
	// kh_mod 0.3.1, changed
	for ($i=0; $i < $cfolders; $i++) {
		$folder_flag = (trim($folders[$i][8]) != '')? 1:0;		// password
		$folder_flag|= ((int)$folders[$i][6] > 0)?	 2:0;		// thumb id
		// IS SET FOLDER PASSWORD AND FOLDER THUMBNAIL?
		if ($folder_flag === 3) {
			$small_icon = ADMIN_IMAGES .'folder_small_thumb_locked.gif';
		}
		// IS SET FOLDER PASSWORD?
		elseif ($folder_flag === 1) {
			$small_icon = ADMIN_IMAGES .'folder_small_locked.gif';
		}
		// IS SET FOLDER THUMBNAIL?
		elseif ($folder_flag === 2) {
			$small_icon = ADMIN_IMAGES .'folder_small_thumb.gif';
		}
		// STANDARD ICON
		else {
			$small_icon = ADMIN_IMAGES .'folder_small.gif';
		}

		// IS SET FOLDER THUMBNAIL?
		$folderThumb = (($thumbID = (int)$folders[$i][6]) > 0)?
							sprintf('&lt;img src=\\\'%s\\\' width=\\\'%d\\\' height=\\\'%d\\\' alt=\\\'\\\' /&gt;',
								$mg2->get_path($mg2->all_images[$thumbID][6], $mg2->all_images[$thumbID][7], 'thumb'),
								$mg2->all_images[$thumbID][10],
								$mg2->all_images[$thumbID][11]
							)
							:
							'';

		// GET PUBLISH DATE AND TIME OF FOLDER
		$publishdate = $mg2->time2date($folders[$i][4], false);
		$publishtime = (empty($mg2->timeformat))?
							''
							:
							$mg2->time2date($folders[$i][4]);

		// INCLUDE FOLDER RECORD TEMPLATE
		include(ADMIN_FOLDER .'admin3_folders.php');
	}

	//
	// LIST FILES
	// kh_mod 0.4.0 b3, changed
	for ($i=$first; $i < $last; $i++) {
		// COUNTER START BY 0
		$num = $i-$first;

		// GET IMAGE VALUES
		$imageID	  = $images[$i][0];
		$imagename = $images[$i][6];
		$subdir	  = $images[$i][7];
		$imagefile = $mg2->get_path($imagename, $subdir);
		$filesize  = $mg2->convertBytes((int)$images[$i][12], 1);

		// GET THUMB FILE
		list($thumbID, $thumbFile, $thumbWidth, $thumbHeight) = $mg2->getFileIcon($imageID);

		// ADD THUMB FILE SUFFIX
		$thumbFile .= $isuffix;

		// CALCULATE MINITHUMB SIZE, MINIMAL 5 PIXEL
		$miniThumbWidth  = max(round($thumbWidth/5,  0), 5);
		$miniThumbHeigth = max(round($thumbHeight/5, 0), 5);

		// GET PUBLISH DATE AND TIME OF FILE
		$publishdate = $mg2->time2date($images[$i][4], false);
		$publishtime = (empty($mg2->timeformat))?
							''
							:
							$mg2->time2date($images[$i][4]);

		// GET THUMB INFO
		$thumbInfo = '';
		if (($mg2->extendedset & 8) && $thumbID !== -1)
		if (include_once(INC_FOLDER .'mg2_exif.php')) {
			$thumbImage = '&lt;img src=\\\'%s\\\' width=\\\'%d\\\' height=\\\'%d\\\' alt=\\\'\\\' /&gt;';
			$thumbTitle = '';
			if ($imageID === $thumbID) {	// if image file
				$exifData = array();			// init resp. reset exifData, kh_mod 0.1.0 b3, add
				exif($imagefile);				// get exif-date
				$exifDate   = $mg2->exif2date($exifData['DTOpticalCapture']);
				$thumbTitle = '&lt;div style=\\\'white-space:nowrap;\\\'&gt;ID: #%d,&nbsp;Exif-%s: ';
				$thumbTitle.= ((strlen($exifDate) > 7)? '&lt;/div&gt;&lt;div&gt;':'') .'%s&lt;/div&gt;';
			}
			$thumbInfo = sprintf($thumbImage . $thumbTitle,
								$thumbFile,
								$thumbWidth,
								$thumbHeight,
								$imageID,
								$mg2->lang['date'],
								$exifDate
							 );
		}

		// GET NUMBER OF COMMENTS
		$Comments = $mg2->getInstance('MG2Comments');
		$comminfo = vsprintf('%d/%d', $mg2->$Comments->getNumComments($imageID));

		// INCLUDE ITEM RECORD TEMPLATE
		include(ADMIN_FOLDER .'admin3_files.php');
	}

	// DISPLAY CONTROLS
	$selectsize = $last - $first;
	if (empty($mg2->sortedfolders)) $mg2->makeFolderlist();
	include(ADMIN_FOLDER .'admin4_controls.php');
?>
