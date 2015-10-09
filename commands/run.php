<?php

namespace Frojd\Plugin\BasePluginWpCli\Commands;

use Frojd\Plugin\WpCliMigration\WpCliMigration;

class Run extends \WP_CLI_Command {
    const EXECUTED_MIGRATIONS_OPTION_NAME  = 'wpcli_migration_executed_migrations';

    /**
     * Run a specific migration
     *
     * ## OPTIONS
     *
     * <migration-file-name>
     * : the name of the migration to run
     * [--force]
     * : Run even if it have been executed before
     *
     * ## EXAMPLES
     *
     *     wp migration-run run 001-activate-plugins.sh
     *
     * @subcommand run
     * @synopsis <migration-file-name> [--force]
     *
     */
    public function commandRun ($args, $kwargs) {
        $migrationFileName = $args[0];
        $force = isset($kwargs['force']) && $kwargs['force'];

        $fullPath = WpCliMigration::getMigrationFolder() . $migrationFileName;

        $this->assertFileExists($fullPath);

        if ($this->isInRunList($migrationFileName) && ! $force) {
            \WP_CLI::line(sprintf(
                '%s have already been executed. You may run it anyway with --force',
                $migrationFileName
            ));

        } else {
            \WP_CLI::line(sprintf(
                'Executing %s',
                $migrationFileName
            ));

            echo shell_exec($fullPath);
            $this->addInRunList($migrationFileName);
        }
    }

    private function assertFileExists($filePath) {
        if (!file_exists($filePath)) {
            throw new \Exception(sprintf('Migration file %s does not exist', $filePath));
        }
    }

    private function isInRunList($migrationFileName) {
        $options = get_option(self::EXECUTED_MIGRATIONS_OPTION_NAME);

        return $options && in_array($migrationFileName, $options);
    }

    private function addInRunList($migrationFileName) {
        $options = get_option(self::EXECUTED_MIGRATIONS_OPTION_NAME);

        if ($options) {
            $options[] = $migrationFileName;
            $options = array_unique($options);
            update_option(self::EXECUTED_MIGRATIONS_OPTION_NAME, $options);

        } else {
            $options = [$migrationFileName];
            add_option(self::EXECUTED_MIGRATIONS_OPTION_NAME, $options, false, 'no');
        }
    }

    /**
     * Run all migrations
     *
     * ## OPTIONS
     *
     * [--force]
     * : Run migrations even if they have been executed before
     *
     * ## EXAMPLES
     *
     *     wp migration-run run-all
     *
     * @subcommand run-all
     * @synopsis [--force]
     *
     */
    public function commandRunAll ($args, $kwargs) {
        $force = isset($kwargs['force']) && $kwargs['force'];

        $count = 0;
        foreach ($this->getAllMigrations() as $migration) {
            if (!$force && $this->isInRunList($migration)) {
                continue;
            }

            $count++;

            self::commandRun([$migration], $kwargs);
        }


        \WP_CLI::line(sprintf('%d migration files executed', $count));
    }

    private function getAllMigrations() {
        return array_map('basename', glob(WpCliMigration::getMigrationFolder().'/*'));
    }

}

\WP_CLI::add_command('migration-run', __NAMESPACE__."\Run");

