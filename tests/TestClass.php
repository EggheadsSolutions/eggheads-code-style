<?php
declare(strict_types=1);

namespace App;

class TestClass
{
    /**
     * @param array<int|string, mixed> $input
     * @return array{output: string, debug: string}
     */
    public static function main(array $input): array
    {
        return $input;
    }

    /**
     * @param array $input
     * @return array
     */
    public function second(array $input): array
    {
        return $input;
    }
}
