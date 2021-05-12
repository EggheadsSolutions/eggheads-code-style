<?php
declare(strict_types=1);

namespace App\CodeSniffer\Samples;

class NamingClass
{
    /** @var int */
    private $badProperty1;

    /** @var string */
    public $_badProperty2;

    /** @var string */
    protected $badProperty3;

    /** @var int */
    protected $_goodProperty1;

    /** @var string */
    public $goodProperty2;

    /** @var string */
    protected $_goodProperty3;

    /** comment */
    private function badMethod1()
    {
    }

    /** comment */
    public function _badMethod2()
    {
    }

    /** comment */
    protected function badMethod3()
    {
    }

    /** comment */
    protected function _goodMethod1()
    {
    }

    /** comment */
    private function _goodMethod2()
    {
    }

    /** comment */
    public function goodMethod3()
    {
    }
}
