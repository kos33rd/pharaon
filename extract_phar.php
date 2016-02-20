<?php

function extract_phar($pharfile, $content_dir='phar_content'){
    $phar = new Phar($pharfile);
    echo("Found ".$phar->count()." objects in .phar;".PHP_EOL);
    deleteContent($content_dir);
    $phar->extractTo($content_dir);
}

function deleteContent($path){
    try{
        $iterator = new DirectoryIterator($path);
        foreach ( $iterator as $fileinfo ) {
            if($fileinfo->isDot())continue;
            if($fileinfo->isDir()){
                if(deleteContent($fileinfo->getPathname()))
                    @rmdir($fileinfo->getPathname());
            }
            if($fileinfo->isFile()){
                @unlink($fileinfo->getPathname());
            }
        }
    } catch ( Exception $e ){
        // write log
        return false;
    }
    return true;
};