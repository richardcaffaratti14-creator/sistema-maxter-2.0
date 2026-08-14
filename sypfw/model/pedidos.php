<?
class pedidos extends SypTable {
	
	public $id;
	public $nombre;
	public $apellido;
	public $telefono;
	public $total;
	public $descripcion;
	public $pedido;
	public $estado;
	public $idPresupuesto;
	public $idFotolibro;
	public $sena;



	public function __construct()
	{
		$this->_pks = array( 
			array('name' =>'id',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => 'PRI',
				'default' => '',
				'extra' => 'auto_increment')
		);
		$this->_table = 'pedidos';
		$this->_fields = array( 
			array('name' => 'id',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => 'PRI',
				'default' => '',
				'extra' => 'auto_increment'),
			array('name' => 'nombre',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'apellido',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'telefono',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'Descuento',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'total',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'descripcion',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'pedido',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'extra',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'idVendedor',
				'type' => 'NUMERIC',
				'null' => 'YES',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'estado',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'Evento',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'idPresupuesto',
				'type' => 'NUMERIC',
				'null' => 'YES',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'idFotolibro',
				'type' => 'NUMERIC',
				'null' => 'YES',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'sena',
				'type' => 'NUMERIC',
				'null' => 'YES',
				'key' => '',
				'default' => '',
				'extra' => ''),
		);
		parent::__construct();
	}
	
	function  __clone() {
		
	}

	//	podria ir en syp_table ???
	public function get( $id = null )
	{	
		if ( $id != null ) $this->addCondition('id', $id); 
		
		$this->limit(1);
		$this->executeGet();	
	}

}
?>