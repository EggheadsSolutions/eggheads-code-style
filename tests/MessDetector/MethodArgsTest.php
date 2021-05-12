<?php
declare(strict_types=1);

namespace App\MessDetector;

use App\AppTest;

class MethodArgsTest extends AppTest
{
    /** MethodArgs */
    public function test()
    {
        $result = $this->_executePhpmd(__DIR__ . '/Samples/TestClass.php', 'MethodArgs');
        self::assertEquals(
            [
                '(MethodArgs) Method arguments/result error: Describe array $badInput.',
                '(MethodArgs) Method arguments/result error: Describe array $badInput2.',
                '(MethodArgs) Method arguments/result error: Describe array $badInput3.',
                '(MethodArgs) Method arguments/result error: Incorrect phpDoc: "array{output: string, debug: string $incorrectDocInput".',
                '(MethodArgs) Method arguments/result error: Describe result array.',
                '(MethodArgs) Method arguments/result error: Some parameters are not described in phpDoc block.',
            ],
            $result
        );
    }
}
