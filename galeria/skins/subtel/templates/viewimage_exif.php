<div class="exif">
  <b><?php echo $mg2->lang['exif_info'];?></b>
  <br />
  <br />
<?php
	if (isset($exifDisplay['Make']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['make'],
			$exifDisplay['Make']
		);
	if (isset($exifDisplay['Model']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['model'],
			$exifDisplay['Model']
		);
	if (isset($exifDisplay['ExposureTime']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['shutter'],
			$exifDisplay['ExposureTime']
		);
	if (isset($exifDisplay['ExposureBias']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['exposurecomp'],
			$exifDisplay['ExposureBias']
		);
	if (isset($exifDisplay['FNumber']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['aperture'],
			$exifDisplay['FNumber']
		);
	if (isset($exifDisplay['FocalLength']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['focallength'],
			$exifDisplay['FocalLength']
		);
	if (isset($exifDisplay['ISOSpeedRating']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['iso'],
			$exifDisplay['ISOSpeedRating']
		);
	if (isset($exifDisplay['Flash']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['flash'],
			$exifDisplay['Flash']
		);
	if (isset($exifDisplay['DTOpticalCapture']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['original'],
			$exifDisplay['DTOpticalCapture']
		);
	if (isset($exifDisplay['Software']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['software'],
			$exifDisplay['Software']
		);
	if (isset($exifDisplay['DateTime']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['datetime'],
			$exifDisplay['DateTime']
		);
	if (isset($exifDisplay['ColorSpace']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['colorspace'],
			$exifDisplay['ColorSpace']
		);
	if (isset($exifDisplay['Photographer']))
		printf('<span class="item"><strong>%s</strong> %s</span>'."\n",
			$mg2->lang['photographer'],
			$exifDisplay['Photographer']
		);
	if (isset($exifDisplay['GPS'])) {
		$position = sprintf('%s %s %s %s, <a target="_blank" href="http://maps.google.com/maps?f=q&amp;hl=%s&amp;q=%f,%f&amp;t=h">Google Maps</a>',
							$exifDisplay['GPS']['Latitude'],
							$exifDisplay['GPS']['LatitudeRef'],
							$exifDisplay['GPS']['Longitude'],
							$exifDisplay['GPS']['LongitudeRef'],
							substr($gallerylang, 0, 2),
							$exifDisplay['GPS']['DecLat'],
							$exifDisplay['GPS']['DecLong']
						);
		echo '
			<div style="margin:10px 0 4px 0;text-align:center;">
			<hr style="color:#909090;background-color:#909090;height:1px;border:0;" noshade="noshade" />
			<span class="item"><strong>Position</strong> '. $position .'</span>
			</div>
		';
	}
?>
</div>
