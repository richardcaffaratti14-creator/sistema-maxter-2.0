<?php
define("EW_PAGE_ID", "report", TRUE); // Page ID
?>
<?php
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "userfn50.php" ?>
<?php include "usuariosinfo.php" ?>
<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Always modified
header("Cache-Control: private, no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
?>
<?php @set_time_limit(999); // Set the maximum execution time (seconds)                                                           ?>
<?php
// Open connection to the database
$conn = ew_Connect();
?>
<?php
$Security = new cAdvancedSecurity();
?>
<?php
if (!$Security->IsLoggedIn())
    $Security->AutoLogin();
$Security->LoadCurrentUserLevel('pedidosvend');
if (!$Security->IsLoggedIn()) {
    $Security->SaveLastUrl();
    Page_Terminate("login.php");
}
if (!$Security->CanReport()) {
    $Security->SaveLastUrl();
    Page_Terminate("login.php");
}

$evt = $_POST['evt'];
$com = $_POST['com'];

$evt_name = array();

?>
<?php include "header.php" ?>
<p><span class="phpmaker">Comisiones

	<form method="post">
	    Evento <select name="evt" style="font-size:14px; padding:3px 5px;">
		<option value=""></option>
		<?
		$_where_sel = '';
		if (!$Security->IsAdmin()) {
			//Usuarios no admin no puedan ver otros eventos
			$_evento = getSiteInfo('evento');
			$_where_sel .= "WHERE (Evento = '" . mysql_real_escape_string($_evento) . "')";
		}
		
		$_selevt = isset($_POST['evt']) ? $_POST['evt'] : "";
		$evtrs = mysql_query("select distinct Evento from pedidos {$_where_sel} order by Evento");
		while ($e = mysql_fetch_array($evtrs)) {
		    $tmp = trim(htmlentities($e['Evento']));
		    $evt_name[$e['Evento']] = $e['Evento'];
		    if (empty($tmp) || is_null($tmp))
			continue;
		    ?><option value="<?= htmlentities($e['Evento']) ?>" <?= $e['Evento'] == $_selevt ? "selected" : "" ?>><?= htmlentities($e['Evento']) ?></option><?
		}
		?>

	    </select>

	    Comisiones <select name="com" style="font-size:14px; padding:3px 5px;">
		<option value="evt" <?= $com == 'evt'? 'selected' : '' ?>>Por Evento</option>
		<option value="ven" <?= $com == 'ven'? 'selected' : '' ?>>Por Vendedor</option>
	    </select> 
	    <input type="Submit" value="Aplicar" id="Submit" name="Submit">
	</form>
    </span>
</p>
<?
include '../sypfw/helpers/MaxterHlp.php';

function d($txt) {
    echo '<pre style="text-align: left;">' . print_r($txt, true) . '</pre>';
}



if ($evt == '') {
    echo '<h3>Selecciona un evento.</h3>';
    include "footer.php";
    die;
}

if ($com == 'evt') {
    include 'comisiones_evento.php';
    include "footer.php";
    die;
} else {
    include 'comisiones_vendedor.php';
    include "footer.php";
    die;
}


function Page_Terminate($url = "") {
	global $conn;
	 // Close Connection
	$conn->Close();

	// Go to url if specified
	if ($url <> "") {
		ob_end_clean();
		header("Location: $url");
	}
	exit();
}
