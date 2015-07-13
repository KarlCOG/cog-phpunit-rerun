<?php
/**
 * Created by PhpStorm.
 * Date: 30.06.15
 * Time: 15:23
 *
 * Utility functions for writing to and reading caches of failed tests
 *
 * @author Karl Klein <karl.klein@cashongo.co.uk>
 */

class CacheUtil {

    /**
     * @var null|string
     */
    private $cachePath = '/tmp/failed_phpunit_tests';

    /**
     * @var string
     */
    private $key;

    private $failedTests;


    public function createCache(array $failedTests, array $cmdOptions) {
        $this->key = $this->generateKey($cmdOptions);
        $this->failedTests = $failedTests;
        $this->writeCache();
    }

    /**
     * Writes failed scenarios cache.
     */
    public function writeCache()
    {
        if (!$this->getFileName()) {
            return;
        }

        if (file_exists($this->getFileName())) {
            unlink($this->getFileName());
        }

        if (! $this->failedTests) {
            return;
        }

        file_put_contents($this->getFileName(), serialize($this->failedTests));
    }

    /**
     * Reads from cache with the given key
     * returns 2d array of test method names with corresponding class names
     */
    public function readCache($key) {
        // read from the file with the given hash as name
        // return the tests
        $this->key = $key;
        return unserialize(file_get_contents($this->getFileName()));
    }

    /**
     * Generates cache key.
     *
     * @param array $cmdOptions
     *
     * @return string
     */
    public function generateKey(array $cmdOptions)
    {
        if (in_array('--rerun', $cmdOptions)) {
            unset($cmdOptions[array_search('--rerun', $cmdOptions)]);
        }

        return md5(implode($cmdOptions));
    }

    /**
     * Returns cache filename (if exists).
     *
     * @return null|string
     */
    private function getFileName()
    {
        if (null === $this->cachePath || null === $this->key) {
            return null;
        }

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777);
        }

        return $this->cachePath . DIRECTORY_SEPARATOR . $this->key . '.txt';
    }

    public function fileExists($key)
    {
        $this->key = $key;
        $file = $this->getFileName();
        if (file_exists($file)) {
            return true;
        }
        return false;
    }
}