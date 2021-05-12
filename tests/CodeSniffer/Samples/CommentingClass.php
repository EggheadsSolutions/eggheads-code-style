<?php
declare(strict_types=1);

namespace App\CodeSniffer\Samples;

class CommentingClass
{
    /** @var int */
    public $propGood;

    public $propBad1;

    /** dsfdsf */
    public $propBad2;

    public function methodBad()
    {
    }

    /**
     * gogogo
     */
    public function methodGood(int $prop)
    {
    }
}
