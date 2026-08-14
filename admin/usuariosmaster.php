<p><span class="phpmaker">Registro maestro: Usuarios
<br><a href="<?php echo $sMasterReturnUrl ?>">Volver a la pagina maestro</a></span>
</p>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Nombre</td>
		<td valign="top">Nivel</td>
		<td valign="top">Último Acceso</td>
		<td valign="top">IP</td>
	</tr>
	<tr class="ewTableSelectRow">
		<td>
<div<?php echo $usuarios->Nombre->ViewAttributes() ?>><?php echo $usuarios->Nombre->ViewValue ?></div>
</td>
		<td>
<div<?php echo $usuarios->idLevel->ViewAttributes() ?>><?php echo $usuarios->idLevel->ViewValue ?></div>
</td>
		<td>
<div<?php echo $usuarios->UltimoAcceso->ViewAttributes() ?>><?php echo $usuarios->UltimoAcceso->ViewValue ?></div>
</td>
		<td>
<div<?php echo $usuarios->IP->ViewAttributes() ?>><?php echo $usuarios->IP->ViewValue ?></div>
</td>
	</tr>
</table>
<br>
