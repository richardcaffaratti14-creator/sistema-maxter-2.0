<?
//Save user level to session
$tmpUserLevel = $_SESSION[EW_SESSION_USER_LEVEL];

$headerSecurity = new cAdvancedSecurity();

function header_canList($item) {
	global $headerSecurity;
	
	$headerSecurity->LoadCurrentUserLevel($item);
	return ($headerSecurity->CanList());
}
function header_canAdmin($item) {
	global $headerSecurity;

	$headerSecurity->LoadCurrentUserLevel($item);
	return ($headerSecurity->CanAdmin());
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?=$Config_NombreCliente?> - Administrador de pedidos</title>
    <link href="siteadmin.css" rel="stylesheet" type="text/css" />
    <link href="custom.css" rel="stylesheet" type="text/css" />
    <link href="custom-print.css" rel="stylesheet" type="text/css" media="print" />
    <script type="text/javascript" src="tab.js"></script>
    <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
   
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	
	
	<?if (@$noIncluirMenu) {?>
		<style type="text/css">
		body {
			background-image:none;
		}
		.subheader {
			width:450px;
		}
		#whiteback {
			width:450px;
		}
		</style>
	<?}?>
	
	
</head>
<body>


<script type="text/javascript">
<!--
var EW_DATE_SEPARATOR; // Default date separator
EW_DATE_SEPARATOR = "/";
if (EW_DATE_SEPARATOR == '') EW_DATE_SEPARATOR = '/';
EW_UPLOAD_ALLOWED_FILE_EXT = "gif,jpg,jpeg,bmp,png,doc,xls,pdf,zip"; // Allowed upload file extension
var EW_FIELD_SEP = ', '; // Default field separator

// Ajax settings
EW_LOOKUP_FILE_NAME = "ewlookup50.php"; // lookup file name
EW_ADD_OPTION_FILE_NAME = "ewaddopt50.php"; // add option file name

// Auto suggest settings
var EW_AST_SELECT_LIST_ITEM = 0;
var EW_AST_TEXT_BOX_ID;
var EW_AST_CANCEL_SUBMIT;
var EW_AST_OLD_TEXT_BOX_VALUE = "";
var EW_AST_MAX_NEW_VALUE_LENGTH = 5; // Only get data if value length <= this setting

// Multipage settings
var ew_PageIndex = 0;
var ew_MaxPageIndex = 0;
var ew_MinPageIndex = 0;
var EW_TABLE_CLASSNAME = "ewTable"; // Note: changed the class name as needed
var ew_MultiPageElements = new Array();

//-->
</script>
<script type="text/javascript" src="ewp50.js"></script>
<script type="text/javascript" src="userfn50.js"></script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js");
//-->

</script>
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
	<!-- header (begin) -->
	
	<tr class="ewHeaderRow">
		<td>
		
