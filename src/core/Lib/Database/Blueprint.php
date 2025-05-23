<?php
declare(strict_types=1);
namespace Core\Lib\Database;

use Core\DB;
use Exception;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Logging\Logger;

/**
 * Handles schema definitions before executing them.
 */
class Blueprint {
    protected $allowPrimaryDropFlag = false;
    protected $columns = [];
    protected $engine = 'InnoDB';
    protected $dbDriver;
    protected $foreignKeys = [];
    protected $indexes = [];
    protected $table;

    /**
     * Constructor for Blueprint class.
     *
     * @param string $table The name of the table to be modified.
     */
    public function __construct(string $table) {
        $this->table = $table;
        $this->dbDriver = DB::getInstance()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public function allowPrimaryDrop(): Blueprint {
        $this->allowPrimaryDropFlag = true;
        return $this;
    }
    
    /**
     * Define a big integer column.
     * 
     * @param string $name The name of the column to be created as BIGINT.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function bigInteger(string $name): Blueprint {
        $this->columns[] = "{$name} BIGINT";
        return $this;
    }

    /**
     * Define a boolean column.
     * 
     * @param string $name The name of the column to be created as TINYINT(1).
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function boolean(string $name): Blueprint {
        $this->columns[] = "{$name} TINYINT(1)";
        return $this;
    }

    /**
     * Create the table.
     */
    public function create(): void {
        $columnsSql = implode(", ", $this->columns);
        
        if ($this->dbDriver === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->table} ({$columnsSql}) ENGINE={$this->engine}";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->table} ({$columnsSql})";
        }
        
        DB::getInstance()->query($sql);
        Tools::info("SUCCESS: Creating Table {$this->table}");

        foreach ($this->indexes as $index) {
            $this->createIndex($index);
        }
        
        foreach ($this->foreignKeys as $fk) {
            $this->createForeignKey($fk);
        }
    }

    /**
     * Create a foreign key (MySQL only).
     *
     * @param string $fk The full SQL statement to create the foreign key constraint.
     *                   This should be a valid `ALTER TABLE` query for adding a foreign key.
     *                   Example: "ALTER TABLE posts ADD FOREIGN KEY (user_id) REFERENCES users(id)"
     * @return void
     */
    protected function createForeignKey(string $fk): void {
        if ($this->dbDriver === 'mysql') {
            DB::getInstance()->query($fk);
            Tools::info("SUCCESS: Adding Foreign Key To {$this->table}");
        }
    }

    /**
    * Create an index on a specific column.
    *
    * @param string $column The name of the column to create an index for.
    *                       Index will be named as `{table}_{column}_idx` in SQLite,
    *                       and will be created via `ALTER TABLE` in MySQL.
    * @return void
    */
    protected function createIndex(string $column): void {
        $sql = ($this->dbDriver === 'sqlite')
            ? "CREATE INDEX IF NOT EXISTS {$this->table}_{$column}_idx ON {$this->table} ({$column})"
            : "ALTER TABLE {$this->table} ADD INDEX ({$column})";

        DB::getInstance()->query($sql);
        Tools::info("SUCCESS: Adding Index {$column} To {$this->table}");
    }

    /**
     * Define a date column.
     * 
     * @param string $name The name of the column to be created as DATE.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function date(string $name): Blueprint {
        $this->columns[] = "{$name} DATE";
        return $this;
    }

    /**
     * Define a datetime column.
     * 
     * @param string $name The name of the column to be created as DATETIME.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function dateTime(string $name): Blueprint {
        $this->columns[] = "{$name} DATETIME";
        return $this;
    }

    /**
     * Define a decimal column.
     * 
     * @param string $name The name of the column.
     * @param int $precision Total number of digits.
     * @param int $scale Number of digits after the decimal.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function decimal(string $name, int $precision = 8, int $scale = 2): Blueprint {
        $this->columns[] = "{$name} DECIMAL({$precision}, {$scale})";
        return $this;
    }

    /**
     * Adds a DEFAULT value to the last defined column.
     *
     * This method appends a default value to the most recently added column
     * in the schema definition. It supports string, integer, float, and boolean
     * values. If no columns have been added yet, it throws an exception.
     * In SQLite, default values for certain column types like TEXT and BLOB are skipped.
     *
     * @param string|int|float|bool $value The default value to assign to the last defined column.
     *                                     Strings will be wrapped in quotes. Other types will be cast directly.
     * @return Blueprint Returns the current Blueprint instance for method chaining.
     *
     * @throws Exception If no column has been defined yet or the column type cannot be determined.
     */
    public function default(string|int|float|bool $value): Blueprint {
        $lastIndex = count($this->columns) - 1;

        if ($lastIndex < 0) {
            throw new Exception("Cannot apply default value without a defined column.");
        }

        preg_match('/^(\w+)\s+([\w()]+)/', $this->columns[$lastIndex], $matches);

        if (!isset($matches[2])) {
            throw new Exception("Could not extract column type.");
        }

        $columnType = Str::upper($matches[2]);

        if ($this->dbDriver === 'sqlite' && Arr::exists(['TEXT', 'BLOB'], $columnType)) {
            Logger::log("Skipping default value for column '{$matches[1]}' (type: $columnType) in SQLite.", 'warning');
            return $this;
        }

        $this->columns[$lastIndex] .= " DEFAULT " . (is_string($value) ? "'$value'" : $value);
        return $this;
    }
    
