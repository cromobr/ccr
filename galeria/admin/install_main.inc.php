<?php if ($step == 0) { ?>
<table class="table_menu" cellpadding="0" cellspacing="0">
<tr valign="top">
	<td align="center" colspan="4">
		<p><strong>Instala&ccedil;&atilde;o do Sistema de Galeria</strong>!</p>
	  <p><strong> 1 / 3</strong></p>
		<p>O Sistema de Galeria ir&aacute; te ajudar instalar em 3 passos.</p>
		<br />
	</td>
</tr>
<tr valign="middle">
  <td width="140">&nbsp;</td>
  <td class="install_td" width="300">
    'data' Subpasta Gravavel:</td>
  <td width="200">
    <?php echo $test1;?>
  </td>
  <td>&nbsp;</td>
</tr>
<tr valign="middle">
  <td width="140">&nbsp;</td>
  <td class="install_td" width="300">
    'pictures' Subpasta Gravavel:
  </td>
  <td width="200">
    <?php echo $test2;?>
  </td>
  <td>&nbsp;</td>
</tr>
<tr valign="middle">
  <td width="140">&nbsp;</td>
  <td class="install_td" width="300">Pasta de Galeria Gravavel: </td>
  <td width="200">
    <?php echo $test3;?>
  </td>
  <td>&nbsp;</td>
</tr>
<tr valign="middle">
  <td width="140">&nbsp;</td>
  <td class="install_td" width="300">Sistema de Galeria: </td>
  <td width="200">
    <?php echo $test4;?>
  </td>
  <td>&nbsp;</td>
</tr>
<tr valign="middle">
  <td width="140">&nbsp;</td>
  <td class="install_td" width="300">GD image library vers&atilde;o 2.x ou recente: </td>
  <td width="200">
    <?php echo $test5;?>
  </td>
  <td>&nbsp;</td>
</tr>
<tr valign="middle">
  <td width="140">&nbsp;</td>
  <td class="install_td" width="300">
    PHP vers&atilde;o 4.3.x ou mais recente:
  </td>
  <td width="200">
    <?php echo $test6 ?>
  </td>
  <td>&nbsp;</td>
</tr>
<?php if ($todo != "") { ?>
<tr valign="bottom">
	<td>&nbsp;</td>
	<td class="install_td" colspan="3">
		<br />
		Voc&ecirc; precisa completar esses passos antes de proseguir:<br /><br /><b><?php echo $todo; ?></b>
	</td>
</tr>
<tr valign="top">
	<td align="center" colspan="4">
		<div style="margin: 5px 0 10px 0">
			<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
			<input type="hidden" name="step" value="" />
			<input type="image" src="<?php echo ADMIN_IMAGES;?>rebuild.gif" class="adminpicbutton" alt="Tente denovo" title="Tente denovo" />
			</form>
		</div>
	</td>
</tr>
<?php } else { ?>
<tr valign="top">
	<td align="center" colspan="4">
		<br />
		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
		<input type="hidden" name="step" value="2" />
		<input type="image" src="<?php echo ADMIN_IMAGES;?>ok.gif" class="adminpicbutton" alt="Pr&oacute;ximo" title="Pr&oacute;ximo" />
		</form>
	</td>
</tr>
<?php } ?>
</table>
<?php }

if ($step == 2) {?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<table class="table_menu" cellpadding="0" cellspacing="0">
<tr>
	<td align="center" colspan="3">
		<p><strong>Bem vindo ao Sistema de Galeria!</strong></p>
		<p> <strong>2</strong> / 3</p>
		<br />
	</td>
</tr>
<tr>
  <td width="200">&nbsp;</td>
	<td class="install_td" width="190"><?php echo $mg2->lang['gallerytitle'];?></td>
	<td>
		<input type="text" name="gallerytitle" value="Galeria" size="80" class="admintext" />
	</td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class="install_td" width="190"><?php echo $mg2->lang['adminemail'];?></td>
	<td>
		<input type="text" name="adminemail" value="" size="80" class="admintext" />
	</td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class="install_td" width="190"><?php echo $mg2->lang['language'];?></td>
	<td>
		<select size="1" name="defaultlang" class="admindropdown">
<?php
	for ($i=0; $i < count($lang); $i++) {
		printf('<option %s>%s</option>', $lang[$i][0], $lang[$i][1]);
	}
?>
		</select>
	</td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class="install_td" width="190"><?php echo $mg2->lang['skin'];?></td>
	<td>
		<select size="1" name="activeskin" class="admindropdown">
<?php
	for ($i=0; $i < count($skins); $i++){
		$selected = ($skins[$i] == $mg2->activeskin)? ' selected="selected"':'';
		echo '<option value="'. $skins[$i] .'"'. $selected .'>'. ucfirst($skins[$i]) .'</option>';
	}
?>
		</select>
	</td>
</tr>
<tr>
   <td width="200">&nbsp;</td>
	<td class="install_td" width="190"><?php echo $mg2->lang['password'],' (padr&atilde;o = 1234)';?></td>
	<td>
		<input type="password" name="password" value="1234" size="20" class="admintext" />
	</td>
</tr>
<tr>
	<td align="center" colspan="3">
		<br />
		<input type="hidden" name="step" value="3" />
		<input type="image" src="<?php echo ADMIN_IMAGES;?>ok.gif" class="adminpicbutton" alt="Pr&oacute;ximo" title="Pr&oacute;ximo" />
	</td>
</tr>
</table>
</form>
<?php }

if ($step == 3) {?>
<table class="table_menu" cellpadding="0" cellspacing="0">
<tr valign="top">
	<td align="center" colspan="2">
		<p><strong>Bem vindo na instala&ccedil;&atilde;o Sistema de Galeria!</strong></p>
	  <p><strong>Step 3 / 3</strong></p>
		<p>Parab&eacute;ns, Sistema de Galeria foi instalado com sucesso !</p>
        <p>O Sistema de Galeria, foi instalado usando suas configura&ccedil;&otilde;es.</p>
<br />
		<p><a href="admin.php">Ir para o Painel de Controle</a></p>
	</td>
</tr>
</table>
<?php } ?>