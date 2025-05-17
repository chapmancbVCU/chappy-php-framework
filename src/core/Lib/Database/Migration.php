<?php
declare(strict_types=1);
namespace Core\Lib\Database;
use Core\Lib\Utilities\DateTime;
use Core\DB;

/**
 * Supports database migration operations.
 */
abstract class Migration {
    /**
     * Database instance.
     *
     * @var DB
     */
    protected DB $_db;

    /**
     * Maps column types to blueprint methods.
     *
     * @var array<string, string>
     */
    protected array $_columnTypesMap = [
        'int' => '_intColumn', 'integer' => '_intColumn', 'tinyint' => '_tinyintColumn', 'smallint' => '_smallintColumn',
        'mediumint' => '_mediumintColumn', 'bigint' => '_bigintColumn', 'numeric' => '_decimalColumn', 'decimal' => '_decimalColumn',
        'double' => '_doubleColumn', 'float' => '_floatColumn', 'bit' => '_bitColumn', 'date' => '_dateColumn',
        'datetime' => '_datetimeColumn', 'timestamp' => '_timestampColumn', 'time' => '_timeColumn', 'year' => '_yearColumn',
        'char' => '_charColumn', 'varchar' => '_varcharColumn', 'text' => '_textColumn'
    ];


    /**
     * Creates instance of Migration class.
     * 
     * @param string $isCli Contains value for interface type.
     */
    public function __construct() {
        $this->_db = DB::getInstance();
    }

    /**
     * Setup acl table's initial fields during first db migration.
     *
     * @param string $table Name of acl table used to test that we are 
     * performing operations on correct table.
     * @return void
     */
    public function aclSetup(string $table): void {
        $timestamp = DateTime::timeStamps();
        if($table == 'acl') {
            $this->_db->insert('acl', ['acl' => 'Admin', 'deleted' => 0, 'created_at' => $timestamp, 'updated_at' => $timestamp]);
        }
    }

    /**
     * Get value for greatest value in batch field in migrations table.
     *
     * @return int Value for batch field to be used in next migration run.
     */
    public static function getNextBatch(): int {
        $db = DB::getInstance();
        $result = $db->query("SELECT MAX(batch) as max_batch FROM migrations")->first();
        return ($result && $result->max_batch !== null) ? (int)$result->max_batch + 1 : 1;
    }

    /**
     * Rollback the migration.
     */
    abstract public function down(): void;

    /**
     * Returns a new Blueprint instance for the specified table.
     *
     * This method is used to define or modify the structure of a database table.
     * It provides an entry point to schema-building methods such as `id()`,
     * `string()`, `timestamps()`, etc.
     *
     * @param string $table The name of the table to build or modify.
     * @return Blueprint An instance of the Blueprint class tied to the given table.
     */
    protected function schema(string $table): Blueprint {
        return new Blueprint($table);
    }

    /**
     * Execute the migration.
     */
    abstract public function up(): void;
}
