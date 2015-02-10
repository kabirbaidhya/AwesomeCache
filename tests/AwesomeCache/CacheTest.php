<?php

namespace Gckabir\AwesomeCache;

class CacheTest extends TestCase
{
    public function __construct()
    {
        $config = array(
            'directory'    => __DIR__.'/../TestData/cache',
        );
        Cache::config($config);
    }

    public function testDirectoryPathAlwaysEndsWithATrailingSlash()
    {
        $directory = Cache::config('directory');

        $lastCharacter = substr($directory, -1, 1);
        $this->assertEquals($lastCharacter, '/');
    }

    public function testDirectoryIsAutoGeneratedWhenInstantiated()
    {
        $myCache = new Cache('testdata');

        $directory = Cache::config('directory');

        $directoryExists = is_dir($directory);

        $this->assertTrue($directoryExists);

    }

    public function testExceptionWhenBlankKeyIsPassedInTheConstructor()
    {
        $this->setExpectedException('Gckabir\AwesomeCache\CacheException');
        $myCache = new Cache('  ');
    }

    public function testStoringDataInTheCache()
    {
        $key = 'testdata1';
        $myCache = new Cache($key);

        if (file_exists($myCache->filePath())) {
            unlink($myCache->filePath());
        }

        $this->assertFileNotExists($myCache->filePath());

        $myCache->putInCache('this is just a test data');

        $this->assertFileExists($myCache->filePath());

        return $key;
    }

    /**
     * @depends testStoringDataInTheCache
     */
    public function testIsCachedFunctionIsWorking($existingDataKey)
    {
        $myCache = new Cache($existingDataKey);
        $this->assertTrue($myCache->isCached());

        @unlink($myCache->filePath());
        // for an un existing file
        $anotherData = new Cache('some-unexisting-data');

        $this->assertFalse($anotherData->isCached());
    }

    public function testRetrievingDataFromCache()
    {
        $key = 'testdata2';

        //storing
        $myCache = new Cache($key);

        $dataToBeCached = array(
            'foo'    => 'Bar',
            'hello'    => 'World',
        );

        $myCache->putInCache($dataToBeCached);

        // retrieving
        $myCacheRetrival = new Cache($key);

        $retrievedData = $myCacheRetrival->cachedData();

        $this->assertNotEmpty($retrievedData);
        $this->assertEquals($dataToBeCached, $retrievedData);

        @unlink($myCacheRetrival->filePath());
    }

    public function testClearingSpecificCache()
    {
        $key = 'testdata3';

        $myCache = new Cache($key);

        $myCache->putInCache('Foo Bar');

        $this->assertFileExists($myCache->filePath());

        $myCache->purge();

        $this->assertFileNotExists($myCache->filePath());
    }

    public function testClearingAllCachedData() {

        $foo1 = new Cache('foo1');
        $foo1->putInCache('Foo BAr 1');

        $foo2 = new Cache('foo2');
        $foo2->putInCache('Foo BAr 2');

        $foo3 = new Cache('foo3');
        $foo3->putInCache('Foo BAr 3');

        Cache::clearAll();

        $this->assertFileNotExists($foo1->filePath());
        $this->assertFileNotExists($foo1->filePath());
        $this->assertFileNotExists($foo1->filePath());

    }

    public function testCountAllIsWorking() {

        Cache::clearAll();
        
        $this->assertEquals(0, Cache::countAll());

        $foo1 = new Cache('foo1');
        $foo1->putInCache('Foo BAr 1');

        $foo2 = new Cache('foo2');
        $foo2->putInCache('Foo BAr 2');

        $this->assertEquals(2, Cache::countAll());

        Cache::clearAll();

        $this->assertEquals(0, Cache::countAll());
    }

    public function testConfigurations() {

        $ourConfig  = array(
            'cacheExpiry'   => 44556,
            'directory'     => 'foo-cache/'
        );

        // Setting configurations
        Cache::config($ourConfig);

        // retrieving all config
        $allConfigs = Cache::config();
        $this->assertTrue(is_array($allConfigs));

        $this->assertEquals($ourConfig['cacheExpiry'], $allConfigs['cacheExpiry']);
        $this->assertEquals($ourConfig['directory'], $allConfigs['directory']);

        // retrieving individual config items
        $this->assertEquals($ourConfig['cacheExpiry'], Cache::config('cacheExpiry'));
        $this->assertEquals($ourConfig['directory'], Cache::config('directory'));
    }

    public function testConfigThrowsExceptionForInvalidInput() {

        $this->setExpectedException('Gckabir\AwesomeCache\CacheException');
        Cache::config(56.2);
    }
}
