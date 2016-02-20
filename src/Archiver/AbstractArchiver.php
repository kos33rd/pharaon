<?php

namespace Extractor;


use BadMethodCallException;

class AbstractArchiver
{
    public function inject_extractor($phar_file)
    {
        throw new NotImplementedException('Not implemented.');
    }

    public function archive($to, $from){
        throw new NotImplementedException('Not implemented.');
    }
}

class NotImplementedException extends BadMethodCallException
{
}