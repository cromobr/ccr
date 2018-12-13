<?php	/* SETUP, DATABASE, SERVER, LOGFILE -  TAB MENU CONTROL, add kh_mod 0.3.0 */

	// TAB CONTROL
	$tab = (isset($_REQUEST['tab']))? (int)$_REQUEST['tab']:0;

	// TAB NAME
	$tab_array = array($this->lang['setup'],
							 $this->lang['data'],
/*							 'Skins',
							 'Comments',
							 'Layout',
							 'Thumbnails',
							 'Password',
*/							 $this->lang['stats'],
							 $this->lang['server'],
							 $this->lang['logfile']
							 );

	// INCLUDE FILE
	$inc_array = array(ADMIN_FOLDER .'setup/admin3_setup.inc.php',
							 ADMIN_FOLDER .'setup/admin1_data.inc.php',
/*							 'skins.inc.php',
							 'comments.inc.php',
							'layout.inc.php',
							 'thumbnails.inc.php',
							 'password.inc.php'
*/							 ADMIN_FOLDER .'setup/admin1_stats.inc.php',
							 ADMIN_FOLDER .'setup/admin1_server.inc.php',
							 ADMIN_FOLDER .'setup/admin1_logfile.inc.php'
							 );

	// EXCUTE COMMANDS
	$exc_array = array('setup',
							 'database',
/*							 'skins',
							 'comments',
							 'layout',
							 'thumbnails',
							 'password',
*/							 'stats',
							 'system',
							 'logfile'
							 );

	switch ($exc_array[$tab]) {
		case 'setup':
				// GET LANGUAGES
				$exists  = false;
				$lang		= array();
				$regexp	= '/^[a-z]{2}.[A-Z]{2}$/';
				$workdir = opendir(LANG_FOLDER);
				while (false !== ($pointer = readdir($workdir))) {
					if ($pointer{0} === '.')				 continue;
					if (!is_dir(LANG_FOLDER . $pointer)) continue;
					if (!preg_match($regexp, $pointer))	 continue;

					$value = 'value="'.$pointer.'"';
					if ($this->defaultlang === $pointer) {
						$value .= ' selected="selected"';
						$exists = true;
					}
					$lang[] = array($value, $pointer);
				}
				closedir($workdir);
				if (!$exists) $lang[] = array('value="" selected="selected"','--');
				sort($lang);

				// GET SKINS
				$exists  = false;
				$workdir = opendir('skins');
				while (false !== ($pointer = readdir($workdir))) {
					if ($pointer{0} === '.')			continue;
					if ($pointer 	 === '_global_')	continue;

					$value = 'value="'.$pointer.'"';
					if ($this->activeskin === $pointer) {
						$value .= ' selected="selected"';
						$exists = true;
					}
					$skins[] = array($value, ucfirst($pointer));
				}
				closedir($workdir);
				if (!$exists) $skins[] = array('value="" selected="selected"','--');
				sort($skins);

				// IMAGE MAP TOOLTIPT_ABOVE
				$mapInfo = sprintf(
									'&lt;div&gt;&lt;img src=\\\'%s\\\' width=\\\'%d\\\' height=\\\'%d\\\' alt=\\\'\\\' /&gt;'.
									'&lt;/div&gt;&lt;div style=\\\'position:absolute;top:3px;width:%2$dpx\\\'&gt;'.
									'&lt;div style=\\\'text-align:center;\\\'&gt;%4$s&lt;/div&gt;'.
									'&lt;div style=\\\'position:absolute;top:40px;\\\'&gt;&nbsp;%5$s&lt;/div&gt;'.
									'&lt;div style=\\\'position:absolute;top:40px;width:%2$dpx;text-align:right;\\\'&gt;%6$s'.
									'&nbsp;&lt;/div&gt;&lt;/div&gt;',
									ADMIN_IMAGES .'imagemap.gif',
									160,
									120,
									$this->lang['thumbsoverview'],
									$this->lang['prev'],
									$this->lang['next']
								);
				break;
		case 'database':
				// COUNT RECORDS
				$num_folderRC = $this->count_oldDB('mg2db_fdatabase.php');	// folder data
				$num_imageRC  = $this->count_oldDB('mg2db_idatabase.php');	// image data

				// GET BACKUP FILES
				$all_backups = array();
				$workdir = opendir(DATA_FOLDER);
				$regexp  = '/^([0-9]{10})_mg2db_([a-z]+)\.php$/i';
				while (false !== ($pointer = readdir($workdir))) {
					if ($pointer{0} === '.')							continue;
					if (!preg_match($regexp, $pointer, $items))	continue;

					switch ($items[2]) {
						case 'idatabase':
						case 'fdatabase':
						case 'settings':
						case 'counter':	$all_backups[$items[2]][] = $items;
					}
				}
				// SORT ALL BACKUPS DESCENDING
				foreach ($all_backups as $key => $backup) {
					$this->sort($backup,1,1);
					$all_backups[$key] = $backup;
				}
				break;
		case 'system':
				// GET CURRENT MEMORY STATUS
				$this->getMemoryStatus();
				break;
	}
?>
<div style="padding-top:5px">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td>
<?php
	$tab_link = '<a href="'.ADMIN_INDEX.'?display=setup&amp;tab=%s&amp;fID=%s&amp;page=%s">%s</a>';
	foreach ($tab_array as $key=>$tab_item) {
		if ($key === $tab) {
			$tab_class = 'tab_selected';
			$tab_text  = $tab_item;
		}
		else {
			$tab_class = 'tab_unselected';
			$tab_text  = sprintf($tab_link, $key, $folderID, $page, $tab_item);
		}
		printf('<div class="%s"><div class="tab_content">%s</div></div>'."\n", $tab_class, $tab_text);
	}
?>
			<div class="tab_blank">&nbsp;</div>
		</td>
	</tr>
	<tr>
		<td class="tab_main" colspan="<?php echo (1+count($tab_array));?>">
			<?php if (is_readable($inc_array[$tab])) include($inc_array[$tab]);?>
		</td>
	</tr>
</table>
</div>
