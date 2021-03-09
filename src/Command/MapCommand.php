<?php

namespace Ptolemy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MapCommand extends Command
{
    protected static $defaultName = 'map';

    public function configure()
    {
        $this
            ->addArgument('entrypoint', InputArgument::REQUIRED, 'The entrypoint directory or file to map')
            ->addOption('unmap', null, InputOption::VALUE_NONE, 'Remove all calls to Ptolemy instead of adding them')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entrypoint = $input->getArgument('entrypoint');

        if (!is_dir($entrypoint) && !is_file($entrypoint)) {
            throw new \Exception(sprintf('The provided entrypoint %s is neither a directory nor a file', $entrypoint));
        }

        $output->writeln(sprintf('Mapping the entrypoint %s', $entrypoint));

        if ($input->getOption('unmap') !== true) {
            $this->map($entrypoint);
        } else {
            $this->unmap($entrypoint);
        }

        return Command::SUCCESS;
    }

    private function map(string $entrypoint): void
    {
        if (is_dir($entrypoint)) {
            $finder = new Finder();

            foreach ($finder->in($entrypoint)->name('*.php')->files() as $file) {
                $this->addCallToLibrary($file->getRealPath());
            }
        } else {
            $this->addCallToLibrary($entrypoint);
        }
    }

    private function unmap(string $entrypoint): void
    {
        if (is_dir($entrypoint)) {
            $finder = new Finder();

            foreach ($finder->in($entrypoint)->name('*.php')->files() as $file) {
                $this->removeCallToLibrary($file->getRealPath());
            }
        } else {
            $this->removeCallToLibrary($entrypoint);
        }
    }

    private function removeCallToLibrary(string $filepath)
    {
        if (!is_file($filepath)) {
            return;
        }

        $content = file_get_contents($filepath);
        $regexCall = "/(\n[^\nP]*Ptolemy[^(]*\(.*\);\n)/";

        $newFileContent = preg_replace($regexCall, '', $content);
        file_put_contents($filepath, $newFileContent);
    }

    private function addCallToLibrary(string $filepath)
    {
        if (!is_file($filepath)) {
            return;
        }

        $content = file_get_contents($filepath);
        $regexCall = "/((?:static )?(public|protected|private) +(?:static )?function +[a-zA-Z0-9_]+\([^)]*\)[^{]*\{)/s";
        $replaceCall = "$1\n        \Ptolemy\Geographer::noteCall();\n";

        $newFileContent = preg_replace($regexCall, $replaceCall, $content);
        file_put_contents($filepath, $newFileContent);
    }
}
