<table cellspacing="5" cellpadding="0" class="table_exif" width="300" align="center">
  <tr>
    <td colspan="2" align="center"><b><?php echo $mg2->lang['exif_info'];?></b></td>
  </tr>
<?php
	if (isset($exifDisplay['Make']))
		echo '<tr>
					<td>'. $mg2->lang['make'] .'</td>
					<td>'. $exifDisplay['Make'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['Model']))
		echo '<tr>
					<td>'. $mg2->lang['model'] .'</td>
					<td>'. $exifDisplay['Model'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['ExposureTime']))
		echo '<tr>
					<td>'. $mg2->lang['shutter'] .'</td>
					<td>'. $exifDisplay['ExposureTime'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['ExposureBias']))
		echo '<tr>
					<td>'. $mg2->lang['exposurecomp'] .'</td>
					<td>'. $exifDisplay['ExposureBias'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['FNumber']))
		echo '<tr>
					<td>'. $mg2->lang['aperture'] .'</td>
					<td>'. $exifDisplay['FNumber'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['FocalLength']))
		echo '<tr>
					<td>'. $mg2->lang['focallength'] .'</td>
					<td>'. $exifDisplay['FocalLength'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['ISOSpeedRating']))
		echo '<tr>
					<td>'. $mg2->lang['iso'] .'</td>
					<td>'. $exifDisplay['ISOSpeedRating'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['Flash']))
		echo '<tr>
					<td>'. $mg2->lang['flash'] .'</td>
					<td>'. $exifDisplay['Flash'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['DTOpticalCapture']))
		echo '<tr>
					<td>'. $mg2->lang['original'] .'</td>
					<td>'. $exifDisplay['DTOpticalCapture'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['Software']))
		echo '<tr>
					<td>'. $mg2->lang['software'] .'</td>
					<td>'. $exifDisplay['Software'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['Make']))
		echo '<tr>
					<td>'. $mg2->lang['datetime'] .'</td>
					<td>'. $exifDisplay['DateTime'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['ColorSpace']))
		echo '<tr>
					<td>'. $mg2->lang['colorspace'] .'</td>
					<td>'. $exifDisplay['ColorSpace'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['Photographer']))
		echo '<tr>
					<td>'. $mg2->lang['photographer'] .'</td>
					<td>'. $exifDisplay['Photographer'] .'</td>
				</tr>
		';
	if (isset($exifDisplay['GPS'])) {
		$position = sprintf('<span style="white-space:nowrap;">%s %s %s %s,</span> <a target="_blank" href="http://maps.google.com/maps?f=q&amp;hl=%s&amp;q=%f,%f&amp;t=h">Google Maps</a>',
							$exifDisplay['GPS']['Latitude'],
							$exifDisplay['GPS']['LatitudeRef'],
							$exifDisplay['GPS']['Longitude'],
							$exifDisplay['GPS']['LongitudeRef'],
							substr($gallerylang, 0, 2),
							$exifDisplay['GPS']['DecLat'],
							$exifDisplay['GPS']['DecLong']
						);
		echo '<tr>
					<td>Position</td>
					<td>'. $position .'</td>
				</tr>
		';
	}
?>
</table>
