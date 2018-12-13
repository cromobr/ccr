<?php
/////////////////////////////
// DISPLAY SLIDESHOW
/////////////////////////////

	$slideimages = $mg2->select($folderID, $mg2->all_images, 1, $mg2->images_sortmode);
	foreach($slideimages as $key=>$slide) {
		if ((int)$slide[0] === $imageID) {
			$nextid = (isset($slideimages[$key+1]))? (int)$slideimages[$key+1][0]:'';
			break;
		}
	}
	if (empty($nextid)) {
		$mg2->nextimage = 'skins/'. $mg2->activeskin .'/images/1x1.gif';	// dummy image
		$mg2->nexturl	 = $mg2->getGalleryLink(array('fID'=>$folderID, 'page'=>$currentPage));
	}
	// NEXT IMAGE
	else {
		$filename = $mg2->all_images[$nextid][6];
		$subdir	 = $mg2->all_images[$nextid][7];
		$mg2->nextimage = $mg2->get_path($filename,$subdir);					// preload next image
		$mg2->nexturl	 = $mg2->getGalleryLink(array('slideshow'=>$nextid,'page'=>$currentPage));
	}

	// GET IMAGE VALUES
	$filename	 = $imageRC[6];
	$subdir		 = $imageRC[7];
	$image_file	 = $mg2->get_path($filename, $subdir);
	$medium_file = $mg2->get_path($filename,  $subdir, 'medium');
	$image_total = ($key+1) .' '. $mg2->lang['of'] .' '. count($slideimages);				// image of total images
	$mg2->link   = $mg2->getGalleryLink(array('fID'=>$folderID, 'page'=>$currentPage));	// stop slideshow link
	$mg2->tooltip 		= $mg2->lang['next'];						// text on mouse pointer
	$mg2->description	= $imageRC[3];									// image description
	$mg2->title			= ($mg2->layoutsetting &  8)?				// filename as title?
							  $mg2->getFilename($imageID)
							  :
							  $mg2->br2line($imageRC[2]);
	$mg2->alt			= ($mg2->layoutsetting & 16)?				// title as alt tag?
							  $mg2->title
							  :
							  '';

	// CHECK CURRENT IMAGE
	$mg2->fullsizelink = '';
	do {
		if ($mg2->getContentType($imageID, 'image')) {
			if (is_readable($medium_file)) {
				list($mg2->width, $mg2->height, $mg2->type, $mg2->attr) = getimagesize($medium_file);
				$mg2->imagefile = $medium_file;
				$mg2->fullsizelink = '<a href="'. $image_file .'" target="_blank">'. $mg2->lang['fullsize'] .'</a>';
				break;
			}
			if (is_readable($image_file)) {
				$mg2->imagefile = $image_file;
				$mg2->width  = $imageRC[8];
				$mg2->height = $imageRC[9];
				break;
			}
		}
		// NO IMAGE TO DISPLAY
		$mg2->imagefile = 'skins/'. $mg2->activeskin .'/images/1x1.gif';
		$mg2->width  = '0';
		$mg2->height = '0';
		$mg2->copyright = 'Load image...';
		$mg2->title = '';
		$mg2->alt	= '';
		$mg2->description = '';
		$mg2->slideshowdelay = '0';
	}
	while(0);

	// PROTECT IMAGE WITH TRANSPARENT GIF
	$mg2->background = '';
	if ($mg2->layoutsetting & 4) {
		$mg2->background = 'background-image:url(\''.$mg2->imagefile.'\');';
		$mg2->imagefile  = 'skins/'.$mg2->activeskin.'/images/1x1.gif';
	}

	// DISPLAY IMAGE
	include('skins/'.$mg2->activeskin.'/templates/slideshow.php');
?>
