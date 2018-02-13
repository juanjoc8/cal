<?php

session_start();
//echo session_id();

/**
 * Show errors
 */
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', true);
setlocale(LC_CTYPE,"es_ES");
if (function_exists("date_default_timezone_set")) {
	date_default_timezone_set('America/Lima');
}

	require_once("config_vars.php");
	require('Smarty.class.php');
	require_once("DB.php");
	require_once("PHPLogger.php");
   
	
	class Base
	{
		/**
		* Smarty object property.
		*
		* @var smarty object
		*/
		var $smarty;
  
		/**
		* Pear::DB property.
		*
		* @public pear::db object   
		*/
		var $db;
		var $dbi;
  
		/**
		* Pear::DB required property, DSN.
		*
		* @var string
		*/
		var $dbdsn;
  
		/**
		* Pear::DB required property, Database connection options.
		*
		* @var array
		*/
		var $dboptions;
		var $dboptionsi;
		
		var $log;
		
		var $clase;
		
		/* 
			variables de la pagina html
			type: html o json (html x default)
			modal: 1 o '' ( '' x default)
		*/
		var $type;
		var $modal;
		
		 function Base($ignoreDbConnect = false)
		{

			$this->smarty = new Smarty;
			
			$this->log = new PHPLogger('../logger.log');
			
			$this->smarty->template_dir = $GLOBALS['CONFIG']['path'].'pages/';
			$this->smarty->compile_dir = $GLOBALS['CONFIG']['path'].'Smarty/templates_c';
			$this->smarty->cache_dir = $GLOBALS['CONFIG']['path'].'Smarty/cache';
			$this->smarty->config_dir = $GLOBALS['CONFIG']['path'].'Smarty/configs'; 
			
			if (!$ignoreDbConnect)
			{
				$connec = $GLOBALS['CONFIG']['dbuser'].':'.$GLOBALS['CONFIG']['dbpass'].'@'.$GLOBALS['CONFIG']['dbhost'].'/'.$GLOBALS['CONFIG']['dbname'];
				
				
				//if(!$_SESSION['usuario']){
				//	echo "xxx";
				//	header ('Location: ../pages/index.html');
				//}
					
			  
			  $this->dbdsn = 'mysql://'.$connec;
			  
			  // Setup DB Connection options
			  $this->dboptions = array(
				  'debug'       => 2,
				  'portability' => DB_PORTABILITY_ALL,
			  );
			  
			  // Connect to database
			  $this->db =& DB::connect($this->dbdsn, $this->dboptions);
			  
			  // Set fetch mode to associative arrays.
			  $this->db->setFetchMode( DB_FETCHMODE_ASSOC );
			  
			 
			 // @mysql_query("");
			  // $this->db->query("SET NAMES 'utf8'");
			  
			  // If something happens, show it.
			  if (PEAR::isError($this->db)) 
			  {
				die($this->db->getMessage());
			  } 
			  //$this->db->setFetchMode( DB_FETCHMODE_ASSOC );
			  $this->Mysqli($connec); // Aumentado por Elmer
			  
			   // @mysql_query("");
			   $this->db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
						  
			}   
			
			$this->smarty->assign('config',$GLOBALS['CONFIG']);
			
			$this->type		= $_REQUEST['type'];
			$this->modal	= $_REQUEST['modal'];
			$this->mod		= $_REQUEST['mod'];
			$this->scope	= $_REQUEST['scope'];

		}
		
		/**
	   * Back-end function for modules administrator, get listing and links
	   */
		function Mysqli($connec) { // Aumentado por Elmer
		  $this->dbdsni = 'mysqli://'.$connec;
		  $this->dboptionsi = array('debug' => 2,'portability' => DB_PORTABILITY_ALL,);
		  $this->dbi =& DB::connect($this->dbdsni, $this->dboptionsi);
		  $this->dbi->setFetchMode( DB_FETCHMODE_ASSOC );
		  if((PEAR::isError($this->dbi)))
			  die($this->dbi->getMessage());
	  }
	  
	  
	/*
		Lista una entidad 'estados activos', de acuerdo a parametros
	*/
	function DBUtilListarParams($entidad,$params){
		
		$query = "select * from " . $entidad;
		$query_cond = '';

		$array_lista = array();
			
		
		foreach($params as $key=>$value){
			if($value!='')
				$query_cond  = $query_cond  . " and " . $key  . " = '" . trim($value) . "' ";
		}
		
		$query = $query . " where est = 'A'";
		
		if($query_cond !='')
				$query = $query  . $query_cond;	  
		
		$query .= ' order by nom';
		
		$this->log->d($this->clase, $query);
		
		$res = $this->db->query($query);
		
		$error = $this->db->isError($this->db); 
		
		if ($this->db->isError($res)){
				$this->log->e($this->clase, 'DBUtilListarParams:' . $res->getDebugInfo());		

		}else{
			
				while ($row = $res->fetchRow()) {
					$array_lista[$row['id']] = $row;
				}	
				$this->log->d($this->clase, $array_lista);		
		}
		
		return $array_lista;
		
		
	}	
	
	function DBUtilListarParamsNombre($entidad,$params){
		
		$query = "select * from " . $entidad;
		$query_cond = '';
		
		$array_lista = array();
		
		
		foreach($params as $key=>$value){
			if($value!='')
				$query_cond  = $query_cond  . " and " . $key  . " = '" . trim($value) . "' ";
		}
		
		$query = $query . " where est = 'A'";
		
		if($query_cond !='')
			$query = $query  . $query_cond;
			
			$query .= ' order by nombre';
			
			$this->log->d($this->clase, $query);
			
			$res = $this->db->query($query);
			
			$error = $this->db->isError($this->db);
			
			if ($this->db->isError($res)){
				$this->log->e($this->clase, 'DBUtilListarParams:' . $res->getDebugInfo());
				
			}else{
				
				while ($row = $res->fetchRow()) {
					$array_lista[$row['id']] = $row;
				}
				$this->log->d($this->clase, $array_lista);
			}
			
			return $array_lista;
			
			
	}

	/*
		Lista una entidad, de acuerdo a parametros
	*/
	function DBUtilRowById($entidad,$id){
		
		
		$query = "select * from " . $entidad;
		$query = $query . " where  id='" . $id . "'";
		
		
		$this->log->d($this->clase, $query);
		
		$res = $this->db->query($query);
		
		$this->log->e($this->clase, 'DBUtilRowById:' . $query);		
		
		$error = $this->db->isError($this->db); 
		
		if ($this->db->isError($res)){
				$this->log->e($this->clase, $res->getDebugInfo());		

		}else{
			
				while ($row = $res->fetchRow()) {
					$array_lista[$row['id']] = $row;
				}	
				$this->log->d($this->clase, $array_lista);		
		}
		if (is_array($array_lista))
			return reset($array_lista);
		else 
			return [];
		
		
		
		
	}	
	/*
		Utilitario para transformar objeto a json
	*/
	
	function UtilObjToJson($obj){
		
		$str_json = '';
		
		if(is_array($obj) && is_array(reset($obj) )){
			//echo "x";
			$str_json = '[';
		}else{
			
			$str_json = '{';
		}
		
		$cont =0;
		
		foreach($obj  as $key=>$value){
			
			if(is_array($value)){
				$cont = $cont + 1;
				if($cont>1)
						$str_json = $str_json . ',';
				$str_json = $str_json . '{';
				foreach($value  as $key1=>$value1){
					$str_json = $str_json . '"' . $key1  . '":"' . $value1 . '",';
				}
				$str_json = substr($str_json,0, strlen($str_json)-1) ;
				$str_json = $str_json . '}';
			}else{
				$str_json = $str_json . '"' . $key  . '":"' . $value . '",';
			}
			
			
			
		}
		
		
		if(is_array($obj) && is_array(reset($obj) )){
			$str_json = $str_json . ']';
		}else{
			if (strlen($str_json)>1)
				$str_json = substr($str_json,0, strlen($str_json)-1) ;
			$str_json = $str_json . '}';
		}
		
		if ($str_json =="{}")
			$str_json = "[]";
		
		$this->log->d($this->clase,'json:' .$str_json);
		
		return $str_json;
	}
	
	
	/*
		Pintar un objeto en formato json
	*/	 	
	function displayJson($obj){
		$str_json = $this->UtilObjToJson($obj);
		
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		
		echo $str_json;
	}
	
	
	/*
		grabar entidad
	*/	
	function DBUtilGrabar($entidad, $valores){
		$sql = '';
		$exist_id = false;
		
		$id_value;
		$this->log->d($this->clase,"_REQUEST['father']:" . isset($_REQUEST['father']));
		
		if ($_REQUEST['scope']){
			$this->log->d($this->clase,"grabando entidad session:" . $entidad);
			return $this->DBUtilGrabarSession($entidad, $valores);
		}
		
		foreach($valores as $key=>$value){
			if($key=='id' && $value!=''){
				$id_value = $value;
				$exist_id = true;
			}
		}
		
		
		if(!$exist_id){
			
			$this->log->d($this->clase,"grabando entidad BD:" . $entidad);
			
			$array_values = array();
			$sql_insert ="insert into " .  $entidad .  " (";
			$sql_values ="(";
			
			$est_exists = false;
			foreach($valores as $key=>$value){
				if($key!='id' && trim($value)!=''){
					$array_values[] = $value; 
					if($key == 'est')
						$est_exists = true;
					$sql_insert = $sql_insert . $key . ',' ;
					$sql_values = $sql_values . '?,' ;
					
				}
			}

			$array_values ['fec_ins'] = date("Y-m-d H:i:s");
			$array_values ['usr_ins'] = $_SESSION['usuario']['id'];
			if(!$est_exists){
				$array_values ['est'] = 'A';
				$sql_insert = substr($sql_insert,0, strlen($sql_insert)-1) . ', fec_ins,usr_ins,est) values';
				$sql_values = substr($sql_values,0, strlen($sql_values)-1) . ',?,?,?);';
			
			}else{
				$sql_insert = substr($sql_insert,0, strlen($sql_insert)-1) . ', fec_ins,usr_ins) values';
				$sql_values = substr($sql_values,0, strlen($sql_values)-1) . ',?,?);';
				
			}
			
			$sql = $sql_insert . $sql_values;
			
			$this->log->d($this->clase,'DBUtilGrabar:' . $sql);
			
			$rs = $this->db->query($sql, $array_values);
			
			$this->log->d($this->clase,"result->");
			$this->log->d($this->clase,$rs);
			
			$this->log->d($this->clase,"paso1");
			$this->log->d($this->clase,$array_values);
			
			$id_new = mysql_insert_id();
			
			$this->log->d($this->clase,"paso2");
			
			
			if ($this->db->isError($rs)){
				$this->log->d($this->clase,"paso3");
				$this->log->e($this->clase, 'DBUtilGrabar Error:' . $rs->getDebugInfo());	
				$result['code']='1';
				$result['message']=$rs->getDebugInfo();
				$result['id']='0';
			}else{
				$this->log->d($this->clase,"paso4");
				$result['code']='0';
				$result['message']='Grabado exitosamente';
				$result['id']=$id_new;
			}
			
			$this->log->d($this->clase,"paso5");
			return $result;
			
		}else{
			$array_values = array();
			$sql_update ="update " .  $entidad .  " set ";
			
			foreach($valores as $key=>$value){
				if($key!='id'){
					$array_values[] = $value; 
					
					$sql_update = $sql_update . $key . '=?,' ;
				}
			}

			$array_values ['fec_act'] = date("Y-m-d H:i:s");
			$array_values ['usr_act'] = $_SESSION['usuario']['id'];
			$array_values[] = $id_value;
			
			
			$sql_update = substr($sql_update,0, strlen($sql_update)-1) . ',fec_act=?,usr_act=? where id=?';
			$rs = $this->db->query($sql_update, $array_values);
			
			$this->log->d($this->clase,'DBUtilGrabar:' . $sql_update);
			
			if ($this->db->isError($rs)){
				$this->log->e($this->clase, 'DBUtilGrabar Error:' . $rs->getDebugInfo());	
				$result['code']='1';
				$result['message']=$rs->getDebugInfo();
				$result['id']='0';
			}else{
				$result['code']='0';
				$result['message']='Actualizado exitosamente';
				$result['id']=$id_new;
			}
			return $result;
		}
		
	}
	
/*
		grabar entidad a sesion
	*/	
	function DBUtilGrabarSession($father, $valores){
		
		//valida si la session es nuevo
		$session_nuevo_registro = 1;
		
		if($_SESSION[ $father]){
			if ($valores['id'])
				$session_nuevo_registro = $valores['id'];
			else
				$session_nuevo_registro = count($_SESSION[ $father]) + 1;
			
		}else{
			
			$_SESSION[ $father] = array();

		}

		$valores['id']	= $session_nuevo_registro;
		
		$_SESSION[ $father][$session_nuevo_registro ] =  $valores;
		
		$result['code']='0';
		$result['message']='Grabado exitosamente';
		$result['id']=$session_nuevo_registro;
			
		return $result;

			
	}	
	
	/*
	ejecutar un query con parametros
	*/
	function DBUtilQueryParams($query,$params){
		$query_cond = '';
		foreach($params as $key=>$value){
			if($value!=''){
				if(is_numeric($value) )
					$query_cond  = $query_cond  . " and " . $key  . " = " . trim($value) . " ";
				else
					$query_cond  = $query_cond  . " and upper(" . $key  . ") like '%" . trim(strtoupper($value)) . "%' ";
			}
				
		}
		$pos_order =strpos($query,"order");
		$query2	= '';
		if( $pos_order >0 ){
			$this->log->d($this->clase, 'pos_order:' . $pos_order);
			$query2	=  substr($query,$pos_order);
			$query = substr($query,0,$pos_order-1);
			
		}
		
		if ($query_cond != '' ){
			if(strpos($query,"where")>0)
				$query = $query . $query_cond ;
			else
				$query = $query . ' where 1=1 ' . $query_cond ;
		}
			
		$query = $query . ' ' . $query2;
			
		$array_lista = array();
		$this->log->d($this->clase, 'DBUtilQueryParams:' . $query);
		$res = $this->db->query($query );

		if ($this->db->isError($res)){
				$this->log->e($this->clase, 'DBUtilQueryParams:' . $res->getDebugInfo());	
		}else
		while ($row = $res->fetchRow()) {
			if ($row['est']=='A')
				$row['est_des'] = 'Activo';
			else
				$row['est_des'] = 'Inactivo';
			//print_r("--------------------------------------------" . $row['id'] . "-------------------------------------");
			//print_r($row);
			$array_lista[$row['id']] = $row;
		}	
		
		$this->log->d($this->clase, 'DBUtilQueryParams->');		
		$this->log->d($this->clase, $array_lista);		
		
		return $array_lista;
	}	
	
	/* 
		Para hacer querys de consulta, like%
	*/
	function DBUtilConsultar($entidad,$valores){
		
		$query = 'select * from ' . $entidad . " where 1=1 ";
		$query_cond = '';

		$array_lista = array();
		foreach($valores as $key=>$value){

			if($value!='')
				$query_cond  = $query_cond  . " and " . $key  . " like '%" . trim($value) . "%' ";
				
		}
		
		if($query_cond !='')
			$query = $query . $query_cond;	  
		
		$this->log->d($this->clase, 'DBUtilConsultar:' . $query);
		
		$res = $this->db->query($query );
				
		if ($this->db->isError($res)){
				$this->log->e($this->clase, 'DBUtilConsultar:' . $res->getDebugInfo());		

		}else		
		while ($row = $res->fetchRow()) {
			if ($row['est']=='A')
				$row['est_des'] = 'Activo';
			else
				$row['est_des'] = 'Inactivo';
			$array_lista[$row['id']] = $row;
		}	
		
		$this->log->d($this->clase, 'DBUtilConsultar->');		
		$this->log->d($this->clase, $array_lista);		
		
		return $array_lista;
		
	}
	
	function DBUtilListarXparametro($entidad,$valores){
		
		$query = 'select * from ' . $entidad;
		$query_cond = '';

		$array_lista = array();
		foreach($valores as $key=>$value){

			if($value!='')
				$query_cond  = $query_cond  . " and " . $key  . " ='" . trim($value) . "' ";
				
		}
		
		if($query_cond !='')
			$query = $query . ' where 1=1' . $query_cond;	  
		
		//echo $query;
		$res = $this->db->query($query );
				
		while ($row = $res->fetchRow()) {
			$array_lista[$row['id']] = $row;
		}	
		
		return $array_lista;
		
	}
	
	function DBUtilDetalle($entidad,$id){
		
		$query = 'select * from ' . $entidad . " where id=" . $id;
		
		$res = $this->db->query($query );
		
		while ($row = $res->fetchRow()) {
			$array_lista[$row['id']] = $row;
		}	
		
		return $array_lista;
		
	}	

	function DBUtilDetalleJSON($entidad,$id){
		
		$query = 'select * from ' . $entidad . " where id=" . $id;
		
		$str_json = $this->DBUtilDetalleJSONSQL($query );
	 
		return $str_json;
		
	}	

	function DBUtilDetalleJSONSQL($query ){
		
		$res = $this->db->query($query );
		//echo "query";
		//echo $query;
		//echo "query";
		$info =  $this->db->tableInfo($res);
		$array_fields = array();
		$array_types = array();
		foreach ($info as &$valor) {
			$array_fields[] = $valor['name'];//datetime
			$array_types [] = $valor['type'];
		}
		
		$str_json = "";
		$i = 0;
		while ($row = $res->fetchRow()) {
			foreach ($array_fields as &$columna) {
				//echo "x" . $row[$columna];
				if($array_types[$i]=='datetime'){
					$time = strtotime($row[$columna]);
					$myFormatForView = date("d/m/Y", $time);
					$str_json = $str_json . '"' . $columna .  '":"' .  $myFormatForView . '",';
				}
				else{
					$str_json = $str_json . '"' . $columna .  '":"' .  $row[$columna] . '",';
				}
				
				$i = $i+1;		
			}
		}
		
		
		
		if($str_json=='')
			$str_json =  '{"resultado":"0"}';
		else{
			$str_json = substr($str_json,0, strlen($str_json)-1);
			$str_json =  '{"resultado":"1",' . $str_json . '}';
		}
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		return $str_json;
	}



	  
	function obtenerData(){
		$data = array();
		//print_r($_GET);
		foreach($_REQUEST as $key=>$value){
			if($key!='mod' && $key!='type'&& $key!='modal'&& $key!='order'&& $key!='scope' && $key!='PHPSESSID' && $key!='_gid' && $key!='_ga' )
				$data[$key] = $value;
			if(strpos($key,"|")>0){
				unset($data[$key]);
				$data[str_replace("|",".",$key)] = $value;
			}
				
		}
		
		//print_r($data);
		return $data;
		
	}	  
	  



	function DBUtilQuery($query){
		$array_lista = array();
		$res = $this->db->query($query );
		
		if ($this->db->isError($res)) {      
			$this->error = "DB Error: [" . $res->getDebugInfo() . "]";
			$this->log->d($this->clase, 'DBUtilQuery->' . $this->error);		
		}else{
			while ($row = $res->fetchRow()) {
				$array_lista[$row['id']] = $row;
			}	
		}
		$this->log->d($this->clase, 'DBUtilQuery:' . $query);		
		$this->log->d($this->clase, $array_lista);	
		
		return $array_lista;
	}	

	

	
	function DBUtilDelete($entidad,$id){
		$res = $this->db->query('delete from ' . $entidad . ' where id='  .$id );
		if ($this->db->isError($res)){
			$this->error = "DB Error: [" . $res->getDebugInfo() . "]";
			return  $this->error;
		}
		else
			return  '';
	}	


	function DBUtilExecute($query){
		$res = $this->db->query($query );
		
		if ($this->db->isError($result)){
				$this->error = "DB Error: [" . $result->getDebugInfo() . "]";
				return -1;
		}else{
			return mysql_insert_id();			
		}		
			
	}	
	
	function format2D($number){
		return number_format($number, 2, '.', ',');
	}
}
	

?>
