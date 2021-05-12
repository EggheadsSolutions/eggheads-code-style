<?php
declare(strict_types=1);

namespace App\CodeSniffer;

use App\AppTest;

class NamingConventionsTest extends AppTest
{
    /** NamingConventions */
    public function test()
    {
        $result = $this->_executePhpcs(__DIR__ . '/Samples/NamingClass.php', null);
        self::assertEquals(
            [
                'Private member variable "badProperty1" must contain a leading underscore',
                'Public member variable "_badProperty2" must not contain a leading underscore',
                'Protected member variable "badProperty3" must contain a leading underscore',
                'Protected or private method name "NamingClass::badMethod1" must contain a leading underscore',
                'Public method name "NamingClass::_badMethod2" must not be prefixed with underscore',
                'Protected or private method name "NamingClass::badMethod3" must contain a leading underscore',
            ],
            $result
        );
    }
}