    public function dropColumns(array|string $columns) {
        $columnString = '';
        $columnList = '';
        $drop = 'DROP ';
        $db = DB::getInstance();

        $indexResults = $db->query("SHOW INDEXES FROM {$this->table}")->results();
        foreach($indexResults as $results) {
            $r = $results->Column_name;
            Tools::info("results: $r");
        }
        if(Arr::isArray($columns)) {
            $last = end($columns);
            // if(Arr::exists())
            foreach($columns as $column) {
                $this->isPrimaryKey($column);
                $columnString .= ($last === $column) ? $drop . $column : $drop . $column . ', ';
                $columnList .=  ($last === $column) ? $column : $column . ', ';
            }
        } else {
            $this->isPrimaryKey($columns);
            $columnString .= $drop . $columns;
            $columnList = $columns;
        }
        
        $sql = "ALTER TABLE {$this->table}
             {$columnString}";
        Tools::info($sql);
        $db->query($sql);
        Tools::info("The column(s) {$columnList} have been dropped from the '{$this->table}' table.");
        // return $this;
    }

    /**
     * Drops a table if it exists.
     *
     * @param string $table The name of the table to drop if it exists.
     * @return void
     */
    public function dropIfExists(string $table): void {
        $sql = "DROP TABLE IF EXISTS {$table}";
        DB::getInstance()->query($sql);
        Tools::info("SUCCESS: Dropping Table {$table}");
    }

    /**
     * Define a double column.
     * 
     * @param string $name The name of the column to be created as DOUBLE.
     * @param int $precision Total number of digits.
     * @param int $scale Number of digits after the decimal.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function double(string $name, int $precision = 16, int $scale = 4): Blueprint {
        $this->columns[] = "{$name} DOUBLE({$precision}, {$scale})";
        return $this;
    }

    /**
     * Define an enum column (MySQL only).
     * 
     * @param string $name The name of the enum column.
     * @param array $values An array of allowed values for the ENUM.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function enum(string $name, array $values): Blueprint {
        if ($this->dbDriver === 'mysql') {
            $enumValues = implode("','", $values);
            $this->columns[] = "{$name} ENUM('{$enumValues}')";
        } else {
            $this->columns[] = "{$name} TEXT";
        }
        return $this;
    }

    /**
     * Define a float column.
     * 
     * @param string $name The name of the column to be created as FLOAT.
     * @param int $precision Total number of digits.
     * @param int $scale Number of digits after the decimal.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function float(string $name, int $precision = 8, int $scale = 2): Blueprint {
        $this->columns[] = "{$name} FLOAT({$precision}, {$scale})";
        return $this;
    }

    /**
     * Define a foreign key (MySQL only).
     * 
     * @param string $column The column name to add a foreign key constraint on.
     * @param string $references The column name in the foreign table being referenced.
     * @param string $onTable The name of the table being referenced.
     * @param string $onDelete Action on delete (e.g., CASCADE).
     * @param string $onUpdate Action on update (e.g., CASCADE).
     */
    public function foreign(
        string $column, 
        string $references, 
        string $onTable, 
        string $onDelete = 'CASCADE', 
        string $onUpdate = 'CASCADE'
    ): void {
        if ($this->dbDriver === 'mysql') {
            $this->foreignKeys[] = "ALTER TABLE {$this->table} ADD FOREIGN KEY ({$column}) REFERENCES {$onTable}({$references}) ON DELETE {$onDelete} ON UPDATE {$onUpdate}";
        }
    }

    /**
     * Add an ID column (primary key).
     */
    public function id() {
        $type = ($this->dbDriver === 'sqlite') ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";
        $this->columns[] = "id {$type}";
    }

    /**
     * Define an index.
     * 
     * @param string $column The column name to add an index on.
     */
    public function index(string $column): void {
        $this->indexes[] = $column;
    }

    /**
     * Tests if field is a primary key.  If true then reports to console and 
     * stops execution of command.
     *
     * @param string $column The name of the field we want to test.
     * @return void
     */
    private function isPrimaryKey(string $column): Blueprint {
        $isPrimaryKey = false;
        if($this->dbDriver === 'mysql') {
            $sql = "SHOW KEYS FROM {$this->table} WHERE Key_name = 'PRIMARY'";
            $results = DB::getInstance()->query($sql)->results();

            foreach($results as $row) {
                $isPrimaryKey = ($row->Column_name === $column) ? true : false;
            }
        } else if($this->dbDriver === 'sqlite') {
            $sql = "PRAGMA table_info({$this->table})";
            $results = DB::getInstance()->query($sql)->results();

            foreach($results as $row) {
                $isPrimaryKey = ($row->Column_name === $column) ? true : false;
            }
        }

        if($isPrimaryKey && !$this->allowPrimaryDropFlag) {
            Tools::info("Cannot modify a PRIMARY KEY {$column} from {$this->table}", 'debug', 'yellow');
            die();
        }
        return $this;
    }

