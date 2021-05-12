<?php
declare(strict_types=1);

namespace App\MessDetector;


use App\AppTest;

class MethodMixTest extends AppTest
{
    /** MethodMix */
    public function test()
    {
        $result = $this->_executePhpmd(__DIR__ . '/Samples/TestClass.php', 'MethodMix');
        self::assertEquals(
            [
                '(MethodMix) Static and dynamic methods mix in single class: TestClass.',
            ],
            $result
        );
    }
}
