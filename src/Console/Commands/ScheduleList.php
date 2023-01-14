<?php

namespace Bayfront\Bones\Console\Commands;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\CronScheduler\Cron;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleList extends Command
{

    protected $schedule;

    public function __construct(Cron $schedule)
    {

        $this->schedule = $schedule;

        parent::__construct();
    }

    /**
     * @return void
     */

    protected function configure()
    {

        $this->setName('schedule:list')
            ->setDescription('List all scheduled jobs')
            ->addOption('sort', null, InputOption::VALUE_REQUIRED)
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

        $jobs = $this->schedule->getJobs();

        $return = [];

        foreach ($jobs as $name => $job) {

            $return[] = [
                'name' => $name,
                'schedule' => Arr::get($job, 'at', ''),
                'prev_date' => $this->schedule->getPreviousDate($name),
                'next_date' => $this->schedule->getNextDate($name)
            ];



        }

        // Sort

        $sort = strtolower((string)$input->getOption('sort'));

        if ($sort == 'schedule') {
            $return = Arr::multisort($return, 'schedule');
        } else if ($sort == 'prev') {
            $return = Arr::multisort($return, 'prev_date');
        } else if ($sort == 'next') {
            $return = Arr::multisort($return, 'next_date');
        } else { // Name
            $return = Arr::multisort($return, 'name');
        }

        // Return


        if ($input->getOption('json')) {
            $output->writeLn(json_encode($return));
        } else {

            if (empty($return)) {
                $output->writeln('<info>No schedules found.</info>');
            } else {

                $rows = [];

                foreach ($return as $k => $v) {

                    $rows[] = [
                        $v['name'],
                        $v['schedule'],
                        $v['prev_date'],
                        $v['next_date']
                    ];

                }

                $table = new Table($output);
                $table->setHeaders(['Job name', 'Schedule', 'Previous date', 'Next date'])->setRows($rows);
                $table->render();

            }

        }

        return Command::SUCCESS;
    }


}