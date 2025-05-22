<?php
declare(strict_types=1);
namespace Core\Lib\Database;

use Core\DB;

/**
 * The migration API that delegates table creation and modifications to the 
 * Blueprint class.
 */
class Schema {
    /**
     * Create a new table.
     *
     * @param string $table The name of the table.
     * @param callable $callback The callback function.
     * @return void
     */
    public static function create(string $table, callable $callback): void {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $blueprint->create();
    }

    /**
     * Drop a table if it exists.
     *
     * @param string $table The name of the table.
     * @return void
     */
    public static function dropIfExists(string $table): void {
        $sql = "DROP TABLE IF EXISTS {$table}";
        DB::getInstance()->query($sql);
    }

    /**
     * Renames a table
     *
     * @param string $from The original table's name.
     * @param string $to The new table name.
     * @return void
     */
    public static function rename(string $from, string $to): void {
        $sql = "ALTER TABLE {$from} RENAME TO {$to}";
        DB::getInstance()->query($sql);
    }

    /**
     * Modify an existing table.
     *
     * @param string $table The name of the table.
     * @param callable $callback The callback function.
     * @return void
     */
    public static function table(string $table, callable $callback): void {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $blueprint->update();
    }
}