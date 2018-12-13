<?php
// IMAGE SORTING, AJAX - SERVER SIDE, kh_mod 0.3.0 add
session_start();

// GET GALLERY ID
$galleryid = 'mg2'. (int)$_POST['galleryid'];

// ACCESS AUTHORITY
if (empty($_SESSION[$galleryid]))								exit();	// gallery inactive?
if (strlen($_SESSION[$galleryid]['adminpwd']) !== 32)		exit();	// isn't set password?
if ($_SESSION[$galleryid]['adminpwd'] !== $_POST['pwd'])	exit();	// password wrong?

// GALLERY ACCESSTIME EXPIRED
if ($_SESSION[$galleryid]['accesstime'] + $_SESSION[$galleryid]['inactivetime'] < time()) {
	unset($_SESSION[$galleryid]);
	exit();
}

// SET NEW SORTING ARRAY
if ($_SESSION[$galleryid]['sortstart'] === true) {
	$_SESSION[$galleryid]['sorting']    = $_POST['sortlist'];
	$_SESSION[$galleryid]['accesstime'] = time();
}
?>