<?php
namespace Pharaon\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Build .phar')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Generating .phar filename'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        if ($name) {
            $text = 'Building '.$name;
        } else {
            $text = 'Building build.phar';
        }

        $output->writeln($text);
    }
}