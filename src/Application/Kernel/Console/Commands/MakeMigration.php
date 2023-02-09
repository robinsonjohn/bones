<?php

namespace Bayfront\Bones\Application\Kernel\Console\Commands;

use Bayfront\Bones\Application\Kernel\Console\ConsoleUtilities;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Application\Utilities\Constants;
use Bayfront\Bones\Exceptions\ConsoleException;
use Bayfront\Bones\Exceptions\FileAlreadyExistsException;
use Bayfront\Bones\Exceptions\UnableToCopyException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMigration extends Command
{

    /**
     * @return void
     */

    protected function configure(): void
    {

        $this->setName('make:migration')
            ->setDescription('Create a database migration')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of migration');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $name = strtolower($input->getArgument('name'));

        $date_name = date('Y-m-d-His') . '_' . $name;

        $util_name = 'Migration (' . $date_name . ')';

        if (is_dir(App::resourcesPath('/database/migrations/'))) {

            $matches = glob(App::resourcesPath('/database/migrations/*_' . $name . '.php'));

            if (!empty($matches)) {
                $output->writeln('<error>Unable to make migration: Migration name already exists</error>');
                return Command::FAILURE;
            }

        }

        ConsoleUtilities::msgInstalling($util_name, $output);

        try {

            $src_file = Constants::get('BONES_RESOURCES_PATH') . '/cli/templates/make/migration.php';

            $dest_file = App::resourcesPath('/database/migrations/' . $date_name . '.php');

            ConsoleUtilities::copyFile($src_file, $dest_file);

            ConsoleUtilities::replaceFileContents($dest_file, [
                '_migration_name_' => $name,
                '_bones_version_' => App::getBonesVersion()
            ]);

            ConsoleUtilities::msgInstalled($util_name, $output);

            $output->writeln('<info>*** NOTE: Be sure to run "composer install" to complete migration installation ***</info>');
            $output->writeln('<info>For more info, see: https://github.com/bayfrontmedia/bones/blob/master/docs/services/db.md#migrations</info>');

            return Command::SUCCESS;

        } catch (FileAlreadyExistsException) {
            ConsoleUtilities::msgFileExists($util_name, $output);
            return Command::FAILURE;
        } catch (UnableToCopyException) {
            ConsoleUtilities::msgUnableToCopy($util_name, $output);
            return Command::FAILURE;
        } catch (ConsoleException) {
            ConsoleUtilities::msgFailedToWrite($util_name, $output);
            return Command::FAILURE;

        }

    }

}