<?php
declare(strict_types=1);

namespace App\CodeSniffer;


use App\AppTest;

class TestBase extends AppTest
{
    /** Базовые настройки */
    public function test()
    {
        $result = $this->_executePhpcs(__DIR__ . '/Samples/test.php', null);
        self::assertEquals([
            'Missing required strict_types declaration',
            'Comment refers to a TODO task',
        ], $result);
    }
}
