<?php
	$gdInfo		 		= (function_exists('gd_info'))?
							  gd_info()
							  :
							  array('GD Version'=>' - ','FreeType Support'=>false);
	$gdVersion			= @preg_replace("/[a-z()\s]/i", "", $gdInfo['GD Version']);
	$gdFreeType			= ($gdInfo['FreeType Support'])?
							  'installed (Linkage '. $gdInfo['FreeType Linkage'] .')'
							  :
							  ' - ';
	$uploadFilesize	= ($cfg_var = ini_get('upload_max_filesize'))?
							  @preg_replace('/(\d+)M$/','${1} MBytes',$cfg_var)
							  :
							  '<i>'. $this->lang['noentry'] .'</i>';
	$postLimit			= ($cfg_var = ini_get('post_max_size'))?
							  @preg_replace('/(\d+)M$/','${1} MBytes', $cfg_var)
							  :
							  '<i>'. $this->lang['noentry'] .'</i>';
	$memoryLimit	 	= ($this->currentMemory['limit'] > 0)?
							  $this->convertBytes($this->currentMemory['limit'], 0)
							  :
							  '<i>'. $this->lang['noentry'] .'</i>';
	$memory_get_usage	= ($this->currentMemory['allocate'] > 0)?
							  $this->convertBytes($this->currentMemory['allocate'], 1)
							  :
							  '<i>'. $this->lang['unknown'] .'</i>';
	$maxInputTime		= ($cfg_var = ini_get('max_input_time'))?
							  $cfg_var .' sec.'
							  :
							  '<i>'. $this->lang['noentry'] .'</i>';
	$maxExecTime		= ($cfg_var = ini_get('max_execution_time'))?
							  $cfg_var .' sec.'
							  :
							  '<i>'. $this->lang['noentry'] .'</i>';
	$mysql_result		= @mysql_query('SELECT @@max_allowed_packet');
	$result_record		= @mysql_fetch_row($mysql_result);
	$maxMySQLPacket	= (isset($result_record[0]))?
							  $this->convertBytes((int)$result_record[0], 1)
							  :
							  '<i>'.$this->lang['unknown'].'</i>';
	$safe_mode			= ini_get('safe_mode'); if (!$safe_mode) $safe_mode = '<i>'. $this->lang['noentry'] .'</i>';
	$safe_mode_gid		= ini_get('safe_mode_gid'); if (!$safe_mode_gid) $safe_mode_gid = '<i>'. $this->lang['noentry'] .'</i>';
	$safe_mode_include_dir = ini_get('safe_mode_include_dir'); if (!$safe_mode_include_dir) $safe_mode_include_dir = '<i>'. $this->lang['noentry'] .'</i>';
	$safe_mode_exec_dir = ini_get('safe_mode_exec_dir'); if (!$safe_mode_exec_dir) $safe_mode_exec_dir = '<i>'. $this->lang['noentry'] .'</i>';
	$safe_mode_allowed_env_vars = ini_get('safe_mode_allowed_env_vars'); if (!$safe_mode_allowed_env_vars) $safe_mode_allowed_env_vars = '<i>'. $this->lang['noentry'] .'</i>';
	$safe_mode_protected_env_vars = ini_get('safe_mode_protected_env_vars'); if (!$safe_mode_protected_env_vars) $safe_mode_protected_env_vars = '<i>'. $this->lang['noentry'] .'</i>';
	$disable_functions = ini_get('disable_functions'); if (!$disable_functions) $disable_functions = '<i>'. $this->lang['noentry'] .'</i>';

	// ERROR REPORTING
	$errorReport = array();
	$errorValue	 = error_reporting();
	if ($errorValue & E_ERROR) $errorReport[] = "E_ERROR";
	if ($errorValue & E_WARNING) $errorReport[] = "E_WARNING";
	if ($errorValue & E_PARSE) $errorReport[] = "E_PARSE";
	if ($errorValue & E_NOTICE) $errorReport[] = "E_NOTICE";
	if ($errorValue & E_CORE_ERROR) $errorReport[] = "E_CORE_ERROR";
	if ($errorValue & E_CORE_WARNING) $errorReport[] = "E_CORE_WARNING";
	if ($errorValue & E_COMPILE_ERROR) $errorReport[] = "E_COMPILE_ERROR";
	if ($errorValue & E_COMPILE_WARNING) $errorReport[] = "E_COMPILE_WARNING";
	if ($errorValue & E_USER_ERROR) $errorReport[] = "E_USER_ERROR";
	if ($errorValue & E_USER_WARNING) $errorReport[] = "E_USER_WARNING";
	if ($errorValue & E_USER_NOTICE) $errorReport[] = "E_USER_NOTICE";
	if (($errorValue & E_ALL) === E_ALL) $errorReport[] = "E_ALL";
	if ($errorValue & E_STRICT) $errorReport[] = "E_STRICT";
	if (empty($errorReport)) $errorReport[] = sprintf('<i>%s</i>', $this->lang['noentry']);

	// DIRECTORY PERMISSION
	clearstatcache();
	$imagefolder = (is_dir($this->imagefolder))?
						sprintf('%03o', fileperms($this->imagefolder) & 1023)
						:
						'<i>'.$this->lang['dontexists'].'</i>';
	$datafolder  = (is_dir(DATA_FOLDER))?
						 sprintf('%03o', fileperms(DATA_FOLDER) & 1023)
						:
						'<i>'.$this->lang['dontexists'].'</i>';

	// FILE PERMISSIONS
	$files = array(
				  'mg2db_fdatabase' => 'php',
				  'mg2db_idatabase' => 'php',
				  'mg2db_settings'  => 'php',
				  'mg2db_counter'	  => 'php',
				  'mg2_log'			  => 'txt'
				);
	foreach ($files as $filename=>$extension) {
		$file_path = sprintf('%s%s.%s', DATA_FOLDER, $filename, $extension);
		if (is_file($file_path))
			$$filename = sprintf('%03o (%s)',
								fileperms($file_path) & 1023,
								$this->convertBytes(filesize($file_path))
							 );
		else
			$$filename = '<i>'.$this->lang['dontexists'].'</i>';
	}

	// CURRENT USER NAME
	$current_user = get_current_user(); if (!$current_user) $current_user = '<i>'. $this->lang['noentry'] .'</i>';
