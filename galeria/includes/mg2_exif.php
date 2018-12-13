<?php

// holds the formatted data read from the EXIF data area
$exifData = array();

// holds the number format used in the EXIF data (1 == moto, 0 == intel)
$align = 0;

// holds the lengths and names of the data formats
$format_length = array(0, 1, 1, 2, 4, 8, 1, 1, 2, 4, 8, 4, 8);
$format_type = array("", "BYTE", "STRING", "USHORT", "ULONG", "URATIONAL", "SBYTE", "UNDEFINED", "SSHORT", "SLONG", "SRATIONAL", "SINGLE", "DOUBLE");

// data for EXIF enumeations
$Orientation = array("", "Normal (0 deg)", "Mirrored", "Upsidedown", "Upsidedown & Mirrored", "", "", "");
$ResUnit = array("", "inches", "inches", "cm", "mm", "um");
$YCbCrPos = array("", "Centre of Pixel Array", "Datum Points");
$ExpProg = array("", "Manual", "Program", "Apeture Priority", "Shutter Priority", "Program Creative", "Program Action", "Portrait", "Landscape");
$LightSource = array("Unknown", "Daylight", "Fluorescent", "Tungsten (incandescent)", "Flash", "Fine Weather", "Cloudy Weather", "Share", "Daylight Fluorescent", "Day White Fluorescent", "Cool White Fluorescent", "White Fluorescent", "Standard Light A", "Standard Light B", "Standard Light C", "D55", "D65", "D75", "D50", "ISO Studio Tungsten");
$MeterMode = array("Unknown", "Average", "Centre Weighted", "Spot", "Multi-Spot", "Pattern", "Partial");
$RenderingProcess = array("Normal Process", "Custom Process");
$ExposureMode = array("Auto", "Manual", "Auto Bracket");
$WhiteBalance = array("Auto", "Manual");
$SceneCaptureType = array("Standard", "Landscape", "Portrait", "Night Scene");
$GainControl = array("None", "Low Gain Up", "High Gain Up", "Low Gain Down", "High Gain Down");
$Contrast = array("Normal", "Soft", "Hard");
$Saturation = array("Normal", "Low Saturation", "High Saturation");
$Sharpness = array("Normal", "Soft", "Hard");
$SubjectDistanceRange = array("Unknown", "Macro", "Close View", "Distant View");
$FocalPlaneResUnit = array("", "inches", "inches", "cm", "mm", "um");
$SensingMethod = array("", "Not Defined", "One-chip Colour Area Sensor", "Two-chip Colour Area Sensor", "Three-chip Colour Area Sensor", "Colour Sequential Area Sensor", "Trilinear Sensor", "Colour Sequential Linear Sensor");

// gets one byte from the file at handle $fp and converts it to a number
function fgetord($fp) {
	return ord(@fgetc($fp));
}

// takes $data and pads it from the left so strlen($data) == $shouldbe
function pad($data, $shouldbe, $put) {
	if (strlen($data) == $shouldbe) {
		return $data;
	} else {
		$padding = "";
		for ($i = strlen($data);$i < $shouldbe;$i++) {
			$padding .= $put;
		}
		return $padding . $data;
	}
}

// converts a number from intel (little endian) to motorola (big endian format)
function ii2mm($intel) {
	$mm = "";
	for ($i = 0;$i <= strlen($intel);$i+=2) {
		$mm .= substr($intel, (strlen($intel) - $i), 2);
	}
	return $mm;
}

// gets a number from the EXIF data and converts if to the correct representation
function getnumber($data, $start, $length, $align) {
	$a = bin2hex(substr($data, $start, $length));
	if (!$align) {
		$a = ii2mm($a);
	}
	return hexdec($a);
}

// gets a rational number (num, denom) from the EXIF data and produces a decimal
function getrational($data, $align, $tag, $type) {
	$a = bin2hex($data);
	if (!$align) $a = ii2mm($a);			// Intel to Motorola
	if ($align == 1) {
		$n = hexdec(substr($a, 0, 8));
		$d = hexdec(substr($a, 8, 8));
	} else {
		$d = hexdec(substr($a, 0, 8));
		$n = hexdec(substr($a, 8, 8));
	}
	if ($type === 'S' && $n > 2147483647) {
		$n = $n - 4294967296;
	}
	if ($n == 0)		$data = 0;
	elseif ($d != 0)	$data = ($n / $d);
	else					$data = $n .'/'. $d;

	// *** kh_mod 0.1.0, add *** //
	if ($tag === 0x829a) {		// Exposure Time, kh_mod 0.2.0, changed
		if ($n >= $d)				$data = round($n/$d,1);
		elseif ($n > 10) {
			$precision = round(log10($d/$n),0) + 1;
			$data = round($n/$d,$precision);
			$data = convert2fraction($data);
		}
		elseif ($d % $n == 0)	$data = '1/'. (int)($d/$n);
		else							$data = $n .'/'. $d;
	}
	elseif ($tag === 0x920a) {	// FocalLength
		$data = round($data,1);
	}
	elseif ($tag === 0x9201) {	// Shutter Speed (APEX Mode)
		$data = exp($data * log(2));
		if ($data > 1) $data = round($data,0);
		if ($data > 0) $data = convert2fraction(1/$data);
		else				$data = '-';
	}
	elseif ($tag === 0x9202 || $tag === 0x9205) { // Aperature Values (APEX Mode)
		$data = exp(($data * log(2))/2);
		$data = round($data,1);
	}
	// *** end kh_mod *** //
	return $data;
}

