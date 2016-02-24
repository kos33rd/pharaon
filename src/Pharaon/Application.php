<?php
namespace Pharaon;

use Symfony\Component\Console\Application as BaseApplication;
use Pharaon\Command\TestCommand;
use Pharaon\Command\BuildCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication{

    function doRun(InputInterface $input, OutputInterface $output){
        $this->add(new BuildCommand());
        $this->add(new TestCommand());
        parent::doRun($input, $output);
    }

}