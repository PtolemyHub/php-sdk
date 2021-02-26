<?php

namespace Ptolemy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MapCommand extends Command
{
    protected static $defaultName = 'map';

    public function configure()
    {
        $this
            ->addArgument('entrypoint', InputArgument::REQUIRED, 'The entrypoint directory or file to map')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entrypoint = $input->getArgument('entrypoint');

        if (!is_dir($entrypoint) && !is_file($entrypoint)) {
            throw new \Exception(sprintf('The provided entrypoint %s is neither a directory nor a file', $entrypoint));
        }

        $output->writeln(sprintf('Mapping the entrypoint %s', $entrypoint));

        if (is_dir($entrypoint)) {
            $finder = new Finder();

            foreach ($finder->in($entrypoint)->name('*.php')->files() as $file) {
                $this->transformFile($file->getRealPath());
            }
        } else {
            $this->transformFile($entrypoint);
        }

        return Command::SUCCESS;
    }

    private function transformFile(string $filepath)
    {
        if (!is_file($filepath)) {
            return;
        }

        $content = file_get_contents($filepath);
        $regexCall = "/((?:static )?(public|protected|private) +(?:static )?function +[a-zA-Z0-9_]+\([^)]*\)[^{]*\{)/s";
        $replaceCall = "$1\n        \Ptolemy\Service\SenderService::track();\n";

        $newFileContent = preg_replace($regexCall, $replaceCall, $content);
        file_put_contents($filepath, $newFileContent);
    }
}
