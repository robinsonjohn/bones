<?php

namespace Bayfront\Bones\Application\Kernel\Console\Commands;

use Bayfront\Bones\Application\Utilities\App;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheList extends Command
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */

    protected function configure(): void
    {

        $this->setName('cache:list')
            ->setDescription('List contents of cache')
            ->addOption('type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('json', null, InputOption::VALUE_NONE);

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $return = [];

        $return_types = $input->getOption('type');

        if (is_file(App::storagePath('/bones/cache/commands.json'))) {

            if (empty($return_types) || in_array('commands', $return_types)) {

                $commands = json_decode(file_get_contents(App::storagePath('/bones/cache/commands.json')), true);

                foreach ($commands as $command) {

                    $return[] = [
                        'type' => 'Command',
                        'class' => $command
                    ];

                }

            }

        }

        if (is_file(App::storagePath('/bones/cache/events.json'))) {

            if (empty($return_types) || in_array('events', $return_types)) {

                $events = json_decode(file_get_contents(App::storagePath('/bones/cache/events.json')), true);

                foreach ($events as $event) {

                    $return[] = [
                        'type' => 'Event',
                        'class' => $event
                    ];

                }

            }

        }

        if (is_file(App::storagePath('/bones/cache/filters.json'))) {

            if (empty($return_types) || in_array('filters', $return_types)) {

                $filters = json_decode(file_get_contents(App::storagePath('/bones/cache/filters.json')), true);

                foreach ($filters as $filter) {

                    $return[] = [
                        'type' => 'Filter',
                        'class' => $filter
                    ];

                }

            }

        }

        // Return

        if ($input->getOption('json')) {
            $output->writeLn(json_encode($return));
        } else {

            if (empty($return)) {
                $output->writeln('<info>No cached items found.</info>');
            } else {

                $rows = [];

                foreach ($return as $v) {

                    $rows[] = [
                        $v['type'],
                        $v['class']
                    ];

                }

                $table = new Table($output);
                $table->setHeaders(['Type', 'Class'])->setRows($rows);
                $table->render();

            }

        }

        return Command::SUCCESS;
    }

}