// opens the JPEG file and attempts to find the EXIF data
// kh_mod 0.1.0, changed
function exif($file) {
	$fp = @fopen($file, 'rb');
	if (fgetord($fp) != 255 || fgetord($fp) != 216) {
		@fclose($fp);
		return false;
	}

	$result = false;
	while (!feof($fp)) {
		$section_length = 0;
		$section_marker = 0;
		$lh = 0;
		$ll = 0;
		for ($i = 0;$i < 7;$i++) {
			$section_marker = fgetord($fp);
			if ($section_marker != 255) break;
		}
		if ($section_marker == 255) return false;
		$lh = fgetord($fp);
		$ll = fgetord($fp);
		$section_length  = $lh << 8;
		$section_length |= $ll;
		if ($section_length < 8) return false;
		$t_data = fread($fp, $section_length - 2);
		if ($section_marker == 225) {
		   $result = extractEXIFData($t_data, $section_length);
			break;
		}
	}
	fclose($fp);
	return $result;
}

// reads the EXIF header and if it is intact it calls readEXIFDir to get the data
function extractEXIFData($data, $length) {
	global $align;
	if (substr($data, 0, 4) != "Exif") return false;
	if (substr($data, 6, 2) == "II") {
		$align = 0;
	}
	elseif (substr($data, 6, 2) == "MM") {
		$align = 1;
	}
	else return false;

	$a = getnumber($data, 8, 2, $align);
	if ($a != 0x2a) return false;

	$first_offset = getnumber($data, 10, 4, $align);
	if ($first_offset < 8 || $first_offset > 16) {
		return false;
	}
	readEXIFDir(substr($data, 14), 8, $length - 6);
	return true;
}

// takes an EXIF tag id and returns the string name of that tag
function tagid2name($id) {
	switch ($id) {
		case 0x0001: return "GPSLatitudeRef";			// MS add
		case 0x0002: return "GPSLatitude";				// MS add
		case 0x0003: return "GPSLongitudeRef";			// MS add
		case 0x0004: return "GPSLongitude";				// MS add
		case 0x000b: return "ACDComment";
		case 0x00fe: return "ImageType";
		case 0xa001: return "ColorSpace";				// kh_mod 0.2.0, add
		case 0x0106: return "PhotometicInterpret";
		case 0x010e: return "ImageDescription";
		case 0x010f: return "Make";
		case 0x0110: return "Model";
		case 0x0112: return "Orientation";
		case 0x0115: return "SamplesPerPixel";
		case 0x011a: return "XRes";
		case 0x011b: return "YRes";
		case 0x011c: return "PlanarConfig";
		case 0x0128: return "ResUnit";
		case 0x0131: return "Software";
		case 0x0132: return "DateTime";
		case 0x013b: return "Artist";
		case 0x013e: return "WhitePoint";				// kh_mod 0.2.0, changed
		case 0x0211: return "YCbCrCoefficients";
		case 0x0213: return "YCbCrPos";
		case 0x0214: return "RefBlackWhite";
		case 0x8298: return "Copyright";
		case 0x829a: return "ExposureTime";
		case 0x829d: return "FNumber";
		case 0x8822: return "ExpProg";
		case 0x8827: return "ISOSpeedRating";
		case 0x9003: return "DTOpticalCapture";
		case 0x9004: return "DTDigitised";
		case 0x9102: return "CompressedBitsPerPixel";
		case 0x9201: return "ShutterSpeed";
		case 0x9202: return "ApertureWidth";
		case 0x9203: return "Brightness";
		case 0x9204: return "ExposureBias";
		case 0x9205: return "MaxApertureWidth";		// kh_mod 0.1.0, changed
		case 0x9206: return "SubjectDistance";
		case 0x9207: return "MeterMode";
		case 0x9208: return "LightSource";
		case 0x9209: return "Flash";
		case 0x920a: return "FocalLength";
		case 0x9213: return "ImageHistory";
		case 0x927c: return "MakerNote";
		case 0x9286: return "UserComment";
		case 0x9290: return "SubsecTime";
		case 0x9291: return "SubsecTimeOrig";
		case 0x9292: return "SubsecTimeDigi";
		case 0xa000: return "FlashPixVersion";
		case 0xa001: return "ColourSpace";
		case 0xa002: return "ImageWidth";
		case 0xa003: return "ImageHeight";
		case 0xa20e: return "FocalPlaneXRes";
		case 0xa20f: return "FocalPlaneYRes";
		case 0xa210: return "FocalPlaneResUnit";
		case 0xa217: return "SensingMethod";
		case 0xa300: return "ImageSource";
		case 0xa301: return "SceneType";
		case 0xa401: return "RenderingProcess";
		case 0xa402: return "ExposureMode";
		case 0xa403: return "WhiteBalance";
		case 0xa404: return "DigitalZoomRatio";
		case 0xa405: return "FocalLength35mm";
		case 0xa406: return "SceneCaptureType";
		case 0xa407: return "GainControl";
		case 0xa408: return "Contrast";
		case 0xa409: return "Saturation";
		case 0xa40a: return "Sharpness";
		case 0xa40c: return "SubjectDistanceRange";
	}
}

