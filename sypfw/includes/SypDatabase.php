<?php
class SypDatabase {

	const ERROR_LOG_DEBUG	= 1;
	const ERROR_LOG_DB		= 2;
	const ERROR_LOG_MAIL		= 3;

	private $_host;
	private $_port;
	private $_password;
	private $_user;
	private $_db;
	
	private $_error_log;
	private $_error;
	private $_error_description;
	private $_error_msg;

	#####################################################
	private $_connection;
	private $_queries;
	private $_id;
	//public $_queries = array();
	private $_maxQueries = 100;

	public function __construct( $user, $pass, $dbname = '', $host = '127.0.0.1', $port = 3306 ) {
		$this->_queries = array();
		
		$this->_host		= $host;
		$this->_port		= $port;
		$this->_user		= $user;
		$this->_password	= $pass;
		$this->_db			= $dbname;
		$this->_error		= false;
		$this->_error_description = '';
		
		$this->_id = time();
		
		try {
			$this->_connection = @mysql_connect ($this->_host.":".$this->_port,
											$this->_user,
											$this->_password);
		} catch (Exception $exc) {
			echo $exc->getTraceAsString();
			$this->_error = true;
			$this->_error_description = 'I cannot connect to the MySQL server. '.$exc->getTraceAsString();
		}

		

		if ( $this->_connection )
		{
			if ( $dbname != '' )
				$this->selectDatabase ($dbname);
		}
		else
		{
			//throw new Exception('I cannot connect to the database.');
			$this->_error = true;
			$this->_error_description = 'I cannot connect to the MySQL server.';
		}
	}

	public function error(){
		return $this->_error;
	}
	
	public function getErrorDescription(){
		return $this->_error_description;
	}

	public function selectDatabase( $dbname ){
		$this->_db = $dbname;
		if ( $dbname != '' )
			if (!mysql_select_db ($this->_db)){
				$this->_error = true;
				$this->_error_description = 'I cannot find the specified database "'.$this->_db.'".';
			}
	}

	public function query( $sql )	{		
		if ( count( $this->_queries ) == $this->_maxQueries )
			array_shift( $this->_queries );

		$query_start = microtime(true);
		$cursor = mysql_query($sql, $this->_connection);
		$query_end = microtime(true);
		
		$this->_queries[] = array(
			'sql'		=> $sql,
			'start'	=> $query_start,
			'end'		=> $query_end,
			'time'	=> $query_end - $query_start

		);

		if ( mysql_errno() ) $this->_showError( $sql );
		return $cursor;
	}

	public function lastInsertId()	{
		return mysql_insert_id( $this->_connection );
	}

	public function read( $cursor )	{
		return mysql_fetch_array($cursor);
	}
	
	public function reset_read( $cursor )	{
		return mysql_data_seek($cursor, 0);
	}

	public function getAffectedRows()	{
		return mysql_affected_rows( $this->_connection );
	}

	public function showLatestQueries()	{

		echo '<table border="1" class="syp_table">';
		echo '<tr>';
		echo '<th>#</th>';
		echo '<th>Time</th>';
		echo '<th>Query</th>';
		//echo '<th>Start</th>';
		//echo '<th>End</th>';
		echo '</tr>';

		$sec = explode(' ', $this->_queries[$i]['time']);
		$sec = $sec[1];
		
		$count = 0;
		$tottime = 0;
		for ( $i = (count( $this->_queries ) - 1 ) ; $i > -1 ; $i-- )
		{
			$count++;
			$tottime += $this->_queries[$i]['time'];
			echo '<tr>';
			echo '<td>'.$count.'</td>';
			echo '<td>'.$this->_queries[$i]['time'].'</td>';
			echo '<td>'.$this->_queries[$i]['sql'].'</td>';
			//echo '<td>'.$this->_queries[$i]['start'].'</td>';
			//echo '<td>'.$this->_queries[$i]['end'].'</td>';
			echo '</tr>';
		}
		echo '<tr>';
		echo '<td colspan="4">Total time: '.$tottime.' </td>';
		echo '</tr>';
		echo '</table>';
	}

	private function _showError( $txt = '' )	{
		die("MySQL Error: " . mysql_errno() . " : " . mysql_error() . "<br />".$txt );
		Log::l("MySQL Error: " . mysql_errno() . " : " . mysql_error() . "<br />".$txt );
		die("Se produjo un error. Por favor intentelo mas tarde.");
	}
}
