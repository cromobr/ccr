<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>FATAL ERROR</title>
</head>
<body>
<div style="margin:18px 0 0 10px;color:red;font:14pt arial, helvetica, sans-serif;">
<?php
	echo $message;
	if (!defined('ADMIN_INDEX')) {
		echo '
			Please contact the
			'.
			(($mg2->adminemail)?
			'<a href="mailto:'. $mg2->adminemail .'">webmaster</a>.'
			:
			'webmaster.')
		;
	}
?>
</div>
</body>
</html>