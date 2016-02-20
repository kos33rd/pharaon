<?php
namespace Pharaon\Test;


class BuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $prev_dir;
    protected $phar_file = 'delta.phar';
    protected $phar_content_dir = 'phar_content';

    public function setUp()
    {
        $this->prev_dir = getcwd();
        chdir(__DIR__);
        require '../src/Builder.php';
    }

    public function test_delta_build()
    {

        $builder = new \Pharaon\Builder();
        $builder->build($this->phar_file, 'delta', 'fixtures/from', 'fixtures/to');
        print('Build complete.' . PHP_EOL);

        extract_phar($this->phar_file, $this->phar_content_dir);

        // Проверка списка на удаление файлов
        $this->assertFileExists($this->phar_content_dir . '/files_to_delete.txt');
        $deletion_list_actual = file_get_contents($this->phar_content_dir . '/files_to_delete.txt');
        $deletion_list_expected = '\dir\to_delete_2.txt' . "\n" . '\to_delete.txt' . "\n";
        $this->assertEquals($deletion_list_actual, $deletion_list_expected);

        // Проверка добавляемых файлов
        $this->assertFileExists($this->phar_content_dir . '/archive/1.txt');
        $this->assertFileExists($this->phar_content_dir . '/archive/2.txt');
        $this->assertFileExists($this->phar_content_dir . '/archive/dir/4.txt');
        $this->assertFileNotExists($this->phar_content_dir . '/archive/dir/3.txt');


    }


    public function tearDown()
    {
        deleteContent($this->phar_content_dir);
        rmdir($this->phar_content_dir);
        unlink($this->phar_file);
        chdir($this->prev_dir);
    }
}

function extract_phar($pharfile, $content_dir)
{
    $phar = new \Phar($pharfile);
    echo("Found " . $phar->count() . " objects in .phar;" . PHP_EOL);
    deleteContent($content_dir);
    $phar->extractTo($content_dir);
}

function deleteContent($path)
{
    try {
        $iterator = new \DirectoryIterator($path);
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
    } catch (\Exception $e) {
        return false;
    }
    return true;
}

;