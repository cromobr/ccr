<?php
// ****** START ADAPTING DATABASE *****
define('DB_NAME',		 'mg2_db');		// name of database
define('DB_USERNAME', 'root');		// enter database username
define('DB_PASSWORD', '');				// enter database passwort
define('DB_SERVER',	 'localhost');	// enter database server name
// ***** END ADAPTING DATABASE *******

// CONNECT TO DB SERVER
if (!$con = @mysql_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD)) {
	echo '<h2>MySQL-Fehler: '.mysql_error().'</h2>';
	exit;
}

// SELECT DATABASE
$database = @mysql_select_db(DB_NAME, $con);

?>