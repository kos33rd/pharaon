<?php
namespace Pharaon\Command;

use DirectoryIterator;
use Exception;
use Phar;
use Pharaon\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Test .phar creation')
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'Directory from which to build delta'
            )
            ->addArgument(
                'to',
                InputArgument::REQUIRED,
                'Directory to build delta to'
            )
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Generating .phar filename',
                'archive.phar'
            )
            ->addArgument(
                'dump',
                InputArgument::OPTIONAL,
                'Directory to dump archive to',
                './phar_content'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phar_filename = $input->getArgument('name');
        $from_dir = $input->getArgument('from');
        $to_dir = $input->getArgument('to');
        $dump_dir = $input->getArgument('dump');

        $builder = new Builder();
        $builder->build($phar_filename, 'delta', $from_dir, $to_dir);

        $output->writeln('Build complete.');
        $this->extract_phar($phar_filename, $dump_dir);
        $output->writeln('Extraction complete.');
    }

    function extract_phar($pharfile, $content_dir = 'phar_content')
    {
        $phar = new Phar($pharfile);
        echo("Found " . $phar->count() . " objects in .phar;" . PHP_EOL);
        $this->deleteContent($content_dir);
        $phar->extractTo($content_dir);
    }

    function deleteContent($path)
    {
        try {
            $iterator = new DirectoryIterator($path);
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isDot()) continue;
                if ($fileinfo->isDir()) {
                    if (deleteContent($fileinfo->getPathname()))
                        @rmdir($fileinfo->getPathname());
                }
                if ($fileinfo->isFile()) {
                    @unlink($fileinfo->getPathname());
                }
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}