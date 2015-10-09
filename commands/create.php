<?php

namespace Frojd\Plugin\BasePluginWpCli\Commands;

use Frojd\Plugin\WpCliMigration\WpCliMigration;

class Create extends \WP_CLI_Command {
    const LAST_CREATED_FILE_OPTION  = 'wpcli_migration_last_created_file';

    /**
     * Creates new migration file
     *
     * ## OPTIONS
     *
     * <filename>
     * : the name of the migration, will be prepended with a number and prefixed with a filetype.
     *
     * ## EXAMPLES
     *
     *     wp migration-create new some_name
     *
     * @subcommand new
     * @synopsis <filename>
     *
     */
    public function commandNew ($args) {
        $name = $args[0];

        $fileName = $this->generateFileName($name);
        $fullPath = WpCliMigration::getMigrationFolder() . $fileName;
        $this->assertFileDoesNotExists($fullPath);
        $this->createMigrationFile($fullPath, $fileName);
        $this->setLastCreatedFile($fileName);

        \WP_CLI::line(sprintf('Created migration %s.', $fullPath));
        \WP_CLI::line('Update the file with your favourite editor or with "wp migration-create append"');
    }

    private function generateFileName($name) {
        $migrationNumber = $this->getMigrationNumber();
        return sprintf("%03d-%s.sh", $migrationNumber, $name);
    }

    private function getMigrationNumber() {
        $files = glob(WpCliMigration::getMigrationFolder().'/*');
        $count = count($files);
        if ($count > 0) {
            $lastFileName = basename(end($files));
            if (preg_match('/^\d+/', $lastFileName, $matches)) {
                return (int) $matches[0] + 1;
            }
        }

        return $count;
    }

    private function assertFileDoesNotExists($filePath) {
        if (file_exists($filePath)) {
            throw new \Exception(sprintf('File %s does already exists', $filePath));
        }
    }

    private function createMigrationFile($fullPath, $fileName) {
        file_put_contents($fullPath, "#!/bin/bash\n\n", LOCK_EX);
        file_put_contents($fullPath, 'echo "Running migration '.$fileName."\"\n\n", FILE_APPEND | LOCK_EX);

        chmod($fullPath, 0755);
    }

    private function setLastCreatedFile($fileName) {
        if (get_option(self::LAST_CREATED_FILE_OPTION)) {

            update_option(self::LAST_CREATED_FILE_OPTION, $fileName);
        } else {

            add_option(self::LAST_CREATED_FILE_OPTION, $fileName, false, 'no');
        }
    }

    /**
     * Appends the latest created migration file with <command>
     *
     * ## OPTIONS
     *
     * <bash-command>
     * : The bash command to add to the latest created migration file
     *
     * ## EXAMPLES
     *
     *     wp migration-create append "wp plugin activate advanced-custom-fields-pro"
     *
     * @subcommand append
     * @synopsis <bash-command>
     *
     */
    public function commandAppend($args) {
        $command = $args[0];
        $fullPath = $this->getLastCreatedFile();

        file_put_contents($fullPath, $command."\n", LOCK_EX | FILE_APPEND);

        \WP_CLI::line(sprintf("Added \"%s\" to %s", $command, $fullPath));
    }

    private function getLastCreatedFile() {
        $fileName = get_option(self::LAST_CREATED_FILE_OPTION);
        $fullPath = WpCliMigration::getMigrationFolder() . $fileName;
        if (!file_exists($fullPath)) {
            \WP_CLI::error('Could not determine witch migration file to run. Have you created a migration?');
        }

        return $fullPath;
    }
}

\WP_CLI::add_command('migration-create', __NAMESPACE__."\Create");

