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
    protected array $columnModifiers = [];
    protected $dbDriver;
    protected $engine = 'InnoDB';
    protected $foreignKeys = [];
    protected $indexes = [];
    protected ?string $lastColumn = null;
    protected array $primaryKeys = [];
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
     * Specifies the position of the last defined column to appear 
     * immediately after another column in the table (MySQL only).
     *
     * This method is only meaningful during ALTER TABLE operations
     * (i.e., when calling $table->update()). The generated SQL will
     * include an `AFTER column_name` clause to control column order.
     *
     * Example:
     * $table->string('nickname')->after('last_name');
     * // Produces: ALTER TABLE users ADD COLUMN nickname VARCHAR(255) AFTER last_name;
     *
     * @param string $column The existing column name after which the new column should be added.
     * @return Blueprint Returns the current Blueprint instance for method chaining.
     */
    public function after(string $column): Blueprint {
        if ($this->lastColumn) {
            $this->columnModifiers[$this->lastColumn]['after'] = $column;
        }
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
        
        $this->setForeignKeys();
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
                $columnsConstrained = $this->isPrimaryKey($column) || $this->isIndex($column) || 
                    $this->isUnique($column) || $this->isForeignKey($column);
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
            if($this->isPrimaryKey($columns) || $this->isIndex($columns) || 
                $this->isUnique($columns) || $this->isForeignKey($columns)) {
                return;
            }

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
     * Drops a foreign key constraint from the table (MySQL only).
     *
     * @param string $column The name of the column to be dropped.
     * @param bool $preserveColumn When true only the foreign key constraint is 
     * removed.  If set to false the column is also dropped from the table.  
     * The default value is true.
     * @return void
     */
    public function dropForeign(string $column, bool $preserveColumn = true): void {
        if ($column === '') {
            Tools::info("Column argument can't be an empty string", 'debug', 'yellow');
            return;
        }

        if ($this->dbDriver === 'mysql') {
            $result = $this->getForeignKey($column);
            if (!$result) {
                Tools::info("No foreign key constraint found on column '{$column}'", 'debug', 'yellow');
                return;
            }

            $constraintName = $result->CONSTRAINT_NAME;
            $dropSql = "ALTER TABLE {$this->table} DROP FOREIGN KEY `{$constraintName}`";
            DB::getInstance()->query($dropSql);
            $this->dropIndex($column);
            if(!$preserveColumn) {
                $this->dropColumns($column);
            }
            Tools::info("Dropped FOREIGN KEY '{$constraintName}' on '{$column}' in '{$this->table}'");

        } elseif ($this->dbDriver === 'sqlite') {
            // SQLite does not support DROP CONSTRAINT or ALTER TABLE DROP FOREIGN KEY.
            // You must recreate the table without the foreign key.
            Tools::info("SQLite does not support dropping foreign keys directly. You must recreate the table.", 'debug', 'yellow');
        }
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
     * Drops indexed value from the table.
     *
     * @param string $column The name of the column to be dropped.
     * @param bool $preserveColumn When true only the index constraint is 
     * removed.  If set to false the column is also dropped from the table.  
     * The default value is true.
     * @return void
     */
    public function dropIndex(string $column, bool $preserveColumn = true): void {
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
        Tools::info("Dropped the indexed constraint for the {$column} column of the {$this->table} table.");
        if(!$preserveColumn) {
            $this->dropColumns($column);
        }
    }

    /**
     * Drops primary key field from the table.
     *
     * @param string $column The name of the column to be dropped.
     * @param bool $preserveColumn When true only the primary key constraint is 
     * removed.  If set to false the column is also dropped from the table.  
     * The default value is true.
     * @return void
     */
    public function dropPrimaryKey(string $column, bool $preserveColumn = true): void {
        if($column === '') {
            Tools::info("Column argument can't be an empty string");
            return;
        }

        if(!$this->isPrimaryKey($column)) {
            Tools::info("'{$column}' is not a primary key.  Skipping operation.");
            return;
        }

        $sql = "ALTER TABLE {$this->table} MODIFY {$column} INT"; // remove AUTO_INCREMENT
        $db = DB::getInstance();
        $db->getInstance()->query($sql);
        $sql = "ALTER TABLE {$this->table} DROP PRIMARY KEY";
        $db->getInstance()->query($sql);
        Tools::info("The primary key constraint for the '{$column}' column for the '{$this->table}' table has been dropped.");
        
        if(!$preserveColumn) {
            $this->dropColumns($column);
        }
    }

    /**
     * Drops column with unique constraint from the table.
     *
     * @param string $column The name of the column to be dropped.
     * @param bool $preserveColumn When true only the unique constraint is 
     * removed.  If set to false the column is also dropped from the table.  
     * The default value is true.
     * @return void
     */
    public function dropUnique(string $column, bool $preserveColumn = false): void {
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
            Tools::info("Dropped UNIQUE index '{$indexName}' for column '{$column}' from '{$this->table}'");

            if(!$preserveColumn) {
                $this->dropColumns($column);
            }

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
        string $referencedColumn,
        string $onTable,
        string $onDelete = 'RESTRICT',
        string $onUpdate = 'RESTRICT'
    ): void {
        $this->foreignKeys[] = [
            'column' => $column,
            'referenced_column' => $referencedColumn,
            'referenced_table' => $onTable,
            'on_delete' => strtoupper($onDelete),
            'on_update' => strtoupper($onUpdate),
        ];
    }

    /**
     * Determines if a particular column has a foreign key constraint (MySQL only).
     *
     * @param string $column The name of the column.
     * @return object The results returned from the database.
     */
    private function getForeignKey(string $column): object {
        $sql = "
            SELECT
                k.CONSTRAINT_NAME,
                k.COLUMN_NAME,
                k.REFERENCED_TABLE_NAME,
                k.REFERENCED_COLUMN_NAME,
                rc.UPDATE_RULE,
                rc.DELETE_RULE
            FROM
                information_schema.KEY_COLUMN_USAGE AS k
            JOIN
                information_schema.REFERENTIAL_CONSTRAINTS AS rc
                ON k.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND k.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
            WHERE
                k.TABLE_NAME = '{$this->table}'
                AND k.TABLE_SCHEMA = DATABASE()
                AND k.COLUMN_NAME = '{$column}'
            ";

        return DB::getInstance()->query($sql)->first();
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
     * Tests if a column is a foreign key.
     *
     * If true, reports to the console. Helps prevent unsafe renames or drops.
     *
     * @param string $column The name of the column to check.
     * @return bool True if the column has a foreign key constraint.
     */
    private function isForeignKey(string $column): bool {
        $isForeignKey = false;

        if ($this->dbDriver === 'mysql') {
            $sql = "SELECT COLUMN_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '{$this->table}' 
                    AND COLUMN_NAME = '{$column}' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL";

            $result = DB::getInstance()->query($sql)->results();

            foreach ($result as $row) {
                if ($row->COLUMN_NAME === $column) {
                    $isForeignKey = true;
                    break;
                }
            }
        } elseif ($this->dbDriver === 'sqlite') {
            $sql = "PRAGMA foreign_key_list('{$this->table}')";
            $results = DB::getInstance()->query($sql)->results();

            foreach ($results as $row) {
                if ($row->from === $column) {
                    $isForeignKey = true;
                    break;
                }
            }
        }

        if ($isForeignKey) {
            Tools::info("Cannot modify FOREIGN KEY '{$column}' from '{$this->table}'.  If you are using the dropForeign function this message can be ignored.", 'debug', 'yellow');
        }

        return $isForeignKey;
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
     * Specify one or more columns to be used as the primary key for the table.
     *
     * This method does not immediately execute any SQL. Instead, it stores the
     * provided column(s) so that the `create()` method can append a PRIMARY KEY
     * definition to the CREATE TABLE statement.
     *
     * Usage:
     * $table->primary('id');
     * // or for composite keys:
     * $table->primary(['user_id', 'post_id']);
     *
     * @param string|array $columns The column name or an array of column names to set as the primary key.
     * @return Blueprint Returns the current Blueprint instance for method chaining.
     */
    public function primary(string|array|null $columns = null): Blueprint {
        // If columns is null, apply to last column
        if ($columns === null && $this->lastColumn !== null) {
            $lastIndex = count($this->columns) - 1;
            $this->columns[$lastIndex] .= " PRIMARY KEY";
        } elseif (is_string($columns)) {
            // Mark an existing column as primary before table creation
            foreach ($this->columns as $i => $colDef) {
                if (str_starts_with($colDef, $columns.' ')) {
                    $this->columns[$i] .= " PRIMARY KEY";
                    break;
                }
            }
        } elseif (is_array($columns)) {
            // For composite primary keys, handle separately (optional)
            $pk = implode(', ', $columns);
            $this->indexes[] = [
                'type' => 'primary',
                'name' => "{$this->table}_primary",
                'columns' => $columns
            ];
        }
        return $this;
    }


    /**
     * Renames a particular column
     *
     * @param string $from The column's original name.
     * @param string $to The column's new name.
     * @return void
     */
    public function renameColumn(string $from, string $to): void {
        if($from === '' || $to === '') {
            Tools::info("Column names cannot be empty", 'debug', 'yellow');
            return;
        }
        
        $isConstrained = $this->isPrimaryKey($from) || $this->isIndex($from) || 
            $this->isUnique($from) || $this->isForeignKey($from);
        if(!$isConstrained) {
            $sql = "ALTER TABLE {$this->table}
                RENAME COLUMN {$from} TO {$to}";
            Db::getInstance()->query($sql);
            Tools::info("Column {$from} renamed to {$to}");
        } else {
            Tools::info("The field {$from} is a constrained column.  Make sure you drop any constraints before renaming this column.", 'debug', 'yellow');
        }
    }

    /**
     * Renames a foreign key.
     *
     * @param string $from The original column name.
     * @param string $to The new column name.
     * @return void
     */
    public function renameForeign(string $from, string $to): void {
        if($from === '' || $to === '') {
            Tools::info("Column names cannot be empty", 'debug', 'yellow');
            return;
        }

        // Check if column has a foreign key constraint
        $isForeignKey = $this->isForeignKey($from);
        $results = $this->getForeignKey($from);

        // Get information for recreating foreign key, drop index, and conserve column.
        if($isForeignKey) {
            $this->dropForeign($from, true);
        } else {
            Tools::info("'{$from}' is not a foreign key.  Skipping operation.", 'debug', 'yellow');
            return;
        }

        // Rename the column
        $this->renameColumn($from, $to);

        // Reapply foreign key if it was present
        if($isForeignKey) {
            $this->foreign(
                $to, 
                $results->REFERENCED_COLUMN_NAME, 
                $results->REFERENCED_TABLE_NAME, 
                $results->DELETE_RULE, 
                $results->UPDATE_RULE
            );
        }

        Tools::info("Successfully renamed foreign key '{$from}' to '{$to}' on '{$this->table}'");
    }

    /**
     * Renames an indexed column by preserving and reapplying the index.
     *
     * @param string $from The original column name.
     * @param string $to The new column name.
     * @return void
     */
    public function renameIndex(string $from, string $to): void {
        if($from === '' || $to === '') {
            Tools::info("Column names cannot be empty", 'debug', 'yellow');
            return;
        }

        // Check if the column is indexed
        $isIndexed = $this->isIndex($from);

        // Drop the index but preserve the column
        if($isIndexed) {
            $this->dropIndex($from, true);
        } else {
            Tools::info("'{$from}' is not an indexed column.  Skipping operation.", 'debug', 'yellow');
            return;
        }

        // Rename the column
        $this->renameColumn($from, $to);

        // Reapply the index if it was present
        if($isIndexed) {
            $this->index($to);
        }

        Tools::info("Successfully renamed indexed column '{$from}' to '{$to}' on '{$this->table}'");
    }

    /**
     * Renames the table's primary key.
     *
     * @param string $from The original column name.
     * @param string $to The new column name.
     * @return void
     */
    public function renamePrimaryKey(string $from, string $to): void {
        if($from === '' || $to === '') {
            Tools::info("Column names cannot be empty", 'debug', 'yellow');
            return;
        }

        // Check if the column is a primary key
        $isPrimaryKey = $this->isPrimaryKey($from);

        // Drop the primary key constraint but conserve the column
        if($isPrimaryKey) {
            $this->dropPrimaryKey($from, true);
        } else {
            Tools::info("'{$from}' is not a primary key.  Skipping operation.", 'debug', 'yellow');
            return;
        }

        // Rename the column
        $this->renameColumn($from, $to);

        // Reapply the index if it was present.
        if($isPrimaryKey) {
            DB::getInstance()->query("ALTER TABLE {$this->table} ADD PRIMARY KEY ({$to})");
        }

        Tools::info("Successfully renamed primary key '{$from}' to '{$to}' on '{$this->table}'");
    }

    /**
     * Renames a column with a unique constraint by preserving and reapplying the index.
     *
     * @param string $from The original column name.
     * @param string $to The new column name.
     * @return void
     */
    public function renameUnique(string $from, string $to): void {
        if($from === '' || $to === '') {
            Tools::info("Column names cannot be empty", 'debug', 'yellow');
            return;
        }

        // Check if the column has a unique constraint.
        $isUnique = $this->isUnique($from);

        // Drop the unique constraint but conserve the column.
        if($isUnique) {
            $this->dropUnique($from, true);
        } else {
            Tools::info("'{$from}' does not have an unique constraint.  Skipping operation.", 'debug', 'yellow');
            return;
        }

        // Rename the column
        $this->renameColumn($from, $to);

        // Reapply the unique constraint if it was present.
        if($isUnique) {
            $this->setUnique($to);
        }

        Tools::info("Successfully unique constrained column '{$from}' to '{$to}' on '{$this->table}'");
    }

    /**
     * Sets foreign keys during creating of table or renaming of existing 
     * foreign key.
     *
     * @return void
     */
    private function setForeignKeys(): void {
        foreach ($this->foreignKeys as $fk) {
            $sql = "ALTER TABLE {$this->table} ADD FOREIGN KEY ({$fk['column']}) " .
                "REFERENCES {$fk['referenced_table']}({$fk['referenced_column']}) " .
                "ON DELETE {$fk['on_delete']} ON UPDATE {$fk['on_update']}";
            DB::getInstance()->query($sql);
            Tools::info("Applied foreign key: {$fk['column']} → {$fk['referenced_table']}.{$fk['referenced_column']}");
        }
    }

    /**
     * Sets the unique index on a column.
     *
     * @param string $column The column where the unique index constraint 
     * will be applied.
     * @return void
     */
    private function setUnique(string $column): void {
        $indexName = "uniq_{$this->table}_{$column}";
        $this->indexes[] = [
            'type' => 'unique',
            'name' => $indexName,
            'columns' => [$column]
        ];
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

        $this->setUnique($this->lastColumn);

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
     * Define an unsigned big integer column (MySQL only).
     *
     * @param string $name The name of the column to be created as unsigned BIGINT (MySQL) or INTEGER for SQLite.
     * @return Blueprint Return the instance to allow method chaining.
     */
    public function unsignedBigInteger(string $name): Blueprint {
        if ($this->dbDriver === 'mysql') {
            $this->columns[] = "{$name} BIGINT UNSIGNED";
        } else {
            // SQLite doesn't support unsigned, but BIGINT is fine
            $this->columns[] = "{$name} BIGINT";
        }
        $this->lastColumn = $name;
        return $this;
    }

    /**
     * Update an existing table.
     */
    public function update(): void {
        foreach ($this->columns as $columnDef) {
            preg_match('/^(\w+)\s/', $columnDef, $matches);
            $columnName = $matches[1] ?? null;

            $modifierSql = '';
            if (
                $this->dbDriver === 'mysql' &&
                $columnName &&
                isset($this->columnModifiers[$columnName]['after'])
            ) {
                $afterCol = $this->columnModifiers[$columnName]['after'];
                $modifierSql = " AFTER `{$afterCol}`";
            }

            $sql = "ALTER TABLE {$this->table} ADD COLUMN {$columnDef}{$modifierSql}";
            DB::getInstance()->query($sql);
            Tools::info("SUCCESS: Adding Column {$columnDef} To {$this->table}");
        }

        foreach ($this->indexes as $index) {
            $this->createIndex($index);
        }

        $this->setForeignKeys();
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