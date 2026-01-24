<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;

/**
 * Collection of stubs for migration files.
 */
class MigrationStubs {
    /**
     * Generates a new Migration class for creating a new table.
     *
     * @param string $fileName The file name for the Migration class.
     * @param string $tableName The name of the table for the migration.
     * @return string The contents of the new Migration class.
     */
    public static function migrationClass(string $fileName, string $tableName): string {
        $tableName = Str::lower($tableName);
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the {$tableName} table.
 */
class {$fileName} extends Migration {
    /**
     * Performs a migration for a new table.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();

        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('{$tableName}');
    }
}
PHP;
    }

    /**
     * Generates a new Migration class for renaming an existing table.
     *
     * @param string $fileName The file name for the Migration class.
     * @param string $from The table's original name.
     * @param string $to The new name for the table.
     * @return string The contents of the new Migration class.
     */
    public static function migrationRenameClass(string $fileName, string $from, string $to): string {
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Migration;

/**
 * Migration class for renaming the {$from} table.
 */
class {$fileName} extends Migration {
    /**
     * Performs a migration for renaming an existing table.
     *
     * @return void
     */
    public function up(): void {
        Schema::rename('{$from}', '{$to}');
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('{$to}');
    }
}
PHP;
    }

    /**
     * Generates a new Migration class for updating a table.
     *
     * @param string $fileName The file name for the Migration class.
     * @param string $tableName The name of the table for the migration.
     * @return string The contents of the new Migration class.
     */
    public static function migrationUpdateClass(string $fileName, string $tableName): string {
        $tableName = Str::lower($tableName);
        return<<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the {$tableName} table.
 */
class {$fileName} extends Migration {
    /**
     * Performs a migration for updating an existing table.
     *
     * @return void
     */
    public function up(): void {
        Schema::table('{$tableName}', function (Blueprint \$table) {

        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('$tableName');
    }
}
PHP;
    }
}