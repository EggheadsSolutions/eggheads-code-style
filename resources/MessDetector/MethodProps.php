<?php


namespace Eggheads\MessDetector;

use PHPMD\Node\ASTNode;
use PHPMD\Node\ClassNode;
use PHPMD\Rule\ClassAware;
use PHPMD\AbstractNode;

/**
 * Проверка на корректность описания тэгов "@var" для свойсва.
 * Запрещено использовать тип array без описания формата, для решения данной задачи смотри https://suckup.de/2020/02/modern-phpdoc-annotations/
 * Отключается следующей конструкцией: "@SuppressWarnings(PHPMD.MethodProps)"
 */
class MethodProps extends AbstractRule implements ClassAware
{
    /**
     * @inheritDoc
     * @param ClassNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        $fields = $node->findChildrenOfType('FieldDeclaration');
        foreach ($fields as $field) {
            $declarators = $field->findChildrenOfType('VariableDeclarator');
            foreach ($declarators as $declarator) {
                $this->_checkVariable($declarator);
            }
        }
    }

    /**
     * Проверка переменной
     *
     * @param ASTNode $node
     * @return void
     */
    private function _checkVariable(ASTNode $node)
    {
        $actualPhpDocNode = $this->_getPhpDocNode($node->getNode()->getParent());
        if (empty($actualPhpDocNode)) {
            return;
        }
        $isInheritDoc = false;
        foreach (self::INHERIT_DOC_TAGS as $inheritDocTag) {
            if (!empty($actualPhpDocNode->getTagsByName($inheritDocTag))) {
                $isInheritDoc = true;
            }
        }
        $varDoc = $actualPhpDocNode->getTagsByName('@var');
        if (empty($varDoc) && !$isInheritDoc) {
            $this->addViolation($node, ['Empty @var tag']);
            return;
        } elseif (empty($varDoc)) {
            return;
        }

        $this->_checkTypeBlock($node, array_pop($varDoc));
    }
}
