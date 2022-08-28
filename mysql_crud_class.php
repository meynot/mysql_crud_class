<?php

namespace Eogsoft\Playground;
use \PDO;

class mysql_crud_class
{
	
	private $conn, $table, $fillable, $viewable, $csrf;
	
	public function __construct($connection, String $tablename, Array $fillable, Array $viewable=[])
	{
		$this->conn = $connection;
		$this->table=$tablename;
		$this->fillable = $fillable;
		$this->viewable = $viewable;
		// I am using password hash as CSRF token - to make things simple yet somehow secured
		$this->csrf = 'my_simple_csrf_which_uses_password_hash';
	}
	
	/**
     * Generate, store, and return the CSRF token
     *
     * @return string[]
     */
    public function getCSRFToken()
    {
		return password_hash($this->csrf, PASSWORD_DEFAULT);
    }
	
	private function verifyCSRFToken($hash)
	{
		return password_verify($this->csrf, $hash);
	}
	
	public function is_ajax_request():bool
	{	
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return true;
		return false;
	}
	
	
	public function index()
	{
		$cols = implode(',',$this->viewable);
		$sql = "SELECT {$cols} FROM {$this->table};";
		$result=[
			'rows'=>0,
			'data'=>[],
			'pages'=>0,
		];
		try {
			//foreach ($this->conn->query($sql) as $row)
			$stmt = $this->conn->prepare($sql);
			$stmt->execute();
			$result['data'] = $stmt->fetchAll(); //PDO::FETCH_ASSOC);
			
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		
		if($this->is_ajax_request())
			$result = json_encode($result);
		
		return $result;
	}
	
	public function create()
	{
		// form should be shown, but...
		// we create form controls dynamically in js inside theme.php
	}
	
	public function store()
	{
		$hash = filter_input(INPUT_POST, '_token');
		
		if( $this->verifyCSRFToken($hash) )
		{
		  $cols = implode(',',$this->fillable);
		  
		  $colsparam = ':'.implode(',:', $this->fillable);

		  try {
			$stmt = $this->conn->prepare('INSERT INTO `'.$this->table.'`( '.$cols.' ) VALUES ( '.$colsparam.' );');
			
			foreach($this->fillable as $col)
			{
				$values[$col] = filter_input(INPUT_POST, $col);
				//$stmt->bindParam(':'.$col, $value);
			}
			
			$stmt->execute($values);
		  } catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		  }
		}

		return $this->index();
	}
	
	public function show($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id=? LIMIT 1;";
		$result=[
			'rows'=>0,
			'data'=>[],
			'pages'=>0,
		];
		try {
			//foreach ($this->conn->query($sql) as $row)
			$stmt = $this->conn->prepare($sql);
			$stmt->execute([$id]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$result['data'] = $row; //PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		
		if($this->is_ajax_request())
			$result = json_encode($result);
		
		return $result;
	}
	
	public function previousRow($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id<? LIMIT 1;";
		$result=[
			'rows'=>0,
			'data'=>[],
			'pages'=>0,
		];
		try {
			//foreach ($this->conn->query($sql) as $row)
			$stmt = $this->conn->prepare($sql);
			$stmt->execute([$id]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$result['data'] = $row; //PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		
		if($this->is_ajax_request())
			$result = json_encode($result);
		
		return $result;
	}
	
	public function nextRow($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id>? LIMIT 1;";
		$result=[
			'rows'=>0,
			'data'=>[],
			'pages'=>0,
		];
		try {
			//foreach ($this->conn->query($sql) as $row)
			$stmt = $this->conn->prepare($sql);
			$stmt->execute([$id]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$result['data'] = $row; //PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		
		if($this->is_ajax_request())
			$result = json_encode($result);
		
		return $result;

	}
	
	public function edit($id)
	{
		return $this->show($id);
	}
	
	public function update($id)
	{
		$cols='';
		$values=[];
		$loop=(Object)['index'=>0];
		foreach($this->fillable as $col)
		{
			if( $loop->index > 0 ) $cols.=', ';
			$cols.= "{$col} = :{$col}";
			$values[$col] = filter_input(INPUT_POST, $col);
			$loop->index++;
		}
		$values['id']=$id;
		
		$sql = "UPDATE {$this->table} SET {$cols} WHERE id=:id;";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute($values);
		
		return $this->index();
	}
	
	public function delete($id)
	{
		$sql = "DELETE FROM {$this->table} WHERE id=?";
		$stmt= $this->con->prepare($sql);
		$stmt->execute([$id]);
		return $this->index();
	}
}