?>
<br />
<table lass="table_actions" cellpadding="0" cellspacing="0" style="width:100%">
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['generellsettings'];?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="phpversion()">PHP-Version:</td>
	<td class="setup_noborder"><?php echo phpversion();?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="gd_info()">GD-Version:</td>
	<td class="setup_noborder"><?php echo $gdVersion;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="gd_info()">FreeType Support:</td>
	<td class="setup_noborder"><?php echo $gdFreeType;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="upload_max_filesize">Max. allowed size for uploaded files:</td>
	<td class="setup_noborder"><?php echo $uploadFilesize;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="post_max_size">Max. size of POST data:</td>
	<td class="setup_noborder"><?php echo $postLimit;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="memory_limit"><?php echo $this->lang['memorylimit'];?></td>
	<td class="setup_noborder"><?php echo $memoryLimit;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="memory_get_usage()"><?php echo $this->lang['allocatedmemory'];?></td>
	<td class="setup_noborder"><?php echo $memory_get_usage;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="max_input_time">Max. Input Time:</td>
	<td class="setup_noborder"><?php echo $maxInputTime;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="max_execution_time">Max. Execution Time:</td>
	<td class="setup_noborder"><?php echo $maxExecTime;?></td>
</tr>
<tr>
	<td class="setup" width="200" title="max_allowed_packet">Max. allowed packet (MySQL):</td>
	<td class="setup_bottom"><?php echo $maxMySQLPacket;?></td>
</tr>
<tr valign="top">
  <td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['filepermissions'];?></td>
</tr>
<tr>
	<?php $root_dir = strrchr(substr(str_replace('\\', '/', DATA_FOLDER), 0, -6), '/');?>
	<td class="setup_right" width="200"><?php echo $root_dir, '/', $this->imagefolder;?></td>
	<td class="setup_noborder"><?php echo $imagefolder;?></td>
