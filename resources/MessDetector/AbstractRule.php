<?php

namespace Eggheads\MessDetector;

use PDepend\Source\AST\ASTNode;
use PHPMD\AbstractNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

abstract class AbstractRule extends \PHPMD\AbstractRule
{
    protected const INHERIT_DOC_TAGS = [
        '@inheritdoc',
        '@inheritDoc',
    ];

    /**
     * Получаем дерево PHPDoc описания
     *
     * @param AbstractNode|ASTNode $node
     * @return PhpDocNode|null
     */
    protected function _getPhpDocNode($node): ?PhpDocNode
    {
        $methodComment = $node->getComment();
        if (empty($methodComment)) {
            return null;
        }

        $lexer = new Lexer();
        $tokens = new TokenIterator($lexer->tokenize($methodComment));
        $constExprParser = new ConstExprParser();
        $phpDocParser = new PhpDocParser(new TypeParser($constExprParser), $constExprParser);
        return $phpDocParser->parse($tokens);
    }

    /**
     * Проверяем тип параметра
     *
     * @param AbstractNode $node
     * @param PhpDocTagNode $parameter
     */
    protected function _checkTypeBlock(AbstractNode $node, PhpDocTagNode $parameter)
    {
        /** @var ParamTagValueNode|ReturnTagValueNode $valueNode */
        $valueNode = $parameter->value;
        if ($valueNode instanceof InvalidTagValueNode) {
            $this->addViolation($node, ['Incorrect phpDoc: "' . $valueNode->value . '"']);
            return;
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
                } elseif ($valueNode instanceof VarTagValueNode) {
                    $message = 'Describe array';
                } else {
                    $message = 'Describe array ' . $valueNode->parameterName;
                }

                $this->addViolation($node, [$message]);
            }
        }
    }
}
