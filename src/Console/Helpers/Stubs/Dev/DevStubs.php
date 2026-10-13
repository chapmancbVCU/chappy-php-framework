<?php
namespace Console\Helpers\Stubs\Dev;

/**
 * Collection of stubs for framework dev tools.
 */
class DevStubs {
    /**
     * Generates composer_remote.txt file.
     *
     * @return string The file's contents.
     */
    public static function composerLocal(): string {
        return <<<TXT
{
  "name": "chappy-php/chappy-php",
  "description": "Starter project for Chappy PHP Framework.",
  "type": "project",
  "license": "MIT",
  "authors": [
    {
      "name": "Chad Chapman",
      "email": "chad.chapman2010@gmail.com"
    }
  ],
  "require": {
    "php": ">=8.4",
      "chappy-php/chappy-php-framework": "dev-main"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Config\\": "config/",
      "Database\\Seeders\\": "database/seeders/",
      "Database\\Migrations\\": "database/migrations/",
      "Database\\Factories\\": "database/factories/"
    }
  },
  "scripts": {
    "setup": [
      "@php vendor/chappy-php/chappy-php-framework/src/scripts/setup.php"
    ],
    "post-install-cmd": [
      "@setup"
    ],
    "post-create-project-cmd": [
      "@setup"
    ]
  },
  "repositories": [
    {
      "type": "path",
      "url": "../chappy-php-framework",
      "options": {
        "symlink": true
      }
    }
  ],
  "require-dev": {
    "psy/psysh": "^0.12.20"
  }
}
TXT;
    }

    /**
     * Generates composer_remote.txt file.
     *
     * @return string The file's contents.
     */
    public static function composerRemote(): string {
        return <<<TXT
{
  "name": "chappy-php/chappy-php",
  "description": "Starter project for Chappy PHP Framework.",
  "type": "project",
  "license": "MIT",
  "authors": [
    {
      "name": "Chad Chapman",
      "email": "chad.chapman2010@gmail.com"
    }
  ],
  "require": {
    "php": ">=8.4",
      "chappy-php/chappy-php-framework": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Config\\": "config/",
      "Database\\Seeders\\": "database/seeders/",
      "Database\\Migrations\\": "database/migrations/",
      "Database\\Factories\\": "database/factories/"
    }
  },
  "scripts": {
    "setup": [
      "@php vendor/chappy-php/chappy-php-framework/src/scripts/setup.php"
    ],
    "post-install-cmd": [
      "@setup"
    ],
    "post-create-project-cmd": [
      "@setup"
    ],
    "require-dev": {
      "psy/psysh": "^0.12.20"
    }
  }
}
TXT;
    }

    /**
     * Generates toggle-composer.sh file.
     *
     * @return string The file's contents.
     */
    public static function toggleComposer(): string {
        return <<<TXT
#!/bin/bash

# Paths
LOCAL="composer_local.txt"
REMOTE="composer_remote.txt"
ACTIVE="composer.json"

# Detect current mode
if cmp -s "\$ACTIVE" "\$LOCAL"; then
  echo "🔁 Switching to REMOTE version of composer.json..."
  cp "\$REMOTE" "\$ACTIVE"
elif cmp -s "\$ACTIVE" "\$REMOTE"; then
  echo "🔁 Switching to LOCAL version of composer.json..."
  cp "\$LOCAL" "\$ACTIVE"
else
  echo "❓ Current composer.json does not match either template."
  echo "Defaulting to LOCAL version..."
  cp "\$LOCAL" "\$ACTIVE"
fi

echo "✅ composer.json has been toggled."

TXT;
    }
}