<?php

class Templater {

	private $_tpl;
	private $_crude_tpl;

	private $_loops;
	private $_keys_search;
	private $_keys_replace;
	
	private $_loop_init = array('{{','}}');
	private $_loop_end = array('{{/','}}');
	
	private $_key_tags = array('{','}');

	private $_result;

	public function __construct( $tpl, $isFile = true ) {
		$this->_tpl = $tpl;
		
		if ( $isFile )
		{
			if ( is_file( $tpl ) )
				$this->_crude_tpl = file_get_contents( $tpl );
			else{
				Dump::dl('ERROR:: Template not found. ' .$tpl);
				$this->_crude_tpl = '';
			}
		}
		else
			$this->_crude_tpl = $tpl;
	}
	
	public function getLoopInit() {
		return $this->_loop_init;
	}

	public function setLoopInit($_loop_init) {
		$this->_loop_init = $_loop_init;
	}

	public function getLoopEnd() {
		return $this->_loop_end;
	}

	public function setLoopEnd($_loop_end) {
		$this->_loop_end = $_loop_end;
	}
	
	public function setLoopTags($start_loop_init_tag, $start_loop_end_tag, $end_loop_init_tag, $end_loop_end_tag){
		$this->_loop_init[0] = $start_loop_init_tag;
		$this->_loop_init[1] = $start_loop_end_tag;
		$this->_loop_end[0] = $end_loop_init_tag;
		$this->_loop_end[1] = $end_loop_end_tag;
	}

	public function getKeyTags() {
		return $this->_key_tags;
	}

	public function setKeyTags($init_tag, $end_tag) {
		$this->_key_tags[0] = $init_tag;
		$this->_key_tags[1] = $end_tag;
	}
	
		
	public function addLoop( $loopName, $searchFields, $replaceFields ) {
		$this->_loops[] = array(
			'name' => $loopName ,
			'search' => $searchFields ,
			'replace' => $replaceFields
		);
	}

	public function addKey ( $keyName, $replace ) {
		$this->_keys_search[] = $this->_key_tags[0].$keyName.$this->_key_tags[1];
		$this->_keys_replace[] = $replace;
	}

	public function setKeys( $keyArray, $replaceArray ){
		for ( $i = 0 , $m = count($keyArray) ; $i < $m ; $i++ )
		{
			$this->addKey( $keyArray[$i] , $replaceArray[$i] );
		}
	}

	private function create(){
		$tpl_result = $this->_crude_tpl;

		//$this->l($this->_keys_search, 'SEARCH');
		//$this->l($this->_keys_replace, 'REPLACE');
		//$this->l($this->_loops, 'LOOPS');

		$i1 = $this->_loop_init[0];
		$i2 = $this->_loop_init[1];
		$e1 = $this->_loop_end[0];
		$e2 = $this->_loop_end[1];
		
		$k1 = $this->_key_tags[0];
		$k2 = $this->_key_tags[1];
		
		for ( $i = 0 ; $i < sizeof($this->_loops) ; $i++ )
		{
			preg_match_all('%'.$i1.$this->_loops[$i]['name'].$i2.'(.*?)'.$e1.$this->_loops[$i]['name'].$e2.'%s', $tpl_result, $result, PREG_PATTERN_ORDER);
			$result = $result[1];

			//$this->l( $result, "RESULT " );

			for ( $l = 0 ; $l < sizeof( $result ) ; $l++ )
			{
				$tmpTPLBLOCK = '';
				for ( $k = 0; $k < sizeof( $this->_loops[$i]['replace'][0] ) ; $k++ )
				{
					$tmpTPL = $result[$l];
					for ( $j = 0; $j < sizeof( $this->_loops[$i]['search'] ) ; $j++ )
					{
						//echo $this->_loops[$i]['search'][$j] . " --- " . $this->_loops[$i]['replace'][$j][$k]. "<br>";
						$tmpTPL = str_replace( $k1.$this->_loops[$i]['search'][$j].$k2, $this->_loops[$i]['replace'][$j][$k], $tmpTPL );
					}
					$tmpTPLBLOCK .= $tmpTPL;
					//$this->l($tmpTPLBLOCK, "BLOCK $i $l $k $j" );
				}
				$tpl_result = preg_replace('%'.$i1.$this->_loops[$i]['name'].$i2.'(.*?)'.$e1.$this->_loops[$i]['name'].$e2.'%s', trim( $tmpTPLBLOCK ), $tpl_result, 1);
			}
		}

		$this->_result = str_replace( $this->_keys_search, $this->_keys_replace, $tpl_result );
		//$this->l( $tpl_result , '-------------- RESULT ----------------');
	}
	
	private function create2(){
		$tpl_result = $this->_crude_tpl;

		//$this->l($this->_keys_search, 'SEARCH');
		//$this->l($this->_keys_replace, 'REPLACE');
		//$this->l($this->_loops, 'LOOPS');

		for ( $i = 0 ; $i < sizeof($this->_loops) ; $i++ )
		{
			preg_match_all('%\{\{'.$this->_loops[$i]['name'].'\}\}(.*?)\{\{/'.$this->_loops[$i]['name'].'\}\}%s', $tpl_result, $result, PREG_PATTERN_ORDER);
			$result = $result[1];

			//$this->l( $result, "RESULT " );

			for ( $l = 0 ; $l < sizeof( $result ) ; $l++ )
			{
				$tmpTPLBLOCK = '';
				for ( $k = 0; $k < sizeof( $this->_loops[$i]['replace'][0] ) ; $k++ )
				{
					$tmpTPL = $result[$l];
					for ( $j = 0; $j < sizeof( $this->_loops[$i]['search'] ) ; $j++ )
					{
						//echo $this->_loops[$i]['search'][$j] . " --- " . $this->_loops[$i]['replace'][$j][$k]. "<br>";
						$tmpTPL = str_replace( '{'.$this->_loops[$i]['search'][$j].'}', $this->_loops[$i]['replace'][$j][$k], $tmpTPL );
					}
					$tmpTPLBLOCK .= $tmpTPL;
					//$this->l($tmpTPLBLOCK, "BLOCK $i $l $k $j" );
				}
				$tpl_result = preg_replace('%\{\{'.$this->_loops[$i]['name'].'\}\}(.*?)\{\{/'.$this->_loops[$i]['name'].'\}\}%s', trim( $tmpTPLBLOCK ), $tpl_result, 1);
			}
		}

		$this->_result = str_replace( $this->_keys_search, $this->_keys_replace, $tpl_result );
		//$this->l( $tpl_result , '-------------- RESULT ----------------');
	}

	public function render( $pre = 0 ){
		$this->create();
		if ( $pre == 1 )
			echo '<pre>'.$this->_result.'</pre>';
		if ( $pre == 2 )
			echo '<xmp>'.$this->_result.'</xmp>';
		else
			echo $this->_result;
	}

	public function save( $file ){
		$this->create();
		if ( $this->_result != '' )
			file_put_contents($file, $this->_result);
	}

	public function get(){
		$this->create();
		return $this->_result;
	}
	//------------------------
	private function l( $o, $label='' ) {
		echo $label.'<xmp>'.print_r( $o, true ).'</xmp><hr />';
	}


}

?>