<?if (isset($_SESSION['siteadmin_status_UserName'])) {?>
	<?if (!@$noIncluirMenu) {?>
	<table width="865" align="center" border="0" cellpadding="0" cellspacing="0" class="top_back noprint">
	  <tr>
		<td width="865" align="right" valign="middle" class="top_link">Usuario conectado: <?=$_SESSION['siteadmin_status_UserName']?>
		<span class="stk">|</span>
		<a href="changepwd.php" class="top_link">Cambiar contraseña</a>
		<span class="stk">|</span>
		<a href="logout.php" class="top_link">Salir</a></td>
	  </tr>
	</table>
	
	<table width="865" height="70" align="center" border="0" cellpadding="0" cellspacing="0" class="noprint">
	  <tr>
		<td align="center" valign="middle" class="header_text"><img src="logo.jpg" width="301" height="50"></td>
	  </tr>
	</table>
	
	
	<table width="1000" height="80" align="center" border="0" cellpadding="0" cellspacing="0" class="noprint">
	  <tr>
		<td width="1000" valign="top"><div id="dolphincontainer">
		<div id="dolphinnav">
		<ul>
		<li><a href="#" rel="menu1"><span>PEDIDOS</span></a></li>
		<li><a href="#" rel="admin"><span>CONFIGURACIÓN</span></a></li>
		</ul>
		</div>
		
		<!-- Sub Menus container. Do not remove -->
		<div id="dolphin_inner">
		
		<div id="menu1" class="innercontent">
        <?
        $current_menu[0] = array("pedidos");
        ?>
		<ul>
		<?if (header_canList('pedidos')) {?><li><a href="pedidoslist.php" ><span>PEDIDOS</span></a></li><?}?>
		<?if (header_canList('presupuestos')) {?><li><a href="presupuestoslist.php" ><span>PRESUPUESTOS</span></a></li><?}?>
		<?if (header_canList('pedidosvend')) {?><li><a href="pedidosvendreport.php" ><span>REPORTE VENTAS</span></a></li><?}?>
		<?if (header_canList('comisiones')) {?><li><a href="comisiones.php" ><span>COMISIONES</span></a></li><?}?>
		<?if (header_canList('fotolibros')) {?><li><a href="fotolibroslist.php" ><span>FOTOLIBROS</span></a></li><?}?>
		<?if (header_canList('accesorios')) {?><li><a href="accesorioslist.php" ><span>ACCESORIOS</span></a></li><?}?>
		</ul>
		</div>
	
		<div id="admin" class="innercontent">
        <?
        $current_menu[1] = array("formato_imagen","formato_video","information","vendedores","usuarios","usuarioslevels");
        ?>
		<ul>
		<?if (header_canList('formato_imagen')) {?><li><a href="formato_imagenlist.php" ><span>FORMATOS IM&Aacute;GENES</span></a></li><?}?>
		<?if (header_canList('formato_video')) {?><li><a href="formato_videolist.php" ><span>FORMATOS VIDEOS</span></a></li><?}?>
		<?if (header_canList('formato_coreo')) {?><li><a href="formato_coreolist.php" ><span>FORMATOS COREOS</span></a></li><?}?>
		<?if (header_canList('information')) {?><li><a href="informationlist.php" ><span>CONFIG</span></a></li><?}?>
		<?if (header_canList('vendedores')) {?><li><a href="vendedoreslist.php"><span>VENDEDORES</span></a></li><?}?>
		<?if (header_canList('usuarios')) {?><li><a href="usuarioslist.php"><span>USUARIOS</span></a></li><?}?>
		<?if (header_canAdmin('usuarioslevels')) {?><li><a href="usuarioslevelslist.php"><span>NIVELES USUARIOS</span></a></li><?}?>
		<?if (header_canList('backup')) {?><li><a href="backup_db.php"><span>COPIA DE DB</span></a></li><?}?>
		</ul>
		</div>
	
		<!-- End Sub Menus container -->
		</div>
	
		</div>


    <?
    function getActiveTab() {
        global $current_menu;
        $current_file = pathinfo($_SERVER["REQUEST_URI"]);
        $current_file = $current_file['basename'];

        $ret = 0;
        foreach ($current_menu as $k => $v) {
            foreach ($v as $menues) {
                if  (
                    (strpos($current_file, $menues."list")===0) ||
                    (strpos($current_file, $menues."add")===0) ||
                    (strpos($current_file, $menues."edit")===0) ||
                    (strpos($current_file, $menues."view")===0) ||
                    (strpos($current_file, $menues."srch")===0) ||
                    (strpos($current_file, $menues."report")===0) ||
                    (strpos($current_file, $menues."delete")===0)
                    ) {
                    $ret = $k;
                }
            }
        }
        return $ret;
    }
    ?>

	<script type="text/javascript">
	
	//dolphintabs.init("ID_OF_TAB_MENU_ITSELF", SELECTED_INDEX)
	dolphintabs.init("dolphinnav", <?=getActiveTab()?>)
	
	</script>
	</td>
	  </tr>
	</table>
	
<?
	}
}
else {?>
	<table width="865" align="center" border="0" cellpadding="0" cellspacing="0" class="top_back">
	<tr>
		<td width="865" align="right" valign="middle" class="top_link">&nbsp;
		</td>
	</tr>
	</table>
	
	<table width="865" height="70" align="center" border="0" cellpadding="0" cellspacing="0">
	  <tr>
		<td align="center" valign="middle" class="header_text"><img src="logo.jpg" width="301" height="50"></td>
	  </tr>
	</table>
	
	
	<table width="865" height="80" align="center" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	  	<td>
		</td>
	  </tr>
	</table>
	
<?}?>		


		
		</td>
	</tr>
	<!-- header (end) -->
	<!-- content (begin) -->
	<tr>
		<td height="100%" valign="top">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<!-- left column (begin) -->
		<!-- left column (end) -->
		<!-- right column (begin) -->
		<td valign="top" class="ewContentColumn">



<?
//Restore user level to session
$_SESSION[EW_SESSION_USER_LEVEL] = $tmpUserLevel;
?>