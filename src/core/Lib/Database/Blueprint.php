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
    protected $columns = [];
    protected $engine = 'InnoDB';
    protected $dbDriver;
    protected $foreignKeys = [];
    protected $indexes = [];
    protected ?string $lastColumn = null;
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
    
    /**
     * Define a big integer column.
     * 
     * @param string $name The name of the column to be created as BIGINT.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function bigInteger(string $name): Blueprint {
        $this->columns[] = "{$name} BIGINT";
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
     * Define an index on one or more columns.
     *
     * This method registers a standard (non-unique) index to be created
     * after the table is created. The index will be applied to the given column
     * or set of columns. The index name is automatically generated as
     * `{table}_{column}_idx` for a single column or based on the provided structure.
     *
     * Note: Actual SQL index creation occurs in the `create()` method via `createIndex()`.
     *
     * @param string|array $column The name of the column or an array of columns to index.
     *                              If a string is provided, a default index name will be generated.
     * @return void
     */
    protected function createIndex(array|string $index): void {
        if (is_string($index)) {
            $indexName = "{$this->table}_{$index}_idx";
            $sql = ($this->dbDriver === 'sqlite')
                ? "CREATE INDEX IF NOT EXISTS {$indexName} ON {$this->table} ({$index})"
                : "ALTER TABLE {$this->table} ADD INDEX ({$index})";

            DB::getInstance()->query($sql);
            Tools::info("SUCCESS: Adding Index {$index} To {$this->table}");
        } else {
            $columns = implode(', ', array_map(fn($col) => "`$col`", $index['columns']));
            $sql = match ($index['type']) {
                'unique' => "CREATE UNIQUE INDEX `{$index['name']}` ON `{$this->table}` ({$columns})",
                default => "CREATE INDEX `{$index['name']}` ON `{$this->table}` ({$columns})",
            };

            DB::getInstance()->query($sql);
            Tools::info("SUCCESS: Adding Index {$index['name']} To {$this->table}");
        }
    }

    /**
     * Define a date column.
     * 
     * @param string $name The name of the column to be created as DATE.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function date(string $name): Blueprint {
        $this->columns[] = "{$name} DATE";
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
    
    /**
     * Drops a column or group of columns.  If a column has a restraint 
     * then warnings are presented to the user.
     * @param array|string $columns An individual column or an array of 
     * columns to drop.
     * @return void
     */
    public function dropColumns(array|string $columns): void {
        if($columns === '' || (Arr::isArray($columns) && Arr::isEmpty($columns))) {
            Tools::info('Column argument can\'t be an empty string or an empty array', 'debug', 'yellow');
            return;
        }

        $columnString = '';
        $columnList = '';
        $drop = 'DROP ';
        $db = DB::getInstance();
        
        if(Arr::isArray($columns)) {
            $columnsConstrained = false;
            $last = end($columns);
            foreach($columns as $column) {
                if($column === '') {
                    Tools::info('Array contains empty string.', 'debug', 'yellow');
                    continue;
                }
                $columnsConstrained = $this->isPrimaryKey($column) || $this->isIndex($column) || $this->isUnique($column);
                if(!$columnsConstrained) {
                    $columnString .= ($last === $column) ? $drop . $column : $drop . $column . ', ';
                    $columnList .=  ($last === $column) ? $column : $column . ', ';
                } 
                if($columnsConstrained && $column == $last) {
                    $columnString = substr($columnString, 0, -2);
                    $columnList = substr($columnList, 0, -2);
                }
            }
        } else {
            if($this->isPrimaryKey($columns) || $this->isIndex($columns) || $this->isUnique($columns)) return;
            $columnString .= $drop . $columns;
            $columnList = $columns;
        }
        
        $sql = "ALTER TABLE {$this->table}
                {$columnString}";
        Tools::info($sql);
        $db->query($sql);
        Tools::info("The column(s) {$columnList} have been dropped from the '{$this->table}' table.");
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

    public function dropForeign(): void {}

    /**
     * Drops indexed value from table.
     *
     * @param string $column The name of the column to be dropped.
     * @return void
     */
    public function dropIndex(string $column): void {
        if(!$this->isIndex($column)) {
            Tools::info("'{$column}' is not an indexed field.  Skipping operation.");
            return;
        }

        if($column === '') {
            Tools::info("Column argument can't be an empty string");
            return;
        }

        if($this->dbDriver === 'sqlite') {
            $sql = "DROP INDEX IF EXISTS {$column}";
        } else {
            $sql = "DROP INDEX {$column} on {$this->table}";
        }

        DB::getInstance()->query($sql);
        $this->dropColumns($column);
        Tools::info("Dropped the indexed value {$column} from the {$this->table} table.");
    }

    /**
     * Drops primary key field from table.
     *
     * @param string $column The name of the column to be dropped.
     * @return void
     */
    public function dropPrimaryKey(string $column): void {
        if(!$this->isPrimaryKey($column)) {
            Tools::info("'{$column}' is not a primary key.  Skipping operation.");
            return;
        }

        if($column === '') {
            Tools::info("Column argument can't be an empty string");
            return;
        }

        $sql = "ALTER TABLE {$this->table} MODIFY {$column} INT"; // remove AUTO_INCREMENT
        $db = DB::getInstance();
        $db->getInstance()->query($sql);
        $sql = "ALTER TABLE {$this->table} DROP PRIMARY KEY";
        $db->getInstance()->query($sql);
        Tools::info("The primary key for this table has been dropped.");
    }

    /**
     * Drops column with unique constraint from table.
     *
     * @param string $column The name of the column to be dropped.
     * @return void
     */
    public function dropUnique(string $column): void {
        if ($column === '') {
            Tools::info("Column argument can't be an empty string", 'debug', 'yellow');
            return;
        }

        if (!$this->isUnique($column)) {
            Tools::info("'{$column}' does not have a unique constraint. Skipping operation.", 'debug', 'yellow');
            return;
        }

        if ($this->dbDriver === 'mysql') {
            // Get the actual unique index name for this column
            $sql = "SELECT INDEX_NAME FROM information_schema.STATISTICS 
                    WHERE table_schema = DATABASE() 
                    AND table_name = '{$this->table}' 
                    AND column_name = '{$column}' 
                    AND non_unique = 0 
                    LIMIT 1";
            $result = DB::getInstance()->query($sql)->first();

            if (!$result) {
                Tools::info("No unique index found for column '{$column}' on table '{$this->table}'", 'debug', 'yellow');
                return;
            }

            $indexName = $result->INDEX_NAME;

            $dropSql = "ALTER TABLE {$this->table} DROP INDEX `{$indexName}`";
            DB::getInstance()->query($dropSql);
            $this->dropColumns($column);
            Tools::info("Dropped UNIQUE index '{$indexName}' for column '{$column}' from '{$this->table}'");

        } elseif ($this->dbDriver === 'sqlite') {
            // In SQLite, find the name of the unique index
            $indexList = DB::getInstance()->query("PRAGMA index_list('{$this->table}')")->results();

            foreach ($indexList as $index) {
                if ($index->unique == 1) {
                    $indexName = $index->name;

                    // Get column(s) associated with this index
                    $indexInfo = DB::getInstance()->query("PRAGMA index_info('{$indexName}')")->results();
                    foreach ($indexInfo as $colInfo) {
                        if ($colInfo->name === $column) {
                            // Found a unique index on this column — drop it
                            $dropSql = "DROP INDEX IF EXISTS `{$indexName}`";
                            DB::getInstance()->query($dropSql);
                            $this->dropColumns($column);
                            Tools::info("Dropped UNIQUE index '{$indexName}' for column '{$column}' from '{$this->table}'");
                            return;
                        }
                    }
                }
            }

            Tools::info("No unique index found for '{$column}' in SQLite table '{$this->table}'", 'debug', 'yellow');
        }
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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
     * Tests if field is an index.  If true then reports to console.
     *
     * @param string $column The name of the field we want to test.
     * @return bool True if value is an index and otherwise we return false.
     */
    private function isIndex(string $column): bool {
        $isIndex = false;
        if($this->dbDriver === 'mysql') {
            $sql = "SHOW INDEX FROM {$this->table}";
            $results = DB::getInstance()->query($sql)->results();
            foreach($results as $row) {       
                $isIndex = ($row->Column_name === $column) ? true : false;
            }
        } else if($this->dbDriver === 'sqlite') {
            $sql = "PRAGMA table_info({$this->table})";
            $results = DB::getInstance()->query($sql)->results();
            foreach($results as $row) {
                $isIndex = ($row->pk == 1) ? false : true;
            }
        }

        if($isIndex) {
            Tools::info("Cannot modify the INDEX {$column} from {$this->table}.  If you are using the dropIndex function this message can be ignored.", 'debug', 'yellow');
        }
        return $isIndex;
    }

    /**
     * Tests if field is a primary key.  If true then reports to console.
     *
     * @param string $column The name of the field we want to test.
     * @return bool True if value is a primary key and otherwise we return 
     * false.
     */
    private function isPrimaryKey(string $column): bool {
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
                $isPrimaryKey = ($row->pk == 1) ? false : true;
            }
        }

        if($isPrimaryKey) {
            Tools::info("Cannot modify the PRIMARY KEY {$column} from {$this->table}.  If you are using the dropPrimaryKey function this message can be ignored.", 'debug', 'yellow');
        }
        return $isPrimaryKey;
    }

    /**
     * Tests if a column has a UNIQUE constraint.
     *
     * If true, reports to the console. This helps avoid modifying unique-indexed columns
     * unintentionally during schema changes like dropping or renaming.
     *
     * @param string $column The name of the column to check.
     * @return bool True if the column is unique, false otherwise.
     */
    private function isUnique(string $column): bool {
        $isUnique = false;

        if ($this->dbDriver === 'mysql') {
            $sql = "SHOW INDEX FROM {$this->table} WHERE Non_unique = 0 AND Column_name = '{$column}'";
            $results = DB::getInstance()->query($sql)->results();

            foreach ($results as $row) {
                if ($row->Column_name === $column) {
                    $isUnique = true;
                    break;
                }
            }
        } else if ($this->dbDriver === 'sqlite') {
            // Get all unique indexes on the table
            $indexList = DB::getInstance()->query("PRAGMA index_list('{$this->table}')")->results();

            foreach ($indexList as $index) {
                if ($index->unique == 1) {
                    // Check if the column is part of the unique index
                    $indexInfo = DB::getInstance()->query("PRAGMA index_info('{$index->name}')")->results();
                    foreach ($indexInfo as $colInfo) {
                        if ($colInfo->name === $column) {
                            $isUnique = true;
                            break 2; // Break both loops
                        }
                    }
                }
            }
        }

        if ($isUnique) {
            Tools::info("Cannot modify the UNIQUE constraint on {$column} from {$this->table}.  If you are using the dropUnique function this message can be ignored.", 'debug', 'yellow');
        }

        return $isUnique;
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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
        $isConstrained = $this->isPrimaryKey($from) || $this->isIndex($from) || $this->isUnique($from);
        if(!$isConstrained) {
            $sql = "ALTER TABLE {$this->table}
                RENAME COLUMN {$from} TO {$to}";
            Db::getInstance()->query($sql);
            Tools::info("Table {$from} renamed to {$to}");
        } else {
            Tools::info("The field {$from} is a constrained column.  Make sure you drop any constraints before renaming this column.", 'debug', 'yellow');
        }
    }

    /**
     * Define a small integer column.
     * 
     * @param string $name The name of the column to be created as SMALLINT.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function smallInteger(string $name): Blueprint {
        $this->columns[] = "{$name} SMALLINT";
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
        return $this;
    }

    /**
     * Adds a unique index to the last defined column.
     *
     * @return Blueprint
     * @throws Exception if no column has been defined yet.
     */
    public function unique(): Blueprint {
        if(!$this->lastColumn) {
            throw new Exception("Cannot apply unique index with a defined column.");
        }

        $indexName = "uniq_{$this->table}_{$this->lastColumn}";
        $this->indexes[] = [
            'type' => 'unique',
            'name' => $indexName,
            'columns' => [$this->lastColumn]
        ];

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
        $this->lastColumn = $name;
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
        $this->lastColumn = $name;
    }
}