    /**
     * Define an integer column.
     * 
     * @param string $name The name of the column to be created as INT.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function integer(string $name): Blueprint {
        $type = ($this->dbDriver === 'sqlite') ? "INTEGER" : "INT";
        $this->columns[] = "{$name} {$type}";
        return $this;
    }

    /**
     * Define a medium integer column.
     * 
     * @param string $name The name of the column to be created as MEDIUMINT.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function mediumInteger(string $name): Blueprint {
        $this->columns[] = "{$name} MEDIUMINT";
        return $this;
    }

    /**
     * Modifies last column added to the schema and make it nullable.
     *
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function nullable(): Blueprint {
        $lastIndex = count($this->columns) - 1;
        if ($lastIndex >= 0) {
            $this->columns[$lastIndex] .= " NULL";
        }
        return $this;  // Allow chaining
    }

    /**
     * Renames a particular column
     *
     * @param string $from The column's original name.
     * @param string $to The column's new name.
     * @return void
     */
    public function renameColumn(string $from, string $to): void {
        $sql = "ALTER TABLE {$this->table}
            RENAME COLUMN {$from} TO {$to}";
        Db::getInstance()->query($sql);
        Tools::info("Table {$from} renamed to {$to}");
    }

    /**
     * Define a small integer column.
     * 
     * @param string $name The name of the column to be created as SMALLINT.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function smallInteger(string $name): Blueprint {
        $this->columns[] = "{$name} SMALLINT";
        return $this;
    }

    /**
     * Define a soft delete column.
     * 
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function softDeletes(): Blueprint {
        $this->columns[] = "deleted TINYINT(1)";
        return $this;
    }

    /**
     * Define a string column.
     * 
     * @param string $name The name of the column.
     * @param int $length The maximum length of the string column.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function string(string $name, int $length = 255): Blueprint {
        $type = ($this->dbDriver === 'sqlite') ? "TEXT" : "VARCHAR({$length})";
        $this->columns[] = "{$name} {$type}";
        return $this;
    }

    /**
     * Define a text column.
     * 
     * @param string $name The name of the column to be created as TEXT.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function text(string $name): Blueprint {
        $this->columns[] = "{$name} TEXT";
        return $this;
    }

    /**
     * Define a time column.
     * 
     * @param string $name The name of the column to be created as TIME.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function time(string $name): Blueprint {
        $this->columns[] = "{$name} TIME";
        return $this;
    }

    /**
     * Define a timestamp column.
     * 
     * @param string $name The name of the column to be created as TIMESTAMP.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function timestamp(string $name): Blueprint {
        $this->columns[] = "{$name} TIMESTAMP";
        return $this;
    }

    /**
     * Define timestamps (created_at and updated_at).
     */
    public function timestamps(): void {
        $this->columns[] = "created_at DATETIME";
        $this->columns[] = "updated_at DATETIME";
    }

    /**
     * Define a tiny integer column.
     * 
     * @param string $name The name of the column to be created as TINYINT or INTEGER depending on DB driver.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function tinyInteger(string $name): Blueprint {
        $type = ($this->dbDriver === 'sqlite') ? "INTEGER" : "TINYINT";
        $this->columns[] = "{$name} {$type}";
        return $this;
    }

    /**
     * Define an unsigned integer column (MySQL only).
     * 
     * @param string $name The name of the column to be created as unsigned INT (MySQL) or INTEGER.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function unsignedInteger(string $name): Blueprint {
        if ($this->dbDriver === 'mysql') {
            $this->columns[] = "{$name} INT UNSIGNED";
        } else {
            $this->columns[] = "{$name} INTEGER";
        }
        return $this;
    }

    /**
     * Update an existing table.
     */
    public function update(): void {
        foreach ($this->columns as $column) {
            $sql = "ALTER TABLE {$this->table} ADD COLUMN {$column}";
            DB::getInstance()->query($sql);
            Tools::info("SUCCESS: Adding Column {$column} To {$this->table}");
        }

        foreach ($this->indexes as $index) {
            $this->createIndex($index);
        }
    }

    /**
     * Define a UUID column (MySQL only).
     * 
     * @param string $name The name of the column to be created as UUID.
     */
    public function uuid(string $name) {
        if ($this->dbDriver === 'mysql') {
            $this->columns[] = "{$name} CHAR(36) NOT NULL";
        } else {
            $this->columns[] = "{$name} TEXT";
        }
    }
}