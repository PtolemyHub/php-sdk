<?php

namespace Ptolemy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class UnmapCommand extends Command
{
    protected static $defaultName = 'unmap';

    public function configure()
    {
        $this
            ->addArgument('entrypoint', InputArgument::REQUIRED, 'The entrypoint directory or file to unmap')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entrypoint = $input->getArgument('entrypoint');

        if (!is_dir($entrypoint) && !is_file($entrypoint)) {
            throw new \Exception(sprintf('The provided entrypoint %s is neither a directory nor a file', $entrypoint));
        }

        $output->writeln(sprintf('Unmapping the entrypoint %s', $entrypoint));

        $this->unmap($entrypoint);

        return Command::SUCCESS;
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
}
