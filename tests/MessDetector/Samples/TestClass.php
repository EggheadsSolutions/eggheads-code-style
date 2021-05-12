<?php
declare(strict_types=1);

namespace Eggheads\Test\MessDetector\Samples;

class TestClass
{
    /**
     * @param array<int|string, mixed> $input
     * @param TestClass[] $input2
     * @return array{output: string, debug: string}
     */
    public static function main(array $input, array $input2): array
    {
        return [$input, $input2];
    }

    /**
     * @param array $badInput
     * @param null|array $badInput2
     * @param ?array $badInput3
     * @param array{output: string, debug: string $incorrectDocInput
     * @return array
     */
    public function second(array $badInput, ?array $badInput2, ?array $badInput3, array $incorrectDocInput): array
    {
        return [$badInput, $incorrectDocInput, $badInput2, $badInput3];
    }

    /**
     * @param array<int, array{output: string, debug: string}> $args
     */
    public function partialPhpDoc($args, $args2)
    {
        return [$args, $args2];
    }

    /**
     * @param int $args
     * @inheritDoc
     */
    public function ignorePhpDoc($args, $args2)
    {
        return [$args, $args2];
    }
}