// takes a (U/S)(SHORT/LONG) checks if an enumeration for this value exists and if it does returns the enumerated value for $tvalue
function enumvalue($tname, $tvalue) {
	// $tname:
	global $Orientation, $ResUnit, $YCbCrPos, $ExpProg, $MeterMode, $LightSource, $RenderingProcess, $ExposureMode, $WhiteBalance, $SceneCaptureType;
	global $GainControl, $Contrast, $Saturation, $Sharpness, $SubjectDistanceRange, $FocalPlaneResUnit, $SensingMethod;
	return (isset(${$tname}[$tvalue]))?	// kh_mod 0.4.0 b3, changed
			 ${$tname}[$tvalue]
			 :
			 $tvalue;
}

// takes the USHORT of the flash value, splits it up into itc component bits and returns the string it represents
// kh_mod 0.1.0, changed
function flashvalue($bin) {
	$retval = "";
	$bin = pad(decbin($bin), 8, "0");
	$flashfired = substr($bin, 7, 1);
	$returnd = substr($bin, 5, 2);
	$flashmode = substr($bin, 3, 2);
	$redeye = substr($bin, 1, 1);
	if ($flashfired == "1") {
		$retval = "Fired";
	} elseif ($flashfired == "0") {
		$retval = "Did not fire";
	}
	if ($returnd == "10") {
		$retval .= ", Strobe return light not detected";
	} elseif ($returnd == "11") {
		$retval .= ", Strobe return light detected";
	}
	if ($flashmode == "01" || $flashmode == "10") {
		$retval .= ", Compulsory mode";
	} elseif ($flashmode == "11") {
		$retval .= ", Auto mode";
	}
	if ($redeye) {
		$retval .= ", Red eye reduction";
	} else {
		$retval .= ", No red eye reduction";
	}
	return $retval;
}

