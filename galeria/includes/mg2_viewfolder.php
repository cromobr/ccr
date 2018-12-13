<?php
//////////////////////////////////////
//	DISPLAY FOLDER CONTENT	//
//////////////////////////////////////

	// GET CONTAINED FOLDERS AND IMAGES
	$folders  = $mg2->select($folderID, $mg2->all_folders, 1, $mg2->getFolderSortMode());	// kh_mod 0.3.0, changed
	$images   = $mg2->select($folderID, $mg2->all_images,  1, $mg2->images_sortmode);		// kh_mod 0.3.0, changed
	$cfolders = count($folders);																				// kh_mod 0.1.0, add
	$cimages  = count($images);																				// kh_mod 0.1.0, add

	// GET DATA FOR CATEGORIES RESP. THUMBNAILS VIEW
	if ($cfolders > 0 && $mg2->foldersetting & 1024) {
		$totalItems		= $cimages;
		$displayView	= INC_FOLDER .'mg2_categories.php';
		$mg2->headline = $mg2->gallerytitle;
	}
	else {
		$totalItems		= $cimages + $cfolders;
		$displayView	= INC_FOLDER .'mg2_thumbnails.php';
		$mg2->headline = ($folderID === 1)?
							  $mg2->gallerytitle
							  :
							  $mg2->br2line($mg2->all_folders[$folderID][2]);
	}

	// DISPLAY ADMIN MODE AND COUNTER LOCKED
	if (!empty($_SESSION[GALLERY_ID]['adminmode'])) {
		$mg2->lang['clicks'] = sprintf('<span title="%s">%s</span>',
											$mg2->lang['adminmode'],
											$mg2->lang['countlocked']
									  );
		$mg2->headline			= sprintf('%s - %s', $mg2->lang['adminmode'], $mg2->headline);
	}

	// STARTIMAGE (SLIDESHOW)
	$mg2->startimage = (!empty($cimages))?
							 $mg2->getGalleryLink(array('slideshow'=>$images[0][0], 'page'=>$currentPage))
							 :
							 '';

	// DISPLAY GALLERY HEADER
	include_once('skins/'.$mg2->activeskin.'/templates/gallery_header.php');

	// DISPLAY EMPTY FOLDER MESSAGE
	if ($cfolders === 0 && $cimages === 0) {
		include('skins/'.$mg2->activeskin.'/templates/emptyfolder.php');
		include('skins/'.$mg2->activeskin.'/templates/gallery_footer.php');
		exit();
	}

	// GET COLS AND ROWS PER PAGE
	$imagecols = ((int)$mg2->all_folders[$folderID][9] < 1)?
					 $mg2->imagecols									// global setting
					 :
					 (int)$mg2->all_folders[$folderID][9];		// folder setting
	$imagerows = ((int)$mg2->all_folders[$folderID][10] < 1)?
					 $mg2->imagerows									// global setting
					 :
					 (int)$mg2->all_folders[$folderID][10];	// folder setting

	//CALCULATE THUMBS PER PAGE AND NUMBER OF PAGES
	$imagecols = max($imagecols, 1);								// number of cols per page
	$imagerows = max($imagerows, 1);								// number of rows per page
	$numItems  = $imagecols * $imagerows;						// max number of items (thumbs) per page
	$numPages  = max(ceil(($totalItems)/$numItems), 1);	// number of total pages per folder

	// CALCULATE FIRST AND LAST INDEX OF PAGE 
	if ($currentPage === 'all') {
		$first = 0;
		$last  = $totalItems;
	}
	else {
		$currentPage = min($currentPage, $numPages);
		$first = $numItems * ($currentPage - 1);
		$last  = min($first + $numItems, $totalItems);
	}

	// VIEW FOLDER CONTENT
	include($displayView);

	// CREDITS - DO NOT REMOVE OR YOU WILL VOID MG2 TERMS OF USE!
	if ($folderID === 1) include(INC_FOLDER .'mg2_credits.php');

	include('skins/'.$mg2->activeskin.'/templates/viewthumbs_end.php');
	include('skins/'.$mg2->activeskin.'/templates/gallery_footer.php');
?>