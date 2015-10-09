# wp-cli-migration

This is a migration manager for wordpress. It allows developers to create and run migration scripts.
The purpouse of this is to be able to automate and version db-changes in a wordpress project.

## Available commands
```
wp migration-create <args>
wp migration-run <args>
````

Run the commands without args for full documentation.

## Configuration

wp-cli-migration saves all migrations to ABSPATH.'migrations/' by default. You may change this by adding the following to your wp-config.php
```php
define('WP_CLI_MIGRATION_PATH', 'some_folder_path');
```

## Examples

Lets say we want to activates some required plugins when a changeset is deployed.

**Create the migration:**
```bash
wp migration-create new plugin-activation
wp migration-create append "wp plugin activate advanced-custom-fields-pro"
wp migration-create append "wp plugin activate w3-total-cache"
```
If you prefer you may also edit the migration file instead of using the append command

**Test the migration localy**

Run the following on localhost to test the migration, it will only run once. If you need to run it again you could use the --force flag.
```bash
wp migration-run run-all
```

**Add "wp migration-run run-all" command to your deploy-script**
