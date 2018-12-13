<?php
class MG2Calendar {
	var $calendar_lib_path;
	var $calendar_main_file;
	var $calendar_setup_file;
	var $calendar_lang_file;
	var $calendar_theme_file;
	var $calendar_button_title;

	function MG2Calendar($calPath = 'calendar/',
                        $lang		= 'en',
                        $theme	= 'calendar_mg2',
								$title	= 'Calendar') {

		$this->calendar_lib_path	  = preg_replace('/\/+$/', '/', $calPath);
		$this->calendar_lang_file	  = 'lang/calendar-'. $lang .'.js';
		$this->calendar_theme_file   = 'css/'. $theme .'.css';
		$this->calendar_main_file    = 'calendar.js';
		$this->calendar_setup_file	  = 'calendar-setup.js';
		$this->calendar_button_title = $title;

		// CHECK LANG FILE
		if (!is_readable($this->calendar_lib_path . $this->calendar_lang_file))
			$this->calendar_lang_file = 'lang/calendar-en.js';
    }

    function load_files() {
        echo $this->get_load_files_code();
    }

    function get_load_files_code() {
        $code  = '<link rel="stylesheet" type="text/css" media="all" href="'.
                 $this->calendar_lib_path . $this->calendar_theme_file .
                 '" />' ."\n";
        $code .= '<script type="text/javascript" src="'.
                 $this->calendar_lib_path . $this->calendar_main_file .
                 '"></script>' ."\n";
        $code .= '<script type="text/javascript" src="'.
                 $this->calendar_lib_path . $this->calendar_lang_file .
                 '"></script>' ."\n";
        $code .= '<script type="text/javascript" src="'.
                 $this->calendar_lib_path . $this->calendar_setup_file .
                 '"></script>' ."\n";
        return $code;
    }

	function _make_calendar($other_options = array()) {
		$triggerID = 'calendar-trigger'. rand(10000,99999);
		$button = '<img src="'. $this->calendar_lib_path .'img/calendar.gif"
					  width="16" height="17" title="' .
					  $this->calendar_button_title .'" alt="' .
					  $this->calendar_button_title .'" id="' .
					  $triggerID .'" class="adminpicbutton" />';
		$calendar_options	= array();
		$calendar_options['firstDay']		= 1;						// SHOW MONDAY FIRTS
		$calendar_options['showsTime']	= true;					// DISPLAYED A TIMER SELECTOR
		$calendar_options['step']			= 1;						// INCREMENT OF YEAR PULL DOWN
		$calendar_options['singleClick']	= false;					// DOUBLE-CLICK MODE
		$calendar_options['showOthers']	= true;					// SHOW DAYS OUTSIDE OF MONTH
		$calendar_options['timeFormat']	= '24';					// TIME FORMAT 12/24 HOURS
		$calendar_options['button']		= $triggerID;			// CALENDAR BUTTON ID
		$optionen = $this->_make_js_hash(array_merge($calendar_options, $other_options));
		$button.= '<script type="text/javascript">Calendar.setup({'.
						$optionen .
					 '});</script>';
		return $button;
	}

	// PRIVATE SECTION
	function _make_js_hash($array) {
		$jstr = '';
		reset($array);
		while (list($key, $val) = each($array)) {
			if (is_bool($val))
				$val = $val ? 'true' : 'false';
			else if (!is_numeric($val))
				$val = '"'.$val.'"';
			if ($jstr) $jstr .= ',';
			$jstr .= '"'. $key .'":'. $val;
		}
		return $jstr;
	}
};
?>