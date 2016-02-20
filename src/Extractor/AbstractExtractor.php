<?php

namespace Extractor;


use BadMethodCallException;

class AbstractExtractor
{
    public function inject_extractor($phar_file)
    {
        throw new NotImplementedException('Not implemented.');
    }
}

class NotImplementedException extends BadMethodCallException
{
}