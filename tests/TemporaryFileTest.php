<?php

namespace Polass\Tests;

use PHPUnit\Framework\TestCase;
use Polass\TemporaryFile;

class EnumTest extends TestCase
{
    public function testConstruct()
    {
        $instance = new TemporaryFile;

        $this->assertInstanceOf(TemporaryFile::class, $instance);
        $this->assertTrue($instance->opened());
    }

    public function testGetPath()
    {
        $instance = new TemporaryFile;

        $filePath = $instance->getPath();

        $this->assertTrue(is_string($filePath));
        $this->assertNotEmpty($filePath);

        $instance->close();

        $this->assertNull($instance->getPath());
    }

    public function testGetSize()
    {
        $instance = new TemporaryFile;

        $this->assertEquals(0, $instance->getSize());

        $instance->put('A');

        $this->assertEquals(1, $instance->getSize());

        $instance->close();

        $this->assertNull($instance->getSize());
    }

    public function testStat()
    {
        $instance = new TemporaryFile;

        $stat = $instance->stat();

        $this->assertTrue(is_array($stat));
        $this->assertArrayHasKey('size', $stat);

        $instance->close();

        $this->assertNull($instance->stat());
    }

    public function testGetResource()
    {
        $instance = new TemporaryFile;

        $resource = $instance->getResource();

        $this->assertTrue(is_resource($resource));

        $instance->close();

        $this->assertNull($instance->getResource());
    }

    public function testCreate()
    {
        $instance = new TemporaryFile;

        $this->assertFileExists($old = $instance->getPath());

        $instance->create();

        $this->assertNotEquals($old, $instance->getPath());

        $instance->close();
        $instance->create();

        $this->assertTrue($instance->opened());
    }

    public function testOpened()
    {
        $instance = new TemporaryFile;

        $this->assertTrue($instance->opened());

        $instance->close();

        $this->assertFalse($instance->opened());
    }

    public function testHead()
    {
        $instance = new TemporaryFile;

        $instance->put('A');
        $instance->head();

        $this->assertEquals(0, $instance->getPosition());
    }

    public function testSeek()
    {
        $instance = new TemporaryFile;

        $instance->put('ABC');
        $instance->seek($position = 2);

        $this->assertEquals($position, $instance->getPosition());

        $instance->close();

        $instance->seek(2);

        $this->assertTrue(true);
    }

    public function testGetPosition()
    {
        $instance = new TemporaryFile;

        $this->assertEquals(0, $instance->getPosition());

        $instance->write('A');

        $this->assertEquals(1, $instance->getPosition());

        $instance->close();

        $this->assertNull($instance->getPosition());
    }

    public function testTail()
    {
        $instance = new TemporaryFile;

        $instance->put('ABC');
        $instance->head();

        $old = $instance->getPosition();

        $instance->tail();

        $position = $instance->getPosition();

        $this->assertNotEquals($old, $position);
        $this->assertEquals(3, $position);

        $instance->close();
        $instance->tail();

        $this->assertTrue(true);
    }

    public function testPut()
    {
        $instance = new TemporaryFile;

        $instance->put($content = 'HOGERA');

        $this->assertEquals($content, $instance->get());

        $instance->put($content = 'FUGA');

        $this->assertEquals($content, $instance->get());

        $instance->close();
        $instance->put('PIYO');

        $this->assertTrue(true);
    }

    public function testAdd()
    {
        $instance = new TemporaryFile;

        $instance->add($content = 'HOGE');

        $this->assertEquals($content, $instance->get());

        $instance->add($addition = 'FUGA');

        $this->assertEquals($content.$addition, $instance->get());
    }

    public function testWrite()
    {
        $instance = new TemporaryFile;

        $instance->write($content = 'HOGE');

        $this->assertEquals($content, $instance->get());

        $instance->write($addition = 'FUGA');

        $this->assertEquals($content.$addition, $instance->get());

        $instance->head();
        $instance->write('PIYO');

        $this->assertEquals('PIYOFUGA', $instance->get());

        $instance->close();
        $instance->put('HOGERA');

        $this->assertTrue(true);
    }

    public function testWriteBom()
    {
        $instance = new TemporaryFile;

        $instance->writeBom();

        $this->assertRegExp('/\A\xEF\xBB\xBF\z/', $instance->get());

        $instance->close();
        $instance->writeBom();

        $this->assertTrue(true);
    }

    public function testRead()
    {
        $instance = new TemporaryFile;

        $instance->put($content = 'ABC');

        $this->assertEquals('', $instance->read(1024));

        $instance->head();

        $this->assertEquals('ABC', $instance->read(1024));

        $instance->head();

        $this->assertEquals('A', $instance->read(1));

        $instance->close();

        $this->assertNull($instance->read(1024));
    }

    public function testGet()
    {
        $instance = new TemporaryFile;

        $this->assertEquals('', $instance->get());

        $instance->put($content = 'ABC');
        $instance->seek(2);

        $this->assertEquals($content, $instance->get());

        $instance->close();

        $this->assertNull($instance->get());
    }

    public function testGetcsv()
    {
        $instance = new TemporaryFile;

        $instance->put('hoge,fuga,"piyo"');
        $instance->head();

        $this->assertEquals(['hoge', 'fuga', 'piyo'], $instance->getcsv());

        $instance->tail();

        $this->assertFalse($instance->getcsv());

        $instance->head();
        $instance->close();

        $this->assertNull($instance->getcsv());
    }

    public function testCopy()
    {
        $filePath = sys_get_temp_dir().'/'.uniqid();

        $instance = new TemporaryFile;
        $instance->put($content = 'ABC');

        $instance->copy($filePath);

        $this->assertFileExists($filePath);
        $this->assertEquals($content, file_get_contents($filePath));

        unlink($filePath);

        $this->assertFileNotExists($filePath);

        $instance->close();

        @$instance->copy($filePath);

        $this->assertFileNotExists($filePath);
    }


    public function testReset()
    {
        $instance = new TemporaryFile;

        $old = $instance->getPath();

        $instance->put('HOGE');
        $instance->reset();

        $this->assertNotEquals($old, $instance->getPath());
        $this->assertTrue($instance->opened());
        $this->assertEquals('', $instance->get());

        $instance->put('FUGA');

        $this->assertEquals('FUGA', $instance->get());

        $instance->close();
        $instance->reset();

        $this->assertTrue($instance->opened());
    }

    public function testClose()
    {
        $instance = new TemporaryFile;

        $instance->close();

        $this->assertFalse($instance->opened());

        $instance->close();

        $this->assertFalse($instance->opened());
    }

    public function testDelete()
    {
        $instance = new TemporaryFile;

        $filePath = $instance->getPath();

        $instance->delete();

        $this->assertFalse($instance->opened());
        $this->assertFileNotExists($filePath);

        $instance->delete();

        $this->assertFalse($instance->opened());
    }
}
