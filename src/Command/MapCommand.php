<?php

namespace Ptolemy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MapCommand extends Command
{
    protected static $defaultName = 'map';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Success !');
        return Command::SUCCESS;
    }
}
