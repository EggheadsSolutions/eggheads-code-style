<?php

namespace Eggheads\MessDetector;

use PDepend\Source\AST\ASTParameter;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed_;
use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Rule\MethodAware;

class ArrayType extends AbstractRule implements MethodAware
{
    /**
     * @param AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        $methodComment = $node->getComment();
        if (empty($methodComment)) {
            return;
        }
        $docBlock = DocBlockFactory::createInstance()
            ->create($methodComment);

        $params = $docBlock->getTagsByName('param');
        if (!empty($params)) {
            foreach ($params as $param) {
                $this->_checkParameterReturnType($node, $param);
            }
        }

        /** @var Return_[] $return */
        $return = $docBlock->getTagsByName('return');
        if (!empty($return) && $return[0] instanceof Return_) {
            $returnType = $return[0]->getType();
            if ((string)$returnType === 'array') {
                $this->addViolation($node, ['return array']);
            }
        } else {
            print_r($return);
        }
    }

    /**
     * Проверяем тип параметра
     *
     * @param AbstractNode $node
     * @param Param $parameter
     */
    private function _checkParameterReturnType(AbstractNode $node, Tag $parameter)
    {
        $parameterType = $parameter->getType();
        if ((string)$parameterType === 'array') {
            $this->addViolation($node, [$parameter->getName()]);
        }
    }
}
