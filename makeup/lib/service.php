<?php

namespace makeUp\lib;


abstract class Service
{
	protected $DB = null;
	protected $recordset = null;
	protected $table = "";
	protected $uniqueId = "";
	protected $columns = "*";

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

	public function read(string $where = "", string $groupBy = "", string $orderBy = "", string $limit = "") : int
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

	public function count() : int
	{
		return $this->recordset->getRecordCount();
	}
	
	public function getByUniqueId(string|int $value) : ?ServiceItem
	{
		$this->recordset = $this->DB->select([
			"columns" => $this->columns,
			"from" => $this->table,
			"where" => "{$this->uniqueId}='$value'"
		]);
		
		return $this->next($this->uniqueId, $value);
	}
	
	public function getByKey(string $key, string $value) : ?ServiceItem
	{
		$this->recordset = $this->DB->select([
			"columns" => $this->columns,
			"from" => $this->table,
			"where" => "$key='$value'"
		]);
		
		return $this->next($key, $value);
	}
	
	public function next(string $key = "", string $value = "") : ?ServiceItem
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


class ServiceItem
{
    private $DB = null;
    private $record = null;
    private $table = "";
    private $key = "";
    private $value = "";

    public function __construct(DB $db, ?object $record, string $table, string $key, string $value)
    {
        $this->DB = $db;
        $this->record = $record;
        $this->table = $table;
        $this->key = $key;
        $this->value = $value;
    }

    public function getProperty(string $item) : mixed
    {
        return isset($this->record->$item) ? $this->record->$item : null;
    }

    public function setProperty(string $item, string|int $value) : void
    {
        $this->record->$item = $value;
    }

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

    public function delete() : bool
    {
        return $this->DB->delete([
            "from" => $this->table,
            "where" => $this->key . "=" . $this->value
        ]);
    }
}