// takes a tag id along with the format, data and length of the data and deals with it appropriatly
function dealwithtag($tag, $format, $data, $length, $align) {
	global $format_type, $exifData;
	$w = false;
	$val = "";
	switch ($format_type[$format]) {
		case "STRING":
			$val = trim(substr($data, 0, $length));
			$w = true;
			break;
		case "ULONG":
		case "SLONG":
			$val = enumvalue(tagid2name($tag), getnumber($data, 0, 4, $align));
			$w = true;
			break;
		case "USHORT":
		case "SSHORT":
			switch ($tag) {
				case 0x9209:
					$val = array(getnumber($data, 0, 2, $align), flashvalue(getnumber($data, 0, 2, $align)));
					$w = true;
					break;
				case 0x9214:
					
					break;
				case 0xa001:
					$tmp = getnumber($data, 0, 2, $align);
					if ($tmp == 1) {
						$val = "sRGB";
						$w = true;
					} else {
						$val = "Uncalibrated";
						$w = true;
					}
					break;
				default:
					$val = enumvalue(tagid2name($tag), getnumber($data, 0, 2, $align));
					$w = true;
					break;
			}
			break;
		case "URATIONAL":
			// *** GPS, MS add *** //
			switch ($tag) {
				case 0x0002:
				case 0x0004:
					$start = 0;
					if		 (($tag == 0x0002) && ($exifData['GPSLatitudeRef']  === "S")) $fortegn = -1;
					elseif (($tag == 0x0004) && ($exifData['GPSLongitudeRef'] === "W")) $fortegn = -1;
					else 	 $fortegn = 1;

					while ($length > 7) {
						$nextval = getrational(substr($data, $start, 8), $align, $tag, "U");

						switch ($start) {
							case  0: $deg_value = floor($nextval); $min_value = ($nextval - $deg_value)*60; break;
							case  8: $min_value = $min_value + floor($nextval); $sec_value = ($nextval - $min_value)*60; break;
							case 16: $sec_value = $sec_value + $nextval;  break;
						}
						$length -= 8;
						$start  += 8;
					}

					if ($tag == 0x0002) {
						$exifData['GPSDecLat'] = $fortegn * ($deg_value + $min_value/60 + $sec_value/3600);
					} else {
						$exifData['GPSDecLong'] = $fortegn * ($deg_value + $min_value/60 + $sec_value/3600); 
					}
					$val = $deg_value .'&deg; ' . $min_value .'&prime; '. round($sec_value,2) .'&Prime; ';
					break;
				// *** end GPS, MS add *** //
				default:
					$val = getrational(substr($data, 0, 8), $align, $tag, "U");	// kh_mod 0.1.0, changed
					break;
			}
			$w = true;
			break;
		case "SRATIONAL":
			$val = getrational(substr($data, 0, 8), $align, $tag, "S");			// kh_mod 0.1.0, changed
			$w = true;
			break;
		case "UNDEFINED":
			switch ($tag) {
				case 0xa300:
					$tmp = getnumber($data, 0, 2, $align);
					if ($tmp == 3) {
						$val = "Digital Camera";
						$w = true;
					} else {
						$val = "Unknown";
						$w = true;
					}
					break;
				case 0xa301:
					$tmp = getnumber($data, 0, 2, $align);
					if ($tmp == 3) {
						$val = "Directly Photographed";
						$w = true;
					} else {
						$val = "Unknown";
						$w = true;
					}
					break;
			}
			break;
	}

	if ($w) {
		$exifData[tagid2name($tag)] = $val;
	}
}

// reads the tags from and EXIF IFD and if correct deals with the data
function readEXIFDir($data, $offset_base, $exif_length) {
	global $format_length, $format_type, $align;
	$value_ptr = 0;
	$sofar = 2;
	$data_in = "";
	$number_dir_entries = getnumber($data, 0, 2, $align);
	for ($i = 0;$i < $number_dir_entries;$i++) {
		$sofar += 12;
		$dir_entry = substr($data, 2 + 12 * $i);
		$tag = getnumber($dir_entry, 0, 2, $align);
		$format = getnumber($dir_entry, 2, 2, $align);
		$components = getnumber($dir_entry, 4, 4, $align);
		if (($format - 1) >= 12) {
			return false;
		}
		$byte_count = $components * $format_length[$format];
		if ($byte_count > 4) {
			$offset_val = (getnumber($dir_entry, 8, 4, $align)) - $offset_base;
			if (($offset_val + $byte_count) > $exif_length) {
				return false;
			}
			$data_in = substr($data, $offset_val);
		} else {
			$data_in = substr($dir_entry, 8);
		}
		if ($tag === 0x8769 || $tag === 0x8825) {  // GPS Info, MS add
			$tmp = (getnumber($data_in, 0, 4, $align)) - 8;
			readEXIFDir(substr($data, $tmp), $tmp + 8 , $exif_length);
		} else {
			dealwithtag($tag, $format, $data_in, $byte_count, $align);
		}
	}
}

// convert a floating point number to fraction
// kh_mod 0.1.0, add
function convert2fraction($float) {
	$MaxTerms	  = 15;				// limit to avoid infinite loop
	$MinDivisor	  = 0.000001;		// limit to avoid divide by zero
	$MaxPrecision = 0.00000001;	// break-off condition

	$n_un   = 1;	// initialize fractions
	$d_un   = 0;	// with 1/0, 0/1
	$n_deux = 0;
	$d_deux = 1;

	$f = $float;	// start value
	for ($i=0; $i<$MaxTerms; $i++) {
		$a = floor($f);				// get next term
		$f = $f - $a;					// get new divisor
		$n = $n_un * $a + $n_deux; // calculate new fraction
		$d = $d_un * $a + $d_deux;
		$n_deux = $n_un;				// save last two fractions
		$d_deux = $d_un;
		$n_un   = $n;
		$d_un   = $d;
		$fPrecs = abs($float - ($n/$d));

		if ($fPrecs < $MaxPrecision)	break;
		if ($f < $MinDivisor)			break;

		$f = 1/$f;
	}
	return ($n >= 1 && $d === 1)? $n:$n .'/'. $d;
}
?>