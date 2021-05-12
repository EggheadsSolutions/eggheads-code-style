<?php
declare(strict_types=1);

namespace App\CodeSniffer;


use App\AppTest;

class CommentingTest extends AppTest
{
    /** Базовые настройки */
    public function test()
    {
        $result = $this->_executePhpcs(__DIR__ . '/Samples/CommentingClass.php', null);
        self::assertEquals(
            [
                'Missing member variable doc comment',
                'Missing @var tag in member variable comment',
                'Missing doc comment for function methodBad()',
            ],
            $result
        );
    }
}
