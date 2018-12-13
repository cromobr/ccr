<!-- INCLUDES FLOWPLAYER JavaScript FILE THAT DOES EMBEDDING AND PROVIDES THE FLOWPLAYER API -->
<script type="text/javascript" src="<?php echo $flwp_javascript;?>"></script>
<table class="minithumb" cellspacing="0" cellpadding="0" border="0" align="center">
<tr>
	<td>&nbsp;<?php $mg2->output('nav_first');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_prev');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_this');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_next');?>&nbsp;</td>
	<td>&nbsp;<?php $mg2->output('nav_last');?>&nbsp;</td>
</tr>
</table>
<?php
	// GET START BUTTON ATTRIBUTES
	list(	$buttonURL,
			$buttonRad,
			$buttonSize) = $mg2->getImgAttributes('play_large.png');

	// GET SPLASH IMAGE TAG
	$splashTag = ($splashURL || $splashColor)?
					 sprintf('
						<div style="background-image:url(%s);background-color:%s;width:100%%;height:100%%;">
							<img style="margin:%dpx 0 0 %dpx;border:0;border:0;" src="%s" %s alt="" usemap="#startbutton" />
							<map id="startbutton" name="startbutton">
								<area shape="circle" href="javascript:void()" coords="%7$d,%7$d,%7$d" alt="%8$s" title="%8$s" />
							</map>
						</div>'
						,
						$splashURL,
						$splashColor,
						$mg2->height/2 - $buttonRad,
						$mg2->width/2  - $buttonRad,
						$buttonURL,
						$buttonSize,
						$buttonRad,
						$mg2->lang['playvideo']
					 )
					 :
					 '';

	// DISPLAY FLASH CONTAINER
	printf('<div id="player" class="viewflash" style="width:%dpx;height:%dpx;">%s</div>',
		$mg2->width,
		$mg2->height,
		$splashTag
	);
?> 
<!-- THIS WILL INSTALL FLOWPLAYER INSIDE PREVIOUS DIV TAG -->
<script language="JavaScript" type="text/javascript">
<!--
	$f("player", '<?php echo $flwp_playerPath;?>',
		{
			canvas: {
				backgroundImage:		"url(<?php echo $flwp_bgURL;?>)",
				backgroundGradient:	"<?php echo $flwp_gradient;?>",
				backgroundColor:		"<?php echo $flwp_bgColor;?>",
				backgroundRepeat:		"<?php echo $flwp_repeat;?>"
			},
			plugins:
			{
				// controlbar settings
				controls:
				{ 
					opacity:		'<?php echo $flwp_opacity;?>',
					autoHide:	'<?php echo $flwp_autoHide;?>',
					fullscreen:	 <?php echo $flwp_fullscreen;?>
				}
			},
			clip:
			{
				url:				'<?php echo $mg2->imagefile;?>',
				scaling:			'<?php echo $flwp_scaling;?>',
				autoPlay:		 <?php echo $flwp_autoPlay;?>,
				autoBuffering:  true
			}
		}
	);
-->
</script>
<div class="description" style="width:<?php echo ($mg2->width+2);?>px;">
	<?php $mg2->output('description');?>
</div>
<div class="copyright"><?php $mg2->output('copyright');?></div>
