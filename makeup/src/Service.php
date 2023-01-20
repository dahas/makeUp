<?php declare(strict_types=1);

namespace makeUp\src;

use ReflectionClass;
use stdClass;


abstract class Service {
	protected $DB = null;
	protected $recordset = null;
	protected $table = "";
	protected $key = "";
	protected $columns = "*";

	public function __construct()
	{
		// Get the database instance
		$this->DB = DB::getInstance();

		$rc = new ReflectionClass(get_class($this));
		foreach ($rc->getAttributes() as $attribute) {
			foreach ($attribute->newInstance() as $name => $property) {
				$this->$name = $property;
			}
		}
	}

	public function isAvailable(): bool
	{
		return $this->DB->isAvailable();
	}

	public function read(string $where = "", string $groupBy = "", string $orderBy = "", string $limit = ""): int
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

	public function create(): ? ServiceItem
	{
		$serviceItem = get_class($this) . "Item";
		return new $serviceItem($this->DB, new stdClass(), $this->table);
	}

	public function count(): int
	{
		return $this->recordset ? $this->recordset->getRecordCount() : 0;
	}

	public function getByUniqueId(string|int $value): ? ServiceItem
	{
		$this->recordset = $this->DB->select([
			"columns" => $this->columns,
			"from" => $this->table,
			"where" => "{$this->key}='$value'"
		]);

		return $this->next($this->key, $value);
	}

	public function getByKey(string $key, string $value): ? ServiceItem
	{
		$this->recordset = $this->DB->select([
			"columns" => $this->columns,
			"from" => $this->table,
			"where" => "$key='$value'"
		]);

		return $this->next($key, $value);
	}

	public function next(string $key = "", mixed $value = null): ? ServiceItem
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


class ServiceItem {
	private $DB = null;
	private $record = null;
	private $table = "";
	private $key = "";
	private $value = "";

	public function __construct(DB $db, ?object $record, string $table, string $key = "", mixed $value = null)
	{
		$this->DB = $db;
		$this->record = $record;
		$this->table = $table;
		$this->key = $key;
		$this->value = $value;
	}

	public function getProperties(): array
	{
		return (array) $this->record;
	}

	public function getProperty(string $item): mixed
	{
		return isset($this->record->$item) ? $this->record->$item : null;
	}

	public function setProperty(string $item, mixed $value): void
	{
		$this->record->$item = $value;
	}

	public function store(): int
	{
		$columns = [];
		$values = [];
		foreach ($this->record as $column => $value) {
			$columns[] = $column;
			$values[] = $value;
		}
		$insertId = $this->DB->insert([
			"into" => $this->table,
			"columns" => implode(",", $columns),
			"values" => implode(",", $values)
		]);
		return $insertId;
	}

	public function update(): bool
	{
		$set = [];

		foreach ($this->record as $item => $value) {
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

	public function delete(): bool
	{
		return $this->DB->delete([
			"from" => $this->table,
			"where" => $this->key . "=" . $this->value
		]);
	}
}