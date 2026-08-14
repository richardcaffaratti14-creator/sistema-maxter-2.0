<?
class vendedores extends SypTable {
	
	public $id;
	public $Vendedor;
	public $Clave;
	public $Activo;
	

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
		$this->_table = 'vendedores';
		
		$this->_fields = array( 
			array('name' => 'id',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => 'PRI',
				'default' => '',
				'extra' => 'auto_increment'),
			
			array('name' => 'Vendedor',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			
			array('name' => 'Clave',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			
			array('name' => 'Activo',
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