</tr>
<tr>
	<td class="setup_right" width="200"><?php echo $root_dir, '/data';?></td>
	<td class="setup_noborder"><?php echo $datafolder;?></td>
</tr>
<tr>
	<td class="setup_right" width="200">mg2db_fdatabase.php</td>
	<td class="setup_noborder"><?php echo $mg2db_fdatabase;?></td>
</tr>
<tr>
	<td class="setup_right" width="200">mg2db_idatabase.php</td>
	<td class="setup_noborder"><?php echo $mg2db_idatabase;?></td>
</tr>
<tr>
	<td class="setup_right" width="200">mg2db_settings.php</td>
	<td class="setup_noborder"><?php echo $mg2db_settings;?></td>
</tr>
<tr>
	<td class="setup_right" width="200">mg2db_counter.php</td>
	<td class="setup_noborder"><?php echo $mg2db_counter;?></td>
</tr>
<tr>
	<td class="setup" width="200">mg2_log.txt</td>
	<td class="setup_bottom"><?php echo $mg2_log;?></td>
</tr>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['safemodesettings'];?></td>
</tr>
<tr>
	<td class="setup_right" width="200">safe_mode:</td>
	<td class="setup_noborder"><?php echo $safe_mode;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="safe_mode_gid">safe_mode_gid:</td>
	<td class="setup_noborder"><?php echo $safe_mode_gid;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="safe_mode_include_dir">safe_mode_include_dir:</td>
	<td class="setup_noborder"><?php echo $safe_mode_include_dir;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="safe_mode_exec_dir">safe_mode_exec_dir:</td>
	<td class="setup_noborder"><?php echo $safe_mode_exec_dir;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="safe_mode_allowed_env_vars">safe_mode_allowed_env_vars:</td>
	<td class="setup_noborder"><?php echo $safe_mode_allowed_env_vars;?></td>
</tr>
<tr>
	<td class="setup" width="200" title="safe_mode_protected_env_vars">safe_mode_protected_env_vars:</td>
	<td class="setup_bottom"><?php echo $safe_mode_protected_env_vars;?></td>
</tr>
<tr valign="top">
	<td class="headline" colspan="2" style="border-right:0px"><?php echo $this->lang['miscellaneous'];?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="php_uname()">Server OS:</td>
	<td class="setup_noborder"><?php echo php_uname();?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="php_sapi_name()">Server API:</td>
	<td class="setup_noborder"><?php echo php_sapi_name();?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="get_current_user()">User name of this script:</td>
	<td class="setup_noborder"><?php echo $current_user;?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="getmyuid()">User ID of this script:</td>
	<td class="setup_noborder"><?php echo getmyuid();?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="getmygid()">Group ID of this script:</td>
	<td class="setup_noborder"><?php echo getmygid();?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="getmypid()">Prozess ID of this script:</td>
	<td class="setup_noborder"><?php echo getmypid();?></td>
</tr>
<tr>
	<td class="setup_right" width="200" title="disable_functions">disable_functions:</td>
	<td class="setup_noborder"><?php echo $disable_functions;?></td>
</tr>
<tr>
	<td class="setup" width="200" title="error_reporting()">error_reporting:</td>
	<td class="setup_bottom"><?php echo implode(', ',$errorReport);?></td>
</tr>
<tr>
	<td colspan="2" class="setup_noborder">
		<div align="center">
<?php
		// CANCEL BUTTON
		printf("\n".'<a href="%s?fID=%d&amp;page=%s"><img src="%scancel.gif" width="24" height="24" alt="%5$s" title="%5$s" class="adminpicbutton" /></a>',
			ADMIN_INDEX,
			$folderID,
			$page,
			ADMIN_IMAGES,
			$this->lang['cancel']
		);
		// RELOAD BUTTON
		printf("\n".'<a href="%s?display=setup&amp;tab=%d&amp;fID=%d&amp;page=%s"><img src="%sreload.gif" alt="%6$s" title="%6$s" class="adminpicbutton" /></a>',
			ADMIN_INDEX,
			$tab,
			$folderID,
			$page,
			ADMIN_IMAGES,
			$this->lang['reload']
		);
?> 
		</div>
	</td>
</tr>
</table>
