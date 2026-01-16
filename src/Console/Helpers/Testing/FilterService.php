<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

class FilterService {
    private $filter;
    private $location;
    private $testFile;
    public function __construct(string $filter, string $location, string $testFile) {
        $this->filter = $filter;
        $this->location = $location;
        $this->testFile = $testFile;
    }

    public function filter() {
        return $this->filter;
    }

    public function location() {
        return $this->location;
    }

    public function testFileName() {
        return $this->testFile;
    }
}