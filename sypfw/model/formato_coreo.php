<?
class formato_coreo extends SypTable {
	
	public $id;
	public $Nombre;
	public $Precio;
	

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
		$this->_table = 'formato_coreo';
		$this->_fields = array( 
			array('name' => 'id',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => 'PRI',
				'default' => '',
				'extra' => 'auto_increment'),
			array('name' => 'Nombre',
				'type' => 'TEXT',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'Precio',
				'type' => 'NUMERIC',
				'null' => 'NO',
				'key' => '',
				'default' => '',
				'extra' => ''),
			array('name' => 'Sufijo',
				'type' => 'TEXT',
				'null' => 'YES',
				'key' => '',
				'default' => '',
				'extra' => '')
		);
		
		$this->carpeta = "_coreos";
		
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
		
		$this->nombre = $this->Nombre;
		$this->precio = $this->Precio;
	}

}
?>