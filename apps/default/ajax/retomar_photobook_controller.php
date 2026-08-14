<style>

    .presu_table {
	width: 100%;
    }
    .presu_table th{
	text-align: left;
    }

    .presu_table td{
	padding: 6px;
    } 

    .presu_table th{
	padding: 6px;
	background-color: #F14E23;
	color: white;
    }
    .presu_table tr:nth-child(even) {
	background-color: #FBC1B3;
    }

</style>

<table class="presu_table" cellspacing="0" cellpading="0">
    <tr>
	<th>#</th>
	<th>Nombre y Apellido</th>
	<th>Vendedor</th>
	<th>Total</th>
	<th>&nbsp;</th>
    </tr>
    <tbody id="presu_container">

    </tbody>
</table>


<script>

    $(function () {
	$('#presu_container').load('ajax/photobook_tbody?vid=0');
    });

</script>