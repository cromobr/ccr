<?php
/////////////////////////////
// DISPLAY ITEM
/////////////////////////////

	// CREATE THUMB NAVIGATION, USE SKIN SETTINGS
	$thumbset = array(
						'num' => (int)$skin_thumbnav_num,
						'div' => (float)$skin_thumbnav_size
					);
	list($currentPage,$prevID,$nextID) = $mg2->imagenavigation($imageID, $thumbset);

	// GET ITEM DATA
	$fileName	 = trim($imageRC[6]);
	$subdir		 = trim($imageRC[7]);
	$fileNameURL = rawurlencode($fileName);
	$imagePath	 = $mg2->get_path($fileName, $subdir);
	$mediumPath  = $mg2->get_path($fileName, $subdir, 'medium');
	$contentType = $mg2->getContentType($imageID);

	// CHECK ITEM
	$mg2->fullsizelink = '';
	if (is_readable($mediumPath)) {
		list($mg2->width, $mg2->height, $mg2->type, $mg2->attr) = getimagesize($mediumPath);
		$mg2->imagefile	 = $mg2->get_path($fileNameURL, $subdir, 'medium');
		$mg2->fullsizelink = sprintf('<a href="%s" target="_blank">%s</a>',
										$mg2->get_path($fileNameURL, $subdir),
										$mg2->lang['fullsize']
									);
	} elseif (is_readable($imagePath)) {
		$mg2->imagefile = $mg2->get_path($fileNameURL, $subdir);
		$mg2->width		 = $imageRC[8];
		$mg2->height	 = $imageRC[9];
	} elseif (is_file($imagePath)) {
		$mg2->imagefile = 'skins/'. $mg2->activeskin .'/images/1x1.gif';
		$mg2->width		 = $imageRC[8];
		$mg2->height	 = $imageRC[9];
		$mg2->status	 = $mg2->lang['filenotreadable'];
	} else {
		// Parameter: current imageID, current image ID, LAST currentPage
		// e.v. besser die lastID übergeben/übernehmen und daraus im Fehler-
		// fall (Datei fehlt) die $folderID und $currentPage generieren?
		notexists($imageID, $folderID, $currentPage);
	}

	// PROTECT IMAGE WITH TRANSPARENT GIF
	$mg2->background = '';
	if (($mg2->layoutsetting & 4) && isset($contentType['image'])) {
		$mg2->background = 'background-image:url('. $mg2->imagefile .');';
		$mg2->imagefile  = 'skins/'. $mg2->activeskin .'/images/1x1.gif';
	}

	// ALLOW TO ADD COMMENTS?
	if ($mg2->commentsets & 8) {
		$workon		  = array('','','','');									// empty comment fields
		$verify		  = $mg2->getVerificationToken(12);					// build new form token, used in 'comment_form.php'
		$hideCommForm = (isset($_GET['showform']))? false:true;		// display or hide form in 'comment_form.php'
		$tokens		  = (isset($_SESSION[GALLERY_ID]['formToks']))?	// get form tokens
							 $_SESSION[GALLERY_ID]['formToks']
							 :
							 array();

		// ADD NEW COMMENT?
		if (!empty($_POST['action']) && $_POST['action'] === 'addcomment') {

			// FORM VERIFICATION TOKEN CORRECT?
			if (isset($tokens[$imageID])										&&
				 (strlen($_POST['verify'])							 === 12)	&&
				 (strcmp($_POST['verify'], $tokens[$imageID]) ===  0))
			{
				$Comments = $mg2->getInstance('MG2Comments');
				if (($input = $mg2->$Comments->addNewComment($imageID)) !== true) {
					$workon = $input;						// last comment input
					$verify = $tokens[$imageID];		// unused token (needed for reload!)
					$hideCommForm = false;				// show comment form
				}
			}
		}

		// SET FORM TOKEN INTO SESSION
		$tokens[$imageID] = $verify;
		while (count($tokens) > 10) {		// delete more then 10 items
			reset($tokens);					// set pointer to the first item
			unset($tokens[key($tokens)]);	// delete the first item
		}
		$_SESSION[GALLERY_ID]['formToks'] = $tokens;
	}

	// *********************************************************************************************** //
	// ********************* CREATE IMAGE MAP AND CALCULATE COORDINATS ********************* //
	// *********************************************************************************************** //
	$areas = array();
	if ($flagM = (int)$mg2->layoutsetting & 3) {

		// IMAGE MAP - AREA TAG
		$area_tag = '<area shape="%s" coords="%s" href="%s" alt="%4$s" title="%4$s" />'."\n";

		// GENERATE NAVIGATION LINKS AND TITLES
		$link_back  = $mg2->getGalleryLink(array('fID'=>$folderID, 'page'=>$currentPage));
		$title_back = $mg2->lang['thumbsoverview'];
		if ($nextID > 0) {
			$link_next  = $mg2->getGalleryLink(array('iID'=>$nextID));
			$title_next = $mg2->lang['next'];
		} else {
			$link_next  = $link_back;
			$title_next = $title_back;
		}

		// IMAGE COORDINATS
		$left1 = 0;
		$left2 = $mg2->width/2;
		$left3 = $mg2->width-1;
		$top1  = 0;
		$top2  = $mg2->height/3;
		$top3  = $mg2->height-1;

		// CALL THUMB OVERVIEW WITH MOUSE CLICK
		if ($flagM === 1) {
			$coords_back = sprintf('%d,%d,%d,%d',
									$left1, $top1, $left3, $top3
								);
			$areas[] = sprintf($area_tag, 'rect', $coords_back, $link_back, $title_back);
		}

		// CALL NEXT IMAGE WITH MOUSE CLICK
		elseif ($flagM === 2) {
			$coords_next = sprintf('%d,%d,%d,%d',
									$left1, $top1, $left3, $top3
								);
			$areas[] = sprintf($area_tag, 'rect', $coords_next, $link_next, $title_next);
		}

		// CALL PREVIOUS, BACK, NEXT WITH MOUSE CLICK
		else {
			// EXISTS AREA TO PREVIOUS IMAGE?
			if ($prevID > 0) {
				$coords_prev = sprintf('%d,%d,%d,%d,%d,%d,%d,%d',
										$left1, $top1, $left2, $top2, $left2, $top3, $left1, $top3
									);
				$link_prev  = $mg2->getGalleryLink(array('iID'=>$prevID));
				$title_prev = $mg2->lang['prev'];
				$areas[] = sprintf($area_tag, 'poly', $coords_prev, $link_prev, $title_prev);
			}

			// AREA TO THUMBNAIL OVERVIEW
			$coords_back = sprintf('%d,%d,%d,%d,%d,%d',
									$left1+1, $top1, $left3-1, $top1, $left2+1, $top2-1
								);
			$areas[] = sprintf($area_tag, 'poly', $coords_back, $link_back, $title_back);

			// AREA TO NEXT IMAGE
			$coords_next = sprintf('%d,%d,%d,%d,%d,%d,%d,%d',
									$left3, $top1, $left3, $top3, $left2+1,$top3, $left2+1, $top2
								);
			$areas[] = sprintf($area_tag, 'poly', $coords_next, $link_next, $title_next);
		}
	}
	// ************************************* END OF IMAGE MAP ************************************* //

	// DISPLAY IMAGE
	$mg2->startimage  = $mg2->getGalleryLink(array('slideshow'=>$imageID, 'page'=>$currentPage));
	$mg2->description	= $imageRC[3];														// image description
	$mg2->headline		= ($mg2->layoutsetting &  8)?									// filename as headline?
							  $mg2->getFilename($imageID)
							  :
							  $mg2->br2line($imageRC[2]);
	$mg2->alt			= ($mg2->layoutsetting & 16)?									// title as alt tag?
							  htmlspecialchars($mg2->headline)
							  :
							  '';
	include('skins/'.$mg2->activeskin.'/templates/gallery_header.php');

	// IMAGE CONTENT
	if (isset($contentType['image']))
	{
		include('skins/'.$mg2->activeskin.'/templates/viewimage_begin.php');
	}
	// FLASH CONTENT
	elseif (isset($contentType['flash']))
	{
		// GET FLOWPLAYER PATH
		$flwp_javascript = ADDON_FOLDER .'flowplayer/flowplayer-3.1.4.min.js';
		$flwp_playerPath = ADDON_FOLDER .'flowplayer/flowplayer-3.1.5.swf';

		// GET SPLASH DATA
		$Splashes = $mg2->getInstance('MG2Splashes');
		list(	$thumbID,
				$splashID,		
				$backgrID,
				$splashColor,
				$flwp_bgColor) = $mg2->$Splashes->getSplashRecord($imageID);

		// GET SPLASH AND FLASH BACKGROUND URLs
		$splashURL	= 'none';	// CSS like
		$flwp_bgURL = '';			// for 'flowplayer'
		if (isset($mg2->all_images[$splashID])) {
			$splashName = rawurlencode(trim($mg2->all_images[$splashID][6]));
			$splashPath = trim($mg2->all_images[$splashID][7]);
			$splashURL  = $mg2->get_path($splashName, $splashPath);
		}
		if (isset($mg2->all_images[$backgrID])) {
			$backgrName = rawurlencode(trim($mg2->all_images[$backgrID][6]));
			$backgrPath = trim($mg2->all_images[$backgrID][7]);
			$flwp_bgURL = $mg2->get_path($backgrName, $backgrPath);
		}

		// DIRTY WORKAROUND
		if ($splashURL === 'none' && $splashColor === 'transparent') {
			$splashURL = $splashColor = false;
		}

		// GENERAL FLASH SETTINGS
		$flwp_repeat	= 'repeat';	// e.g.: 'no-repeat', 'repeat-x'
		$flwp_gradient	= 'none';	// e.g.: '[0.3, 0]', 'none', 'low', 'medium', 'high'
		$flwp_scaling	= 'fit';
		$flwp_opacity	= '0.65';

		// VIDEO SETTINGS
		if (isset($contentType['video'])) {
			$flwp_autoHide   = 'always';
			$flwp_fullscreen = 'true';
			$flwp_autoPlay	  = ($splashURL || $splashColor)? 'true':'false';
		}
		// AUDIO SETTINGS
		else {
			$flwp_autoHide   = 'never';
			$flwp_fullscreen = 'false';
			$flwp_autoPlay   = 'true';
		}
		include('skins/'.$mg2->activeskin.'/templates/viewflash_begin.php');
	}

	// DISPLAY EXIF
	if ((int)$mg2->showexif > 0 && is_readable(INC_FOLDER .'mg2_exif.php')) {
		if (include(INC_FOLDER .'mg2_exif.php')) exif($imagePath);

		// CHECK AND FORMAT EXIF DATA
		if (!empty($exifData)) {
			$missedExif  = 0;
			$exifDisplay = array();
			$empty = create_function('&$number', '$number++;return "&nbsp;&ndash;&nbsp;";');
			if ($mg2->showexif & 1)		{	// Make
				$exifDisplay['Make'] = (isset($exifData['Make']))?
					$exifData['Make']
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<1)	{	// Model
				$exifDisplay['Model'] = (isset($exifData['Model']))?
					$exifData['Model']
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<2)	{	// ExposureTime
				$exifDisplay['ExposureTime'] = (isset($exifData['ExposureTime']))?
					$exifData['ExposureTime'] . $mg2->lang['seconds']
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<3) {	// ExposureBias
				$exifDisplay['ExposureBias'] = (isset($exifData['ExposureBias']))?
					round($exifData['ExposureBias'],2)
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<4) {	// FNumber
				$exifDisplay['FNumber'] = (isset($exifData['FNumber']))?
					'f'. round($exifData['FNumber'], 2)
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<5) {	// FocalLength
				$exifDisplay['FocalLength'] = (isset($exifData['FocalLength']))?
					round($exifData['FocalLength'], 2) . $mg2->lang['mm']
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<6) {	// ISOSpeedRating
				$exifDisplay['ISOSpeedRating'] = (isset($exifData['ISOSpeedRating']))?
					$exifData['ISOSpeedRating']
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<7) {	// Flash
				$exifDisplay['Flash'] = (isset($exifData['Flash']))?
					$exifData['Flash'][1]
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<8) {	// DTOpticalCapture
				$exifDisplay['DTOpticalCapture'] = (isset($exifData['DTOpticalCapture']))?
					$mg2->exif2date($exifData['DTOpticalCapture'])
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<9)	{	// Software
				$exifDisplay['Software'] = (isset($exifData['Software']))?
					$exifData['Software']
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<10) {	// DateTime
				$exifDisplay['DateTime'] = (isset($exifData['DateTime']))?
					$mg2->exif2date($exifData['DateTime'])
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<11) {	// ColorSpace
				$exifDisplay['ColorSpace'] = (isset($exifData['ColorSpace']))?
					$exifData['ColorSpace']
					:
					$empty($missedExif);
			}
			if ($mg2->showexif & 1<<12) {	// Photographer
				if (!empty($imageRC[14]))
					$exifDisplay['Photographer'] = $imageRC[14];
				elseif (!empty($exifData['Artist']))
					$exifDisplay['Photographer'] = $exifData['Artist'];
				elseif (!empty($exifData['Copyright']))
					$exifDisplay['Photographer'] = $exifData['Copyright'];
				else
					$exifDisplay['Photographer'] = $empty($missedExif);
			}
			if ($mg2->showexif & 1<<13) {	// GPS
				if (isset($exifData['GPSLatitude'])) {
					$exifDisplay['GPS'] = array(
													'Latitude' 		=> $exifData['GPSLatitude'],
													'LatitudeRef'	=> $exifData['GPSLatitudeRef'],
													'Longitude'		=> $exifData['GPSLongitude'],
													'LongitudeRef'	=> $exifData['GPSLongitudeRef'],
													'DecLat'			=> $exifData['GPSDecLat'],
													'DecLong'		=> $exifData['GPSDecLong']
												 );
				}
			}

			// MISSED EXIF ENTRIES LESS THAN SELECTED?
			if ($missedExif < count($exifDisplay)) {
				include('skins/'.$mg2->activeskin.'/templates/viewimage_exif.php');
			}
		}
	}

	// DISPLAY AND/OR ADD COMMENTS?
	if ($mg2->commentsets & 9) {
		$Comments = $mg2->getInstance('MG2Comments');
		$mediumComments = $mg2->$Comments->getComments($imageID);
		$numberComments = count($mediumComments);

		// INCLUDE COMMENT TEMPLATES
		if ($mg2->commentsets & 1 && ($numberComments > 0))
			include('skins/'.$mg2->activeskin.'/templates/comment_view.php');
		if ($mg2->commentsets & 8)
			include('skins/'.$mg2->activeskin.'/templates/comment_form.php');
	}
	else $numberComments = -1;

	// SET CLICK COUNTER?
	$countClicks  = (($mg2->foldersetting & 128) && !isset($_REQUEST['showform']))?
						 1					// count mouse click
						 :
						 -1;				// don't count mouse click
	// SET COMMENT COUNTER?
	$countComment = (($mg2->foldersetting & 256) && $numberComments >= 0)?
						 $numberComments	// set number of comments
						 :
						 -1;					// don't set number of comments
	if ($countClicks !== -1 || $countComment !== -1) {
		$Counter = new MG2Counter($mg2->sqldatabase);
		if ($countClicks || $Counter->getNumComments($imageID) !== $countComment)
			$Counter->setDatabase($imageID, $countClicks, $countComment);
	}

	// END OF PAGE
	include('skins/'.$mg2->activeskin.'/templates/gallery_footer.php');
?>
