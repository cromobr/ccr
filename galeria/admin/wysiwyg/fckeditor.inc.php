<!-- Include FCKEditor in MG2, file 'admin_header.php' -->
<script type="text/javascript" src="<?php echo ADMIN_FOLDER;?>wysiwyg/fckeditor.js"></script>
<script type="text/javascript">
	window.onload = function () {
		var oFCKeditor = new FCKeditor('editor');
		// oFCKeditor.Width = 500;
		oFCKeditor.Config["AutoDetectLanguage"] = false;
		oFCKeditor.Config["DefaultLanguage"]    = "<?php echo substr($mg2->activelang,0,2);?>";
		oFCKeditor.BasePath = "<?php echo ADMIN_FOLDER;?>wysiwyg/";
		oFCKeditor.ReplaceTextarea();
	}
</script>
