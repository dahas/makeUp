<?php declare(strict_types=1);

namespace makeUp\lib;

use mysqli_sql_exception;


class DB {
    
    private static $instance = null;
    private $conn = null;
    private $db = "";
    private $host = "";
    private $user = "";
    private $pass = "";
    private $charset = "utf8";

    protected function __construct()
    {
        $this->db = Config::get('database', 'db_name');
        $this->host = Config::get('database', 'host');
        $this->user = Config::get('database', 'username');
        $this->pass = Config::get('database', 'password');
        $this->charset = Config::get('database', 'charset');

        try {
            $this->conn = @mysqli_connect($this->host, $this->user, $this->pass);
            if ($this->conn) {
                @mysqli_select_db($this->conn, $this->db);
                mysqli_set_charset($this->conn, $this->charset);
            }
        } catch (mysqli_sql_exception $e) {
        }
    }

	public function isAvailable(): bool
	{
		return $this->conn ? true : false;
	}

    public static function getInstance(): DB
    {
        if (self::$instance == null) {
            self::$instance = new DB();
        }

        return self::$instance;
    }

    public function select(array $conf): Recordset|false
    {
        if (!$this->conn) {
            return false;
        }

        $sql = "SELECT";
        if (isset($conf['columns'])) {
            $sql .= " {$conf['columns']}";
        }

        if (isset($conf['from'])) {
            $sql .= " FROM {$conf['from']}";
        }

        if (isset($conf['where'])) {
            $sql .= " WHERE {$conf['where']}";
        }

        if (isset($conf['groupBy'])) {
            $sql .= " GROUP BY {$conf['groupBy']}";
        }

        if (isset($conf['orderBy'])) {
            $sql .= " ORDER BY {$conf['orderBy']}";
        }

        if (isset($conf['limit'])) {
            $sql .= " LIMIT {$conf['limit']}";
        }

        $rs = mysqli_query($this->conn, $sql);
        return new Recordset($rs);
    }

    public function insert(array $conf): int
    {
        if (!$this->conn) {
            return 0;
        }

        $sql = "INSERT INTO";
        if (isset($conf['into'])) {
            $sql .= " {$conf['into']}";
        }

        if (isset($conf['columns'])) {
            $sql .= " ({$conf['columns']})";
        }

        if (isset($conf['values'])) {
            $valArr = explode(",", $conf['values']);
            $newArr = array();
            foreach ($valArr as $val) {
                $newArr[] = "'" . trim(mysqli_real_escape_string($this->conn, $val)) . "'";
            }
            $newValues = implode(",", $newArr);
            $sql .= " VALUES ($newValues)";
        }
        $res = mysqli_query($this->conn, $sql);
        if ($res) {
            return mysqli_insert_id($this->conn);
        }

        return 0;
    }

    public function update(array $conf): mixed
    {
        if (!$this->conn) {
            return false;
        }

        $sql = "UPDATE";
        if (isset($conf['table'])) {
            $sql .= " {$conf['table']}";
        }

        if (isset($conf['set'])) {
            $sql .= " SET {$conf['set']}";
        }

        if (isset($conf['where'])) {
            $sql .= " WHERE {$conf['where']}";
        }

        return mysqli_query($this->conn, $sql);
    }

    public function delete(array $conf): mixed
    {
        if (!$this->conn) {
            return false;
        }

        $sql = "DELETE FROM";
        if (isset($conf['from'])) {
            $sql .= " {$conf['from']}";
        }

        if (isset($conf['where'])) {
            $sql .= " WHERE {$conf['where']}";
        }

        return mysqli_query($this->conn, $sql);
    }

    public function __destruct()
    {
        if ($this->conn && mysqli_close($this->conn)) {
            $this->conn = null;
        }
    }

}

class Recordset {
    private $recordset = null;

    public function __construct(mixed $rs)
    {
        $this->recordset = $rs;
    }

    public function getRecordCount(): int
    {
        return $this->recordset ? mysqli_num_rows($this->recordset) : 0;
    }

    public function reset(): bool
    {
        return $this->recordset ? mysqli_data_seek($this->recordset, 0) : false;
    }

    public function next(): mixed
    {
        $record = $this->recordset ? mysqli_fetch_object($this->recordset) : null;
        if ($record) {
            return $record;
        } else {
            return null;
        }
    }

    public function __destruct()
    {
        if ($this->recordset)
            mysqli_free_result($this->recordset);
    }

}


/**
 * Class Record
 * @package makeUp\lib
 */
class Record {
    private $record = null;

    /**
     * Record constructor.
     * 
     * @param object $record Single record
     */
    public function __construct($record)
    {
        $this->record = $record;
    }

    /**
     * Access a property.
     * 
     * @param string $item
     * @return mixed $value
     */
    public function getProperty($item)
    {
        return $this->record->$item ?? null;
    }

    /**
     * Change the value of a property.
     * 
     * @param string $item
     * @param mixed $value
     */
    public function setProperty($item, $value)
    {
        $this->record->$item = $value;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        unset($this->record);
    }
}