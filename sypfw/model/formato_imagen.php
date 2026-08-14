<?
class formato_imagen extends SypTable {
	
	public $id;
	public $nombre;
	public $precio;
	public $ancho;
	public $alto;
	public $carpeta;
	public $orden;
	

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
		$this->_table = 'formato_imagen';
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
			array('name' => 'precio',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'ancho',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'alto',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'carpeta',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'orden',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => '')
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