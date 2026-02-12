<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;

/**
 * Collection of stubs for migration files.
 */
class MigrationStubs {
    /**
     * Template for acl table migration.
     *
     * @return string The content for acl table migration.
     */
    public static function aclTableTemplate(): string {
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;
use Core\Lib\Database\Schema;
/**
 * Migration class for the acl table.
 */
class MDT20240808232014CreateAclTable extends Migration {
  /**
   * Performs a migration.
   *
   * @return void
   */
  public function up(): void {
    Schema::create('acl', function (Blueprint \$table) {
      \$table->id();                      // Auto-incrementing primary key
      \$table->string('acl', 25);         // VARCHAR(25)
      \$table->softDeletes();             // Adds deleted column
      \$table->timestamps();              // Adds created_at & updated_at
    });

    \$this->aclSetup('acl');  // Ensures initial ACL setup
  }

  /**
   * Undo a migration task.
   *
   * @return void
   */
  public function down(): void {
    Schema::dropIfExists('acl');
  }
}
PHP;
    }

    /**
     * Template for email_attachments table migration.
     *
     * @return string The content for email_attachments table migration.
     */
    public static function emailAttachmentsTableTemplate(): string {
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the email_attachments table.
 */
class MDT20250621195401CreateEmailAttachmentsTable extends Migration {
    /**
     * Performs a migration for a new table.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('email_attachments', function (Blueprint \$table) {
            \$table->id();
            \$table->string('attachment_name', 255);
            \$table->string('name', 255);
            \$table->string('path', 255);
            \$table->text('description');
            \$table->string('mime_type', 100);
            \$table->integer('size');
            \$table->integer('user_id');
            \$table->index('user_id');
            \$table->softDeletes();
            \$table->timestamps();
        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('email_attachments');
    }
}
PHP;
    }

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
     * Template for migrations table migration.
     *
     * @return string The content for migrations table migration.
     */
    public static function migrationTableTemplate(): string {
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the migrations table.
 */
class MDT20240805010123CreateMigrationTable extends Migration {
    /**
     * Performs a migration.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('migrations', function (Blueprint \$table) {
          \$table->id();
          \$table->string('migration', 512);
          \$table->index('migration');
          \$table->integer('batch');
      });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
      Schema::dropIfExists('migrations');
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

    /**
     * Template for profile_images table migration.
     *
     * @return string The content for profile_images table migration.
     */
    public static function profileImagesTableTemplate(): string {
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Migration;

/**
 * Migration class for the profile_images table.
 */
class MDT20240821210722CreateProfileImagesTable extends Migration {
  /**
   * Performs a migration.
   *
   * @return void
   */
  public function up(): void {
    Schema::create('profile_images', function (Blueprint \$table) {
      \$table->id();
      \$table->string('url', 255);
      \$table->integer('sort')->nullable();
      \$table->integer('user_id');
      \$table->string('name', 255);
      \$table->timestamps();
      \$table->softDeletes();
      \$table->index('user_id');
    });
  }

  /**
   * Undo a migration task.
   *
   * @return void
   */
  public function down(): void {
    Schema::dropIfExists('profile_images');
  }
}
PHP;
    }

    /**
     * Template for user_sessions table migration.
     *
     * @return string The content for user_sessions table migration.
     */
    public static function userSessionsTableTemplate(): string {
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Migration;

/**
 * Migration class for the user_sessions table.
 */
class MDT20241118175443CreateUserSessionsTable extends Migration {
    /**
     * Performs a migration.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('user_sessions', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
            \$table->integer('user_id');
            \$table->string('session', 255);
            \$table->string('user_agent', 255);
            \$table->index('user_id');
            \$table->index('session');
        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('user_sessions');
    }
}
PHP;
    }

    /**
     * Template for users table migration.
     *
     * @return string The content for users table migration.
     */
    public static function usersTableTemplate(): string {
        return <<<PHP
<?php
namespace Database\Migrations;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;
use Core\Lib\Database\Schema;
/**
 * Migration class for the users table.
 */
class MDT20240805010157CreateUsersTable extends Migration {
    /**
     * Performs a migration task.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('users', function (Blueprint \$table) {
            \$table->id();
            \$table->string('username', 150);
            \$table->string('email', 150);
            \$table->string('password', 150);
            \$table->string('fname', 150);
            \$table->string('lname', 150);
            \$table->text('acl');
            \$table->text('description')->nullable();
            \$table->tinyInteger('reset_password')->default(0);
            \$table->tinyInteger('inactive')->default(0);
            \$table->integer('login_attempts')->default(0);
            \$table->timestamps();
            \$table->softDeletes();

            // Indexes
            \$table->index('created_at');
            \$table->index('updated_at');
        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('users');
    }
}
PHP;
    }
}