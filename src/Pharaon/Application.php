<?php
namespace Pharaon;

use Symfony\Component\Console\Application as BaseApplication;
use Pharaon\Command\BuildCommand;

class Application extends BaseApplication{
    function doRun($input, $output){
        $this->add(new BuildCommand());
        parent::doRun($input, $output);
    }
}