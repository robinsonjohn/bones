<?php

namespace Bayfront\Bones\Console\Commands;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModel extends Command
{

    /**
     * @return void
     */

    protected function configure()
    {

        $this->setName('make:model')
            ->setDescription('Create a new model')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of model');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $template = BONES_RESOURCES_PATH . '/cli-templates/model.php';

        if (file_exists($template)) {

            $name = ucfirst($input->getArgument('name'));

            $dir = base_path('/' . strtolower(rtrim(get_config('app.namespace'), '\\')) . '/Models');

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $file_name = $dir . '/' . $name . '.php';

            if (!copy($template, $file_name)) {

                $output->writeln('<error>Unable to create model: Failed to copy file</error>');

                return Command::FAILURE;

            }

            // Edit the contents

            $contents = file_get_contents($file_name);

            $contents = str_replace([
                '_namespace_',
                '_model_name_',
                '_bones_version_'
            ], [
                rtrim(get_config('app.namespace'), '\\'),
                $name,
                BONES_VERSION
            ], $contents);

            if (!file_put_contents($file_name, $contents)) {

                unlink($file_name);

                $output->writeLn('<error>Unable to create model: Failed to write file.</error>');

            }

            $output->writeln('<info>Model created at: ' . strtolower(rtrim(get_config('app.namespace'), '\\')) . '/Models/' . $name . '</info>');

            return Command::SUCCESS;

        } else {

            $output->writeln('<error>Unable to create model: Template not found</error>');

            return Command::FAILURE;

        }

    }


}