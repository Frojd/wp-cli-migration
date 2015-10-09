# wp-cli-migration

This is a migration manager for wordpress. It allows developers to create and run migration scripts.
The purpouse of this is to be able to automate and version db-changes in a wordpress project.

## Available commands
```
wp migration create <args>
wp migration run <args>
````

Run the commands without args for full documentation.

## Configuration

wp-cli-migration saves all migrations to ABSPATH by default. You may change this by adding the following to your wp-config.php
```php
define('WP_CLI_MIGRATION_PATH', 'some_folder_path');
```
