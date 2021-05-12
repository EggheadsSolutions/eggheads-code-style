<?php

namespace Eggheads\Test\MessDetector\Samples;

class TestClass2
{
    /** @var array */
    public $badArrayProp;

    /** @var int[] */
    public $propGood1;

    /** @var array<int, int> */
    public $propGood2;

    /**
     * sdsdfdsfdsf
     */
    protected $_noVarDoc;

    protected $_emptyDoc;
}
