<?php

namespace makeup\lib;


/**
 * Class Service
 * @package makeup\lib
 */
abstract class Service
{
	protected $DB = null;
	protected $recordset = null;

	protected $table = "";
	protected $uniqueId = "";
	protected $columns = "*";


	/**
	 * 
	 * @param string $table Name of the table
	 * @param string $uniqueId The unique column that increases automatically.
	 * @param string $columns Comma-separated list of columns (optional, default is *)
	 */
	public function __construct($config)
	{
		// Get the database instance
		$this->DB = DB::getInstance();

		if (isset($config["table"]))
			$this->table = $config["table"];
		if (isset($config["uniqueID"]))
			$this->uniqueId = $config["uniqueID"];
		if (isset($config["columns"]))
			$this->columns = $config["columns"];
	}


	/**
	 * READ table from the database. 
	 * 
	 * @param string $where MySQL WHERE clause (optional)
	 * @param string $groupBy MySQL GROUP BY clause (optional)
	 * @param string $orderBy MySQL ORDER BY clause (optional)
	 * @param string $limit MySQL LIMIT clause (optional)
	 * @return int $count
	 */
	public function read($where = "", $groupBy = "", $orderBy = "", $limit = "") : int
	{
		$statement = [
			"columns" => $this->columns,
			"from" => $this->table
		];
		if ($where) {
			$statement = array_merge($statement, ["where" => $where]);
		}
		if ($groupBy) {
			$statement = array_merge($statement, ["groupBy" => $groupBy]);
		}
		if ($orderBy) {
			$statement = array_merge($statement, ["orderBy" => $orderBy]);
		}
		if ($limit) {
			$statement = array_merge($statement, ["limit" => $limit]);
		}
		$this->recordset = $this->DB->select($statement);
		
		return $this->count();
	}


	/**
	 * CREATE a new record
	 * 
	 * @return boolean $inserted
	 */
	public function create() : ?ServiceItem
	{
		$values = func_get_args();
		$colsArr = explode(",", $this->columns);
		$columns = array_map('trim', $colsArr);

		if (($key = array_search($this->uniqueId, $columns)) !== false) {
			unset($columns[$key]);
		}

		$vSize = count($values);
		$cSize = count($columns);

		if ($vSize < $cSize) {
			for ($n = $vSize; $n < $cSize; $n++) {
				$values[$n] = "";
			}
		}

		$insertId = $this->DB->insert([
			"into" => $this->table,
			"columns" => implode(",", $columns),
			"values" => implode(",", $values)
		]);

		return $this->getByUniqueId($insertId);
	}


	/**
	 * Returns the records count.
	 * 
	 * @return int $count
	 */
	public function count() : int
	{
		return $this->recordset->getRecordCount();
	}
	
	
	/**
	 * Get a single record by the given column and its value.
	 * 
	 * @param string|int $value Value
	 * @return object $serviceItem
	 */
	public function getByUniqueId($value) : ?ServiceItem
	{
		$this->recordset = $this->DB->select([
			"columns" => $this->columns,
			"from" => $this->table,
			"where" => "{$this->uniqueId}='$value'"
		]);
		
		return $this->next($this->uniqueId, $value);
	}
	
	
	/**
	 * Get a single record by the given column and its value.
	 * 
	 * @param string $key Column
	 * @param string|int $value Value
	 * @return object $serviceItem
	 */
	public function getByKey($key, $value) : ?ServiceItem
	{
		$this->recordset = $this->DB->select([
			"columns" => $this->columns,
			"from" => $this->table,
			"where" => "$key='$value'"
		]);
		
		return $this->next($key, $value);
	}
	
	/**
	 * Creates the model of the data provided by the service.
	 * Cannot be executed before useService() has been run.
	 * @return object|null $serviceItem
	 * @throws \Exception
	 */
	public function next($key = "", $value = "") : ?ServiceItem
	{
		if (!$this->recordset) {
			throw new \Exception('No collection found! Create a recordset first.');
		}
		if ($record = $this->recordset->next()) {
			// Getting name of child class (our service)
			$serviceItem = get_class($this) . "Item";
			return new $serviceItem($this->DB, $record, $this->table, $key, $value);
		} else {
			$this->recordset->reset();
			return null;
		}
	}


}


/**
 * Class ServiceItem
 * @package makeup\lib
 */
class ServiceItem
{
    private $DB = null;
    private $record = null;
    private $table = "";
    private $key = "";
    private $value = "";


    /**
     * ServiceItem constructor.
     * 
     * @param object $db Database
     * @param object $record Single record
     * @param object $table Table name
     * @param object $key Column name
     * @param object $value Column value
     */
    public function __construct($db, $record, $table, $key, $value)
    {
        $this->DB = $db;
        $this->record = $record;
        $this->table = $table;
        $this->key = $key;
        $this->value = $value;
    }


    /**
     * Access a property.
     * 
     * @param string $item
     * @return string $value
     */
    public function getProperty($item)
    {
        return isset($this->record->$item) ? $this->record->$item : null;
    }


    /**
     * Change the value of a property.
     * 
     * @param string $item
     * @param string $value
     */
    public function setProperty($item, $value) : void
    {
        $this->record->$item = $value;
    }


    /**
     * Update the record.
     * 
     * @return boolean $updated
     */
    public function update() : bool
    {
        $set = [];

        foreach($this->record as $item => $value) {
            $set[] = "$item='$value'";
        }

        if (!empty($set)) {
            return $this->DB->update([
                "table" => $this->table,
                "set" => implode(", ", $set),
                "where" => $this->key . "=" . $this->value
            ]);
        }
        
        return false;
    }


    /**
     * Delete a record.
     * 
     * @return boolean $deleted
     */
    public function delete() : bool
    {
        return $this->DB->delete([
            "from" => $this->table,
            "where" => $this->key . "=" . $this->value
        ]);
    }
}
