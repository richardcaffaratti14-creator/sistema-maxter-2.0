<table width="100%" border="0" cellspacing="0" cellpadding="2">
<?php if (AllowListMenu('menu_izq')) { ?>
	<tr><td><span class="phpmaker"><a href="menu_izqlist.php?cmd=resetall">Menú</a></span></td></tr>
<?php } ?>
<?php if (AllowListMenu('paginas_documentos')) { ?>
	<tr><td><span class="phpmaker"><a href="paginas_documentoslist.php?cmd=resetall">Documentos</a></span></td></tr>
<?php } ?>
<?php if (IsLoggedIn() && !IsSysAdmin()) { ?>
	<tr><td><span class="phpmaker"><a href="changepwd.php">Cambiar contraseña</a></span></td></tr>
<?php } ?>
<?php if (IsLoggedIn()) { ?>
	<tr><td><span class="phpmaker"><a href="logout.php">Desautenticarme</a></span></td></tr>
<?php } elseif (substr(ew_ScriptName(), -1*strlen("login.php")) <> "login.php") { ?>
	<tr><td><span class="phpmaker"><a href="login.php">Autenticacion</a></span></td></tr>
<?php } ?>
</table>
