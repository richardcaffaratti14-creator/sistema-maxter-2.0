<?

class presupuestos extends SypTable {

    public $id;
    public $nombre;
    public $apellido;
    public $telefono;
    public $presupuesto;
    public $pedido;
    public $subtotal;
    public $descuento;
    public $total;
    public $sena;
    public $idVendedor;
    public $estado;
    public $evento;

    public function __construct() {
	$this->_pks = array(
	    array('name' => 'id',
		'type' => 'NUMERIC',
		'null' => 'NO',
		'key' => 'PRI',
		'default' => '',
		'extra' => 'auto_increment')
	);
	$this->_table = 'presupuestos';
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
	    array('name' => 'presupuesto',
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
	    array('name' => 'subtotal',
		'type' => 'NUMERIC',
		'null' => 'NO',
		'key' => '',
		'default' => '',
		'extra' => ''),
	    array('name' => 'descuento',
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
	    array('name' => 'sena',
		'type' => 'NUMERIC',
		'null' => 'NO',
		'key' => '',
		'default' => '',
		'extra' => ''),
	    array('name' => 'idVendedor',
		'type' => 'NUMERIC',
		'null' => 'NO',
		'key' => '',
		'default' => '',
		'extra' => ''),
	    array('name' => 'estado',
		'type' => 'NUMERIC',
		'null' => 'NO',
		'key' => '',
		'default' => '',
		'extra' => ''),
	    array('name' => 'evento',
		'type' => 'TEXT',
		'null' => 'NO',
		'key' => '',
		'default' => '',
		'extra' => ''),
	);
	parent::__construct();
    }

    function __clone() {
	
    }

    //	podria ir en syp_table ???
    public function get($id = null) {
	if ($id != null)
	    $this->addCondition('id', $id);

	$this->limit(1);
	$this->executeGet();
    }

}

?>