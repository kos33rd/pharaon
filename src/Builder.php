<?php

namespace Pharaon;

use SplFileInfo;
use Symfony\Component\Finder\Finder;

class Builder
{
    private $from_dir;
    private $to_dir;

    private $internal_archive_dir = 'archive';
    private $internal_config_file = 'pharaon.json';
    private $build_config;

    /**
     * @param string $pharname имя генерируемого .phar-пакета
     * @param string $deployer тип деплоя - delta или full
     * @param string $from директория с старой версией
     * @param string $to директория с новой версией
     */
    public function build($pharname = "result.phar", $deployer = 'delta', $from, $to)
    {
        $this->from_dir = $from;
        $this->to_dir = $to;

        $this->check_phar_enabled();
        $phar = $this->create_phar($pharname);
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();
        $this->build_config['deployer'] = $deployer;
        if ($deployer == 'delta') {
            $this->make_delta($phar);
        } else {
            $this->make_full_archive($phar);
        }

        $this->inject_deployer_to_phar($phar, $deployer);
        $phar->addFromString($this->internal_config_file, json_encode($this->build_config));
        $phar->stopBuffering();
    }

    private function make_delta(\Phar $phar)
    {
        $files_to_add_or_replace = $this->get_files_to_add_or_replace();
        $files_to_delete = $this->get_files_to_delete();

        foreach ($files_to_add_or_replace as $file) {
            $this->addFileToPhar($phar, $file);
        }
        $files_to_delete_list = '';
        foreach ($files_to_delete as $file) {
            $files_to_delete_list .= str_replace_first($this->from_dir, '', $file->getPathname()) . "\n";
        }
        $phar->addFromString('files_to_delete.txt', $files_to_delete_list);
        return $phar;
    }


    private function make_full_archive($phar)
    {
        $files = new Finder();
        $files->files()
            ->ignoreVCS(true)
            ->name('*.*')
            ->in($this->to_dir);
        foreach ($files as $file) {
            $this->addFileToPhar($phar, $file);
        }
        return $phar;
    }


    private function create_phar($pharname)
    {
        $pharfile = new SplFileInfo($pharname);
        if ($pharfile->isFile()) {
            echo "Warning! Existing file $pharname has been overwritten." . PHP_EOL;
            unlink($pharname);
        }
        return new \Phar($pharname, 0, 'result.phar');
    }

    private function inject_deployer_to_phar($phar, $deployer)
    {
    }

    private function check_phar_enabled()
    {
        $readonly_flag = ini_get('phar.readonly');
        if ($readonly_flag) echo 'Warning! phar.readonly flag in php.ini is set to "On". Switch it to "Off" before continue.';
        return;
    }

    /**
     * @return \SplFileInfo[]
     */
    private function get_files_to_add_or_replace()
    {
        $from = $this->from_dir;
        $to = $this->to_dir;
        $only_modified_files_filter = function (\SplFileInfo $dest_file) use (&$from, &$to) {
            $dest_abs_path = $dest_file->getPathname();
            $common_path = str_replace_first($to, '', $dest_abs_path);
            $src_abs_path = $from . $common_path;
            $src_file = new SplFileInfo($src_abs_path);

            if ($src_file->isFile()) {
                if ($dest_file->getMTime() != $src_file->getMTime()) {
                    if(!files_identical($dest_file, $src_file)){
                        echo '(M) ' . $dest_file->getPathname() . PHP_EOL;
                        return true;
                    } else {
                        echo '(OC) ' . $dest_file->getPathname() . PHP_EOL;
                        return false;
                    }
                } else {
                    echo '(OT) ' . $dest_file->getPathname() . PHP_EOL;
                    return false;
                }
            } else {
                echo '(A) ' . $dest_file->getPathname() . PHP_EOL;
                return true;
            }
        };

        $files = new Finder();
        $files->files()
            ->ignoreVCS(true)
            ->name('*.*')
            ->in($to)
            ->filter($only_modified_files_filter);
        return $files;
    }

    /**
     * @return \SplFileInfo[]
     */
    private function get_files_to_delete()
    {
        $from = $this->from_dir;
        $to = $this->to_dir;
        $deleted_files_filter = function (\SplFileInfo $src_file) use (&$from, &$to) {
            $src_abs_path = $src_file->getPathname();
            $common_path = str_replace_first($from, '', $src_abs_path);
            $dest_abs_path = $to . $common_path;
            $dest_file = new SplFileInfo($dest_abs_path);

            if ($dest_file->isFile()) {
                return false;
            } else {
                echo '(D) ' . $src_file->getPathname() . PHP_EOL;
                return true;
            }
        };

        $files = new Finder();
        $files->files()
            ->ignoreVCS(true)
            ->name('*.*')
            ->in($from)
            ->filter($deleted_files_filter);
        return $files;
    }


    private function addFileToPhar(\Phar $phar, \SplFileInfo $file)
    {
        // хитровыебанный strtr невозбранно спизжен у composer-а
        $path = strtr(str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file->getPathname()), '\\', '/');
        $path = $this->internal_archive_dir . str_replace_first($this->to_dir, '', $path);
        $content = file_get_contents($file);
        $phar->addFromString($path, $content);
    }

}

function str_replace_first($from, $to, $subject)
{
    $from = '/' . preg_quote($from, '/') . '/';
    return preg_replace($from, $to, $subject, 1);
}


define('READ_LEN', 4096);

//   pass two file names
//   returns TRUE if files are the same, FALSE otherwise
function files_identical($fn1, $fn2)
{
    if (filetype($fn1) !== filetype($fn2))
        return FALSE;
    if (filesize($fn1) !== filesize($fn2))
        return FALSE;
    if (!$fp1 = fopen($fn1, 'rb'))
        return FALSE;
    if (!$fp2 = fopen($fn2, 'rb')) {
        fclose($fp1);
        return FALSE;
    }
    $same = TRUE;
    while (!feof($fp1) and !feof($fp2))
        if (fread($fp1, READ_LEN) !== fread($fp2, READ_LEN)) {
            $same = FALSE;
            break;
        }

    if (feof($fp1) !== feof($fp2))
        $same = FALSE;
    fclose($fp1);
    fclose($fp2);
    return $same;
}