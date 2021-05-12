<?php
declare(strict_types=1);

namespace App\MessDetector;

use App\AppTest;

class MethodPropsTest extends AppTest
{
    /** MethodProps */
    public function test()
    {
        $result = $this->_executePhpmd(__DIR__ . '/Samples/TestClass2.php', 'MethodProps');

        self::assertEquals(
            [
                '(MethodProps) Property error: Describe array.',
                '(MethodProps) Property error: Empty @var tag.',
            ],
            $result
        );
    }
}
