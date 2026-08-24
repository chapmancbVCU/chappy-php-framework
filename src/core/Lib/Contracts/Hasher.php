<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

interface Hasher {
    public function make(string $value): string;
    public function verify(string $value, string $hashed): bool;
    public function needsRehash(string $hashed): bool;
}