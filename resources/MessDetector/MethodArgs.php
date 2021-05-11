<?php

namespace Eggheads\MessDetector;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Rule\MethodAware;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * Проверка на корректность описания тэгов "@param" и "@return".
 * Запрещено использовать тип array без описания формата, для решения данной задачи смотри https://suckup.de/2020/02/modern-phpdoc-annotations/
 * Отключается следующей конструкцией: "@SuppressWarnings(PHPMD.MethodArgs)"
 */
class MethodArgs extends AbstractRule implements MethodAware
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

        $lexer = new Lexer();
        $tokens = new TokenIterator($lexer->tokenize($methodComment));
        $constExprParser = new ConstExprParser();
        $phpDocParser = new PhpDocParser(new TypeParser($constExprParser), $constExprParser);
        $actualPhpDocNode = $phpDocParser->parse($tokens);
        $parameters = $actualPhpDocNode->getTagsByName('@param');

        if (count($node->getNode()->getParameters()) !== count($parameters)
            && empty($actualPhpDocNode->getTagsByName('@inheritdoc'))
            && empty($actualPhpDocNode->getTagsByName('@inheritDoc'))) {
            $this->addViolation($node, ['Some parameters are not described in phpDoc block']);
        }

        foreach ($parameters as $parameter) {
            $this->_checkTypeBlock($node, $parameter);
        }

        $return = $actualPhpDocNode->getTagsByName('@return');
        if (!empty($return)) {
            $this->_checkTypeBlock($node, array_pop($return));
        }
    }

    /**
     * Проверяем тип параметра
     *
     * @param AbstractNode $node
     * @param PhpDocTagNode $parameter
     */
    private function _checkTypeBlock(AbstractNode $node, PhpDocTagNode $parameter)
    {
        /** @var ParamTagValueNode|ReturnTagValueNode $valueNode */
        $valueNode = $parameter->value;
        if ($valueNode instanceof InvalidTagValueNode) {
            $this->addViolation($node, ['Incorrect phpDoc: "' . $valueNode->value . '"']);
            return;
        }

        if ($valueNode->parameterName == '$badInput3') {
            print_r($valueNode->type);
        }

        if ($valueNode->type instanceof UnionTypeNode) {
            $types = $valueNode->type->types;
        } elseif ($valueNode->type instanceof NullableTypeNode) {
            $types = [$valueNode->type->type];
        } else {
            $types = [$valueNode->type];
        }

        foreach ($types as $type) {
            if ($type instanceof IdentifierTypeNode && $type->name === 'array') {
                if ($valueNode instanceof ReturnTagValueNode) {
                    $message = 'Describe result array';
                } else {
                    $message = 'Describe array ' . $valueNode->parameterName;
                }

                $this->addViolation($node, [$message]);
            }
        }
    }
}
