<?php
/**
 * Chinese Time - UTF+8
 */
date_default_timezone_set("Asia/Shanghai");

/**
 * Display all the errors on the interface to help troubleshooting
 */
error_reporting(-1);

ini_set('display_errors', 'On');
/**
*Connection tools 
*
*/
class MySql_Manager
{
	private $con;
	private $server_name;
	private $username;
	private $password;
	private $db_name;
	

	public function MySql_Manager($server_name, $username, $password, $db_name)
	{
		$this->server_name = $server_name;
		$this->username = $username;
		$this->password = $password;
		$this->db_name = $db_name;
		$this->con = $this->connection();
	}

	/**
	 * Connect to the DB server
	 * @return resource
	 */
	private function connection()
	{
		if ($this->con === null) {
			$this->con = mysql_connect($this->server_name, $this->username, $this->password);
            mysql_select_db($this->db_name);
			return $this->con;
		}
		return $this->con;
	}

	/**
	 * Get connection
	 * @return resource
	 */
	public function getConnection()
	{
		return $this->con;
	}

	// Close Connection function
	public function closeConnection()
	{
		mysql_close($this->con);
	}

    /**
     * Simple query
     * @param $sql query sql sentence
     * @return array data set
     */
	public function query($sql)
	{
		$result = mysql_query($sql, $this->con) or die(mysql_error());
		$data = mysql_fetch_row($result);
		return $data;

	}

	/**
     * Multiple query
	 * @param $sql sql sentence
	 * @return array data set
	 */
	public function query_Mul($sql)
	{
		//$pre_time = microtime(true);
		$result = mysql_query($sql, $this->con);
		$data = array();
		while ($arr = mysql_fetch_row($result)) {
			array_push($data, $arr);
		}
		//$time = microtime(true) - $pre_time;
		//$date = date("Y-m-d h:i:sa");
		//echo "Date:".$date."<br>"." execution time:".$time."second";
		//echo "<br>";
		return $data;
	}

    /**
     * update query
     * @param $sql
	 * @return array data set
     */
    public function queryUpdate($sql){
        $result=mysql_query($sql,$this->con);
        return $result;
    }
}

class Download_Manager{
	function Download_Manager(){
		
	}
}

?>