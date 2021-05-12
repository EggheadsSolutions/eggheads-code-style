<?php

namespace Eggheads\MessDetector;

use PHPMD\AbstractNode;
use PHPMD\Node\MethodNode;
use PHPMD\Rule\MethodAware;

/**
 * Проверка на корректность описания тэгов "@param" и "@return".
 * Запрещено использовать тип array без описания формата, для решения данной задачи смотри https://suckup.de/2020/02/modern-phpdoc-annotations/
 * Отключается следующей конструкцией: "@SuppressWarnings(PHPMD.MethodArgs)"
 */
class MethodArgs extends AbstractRule implements MethodAware
{
    /**
     * @inheritDoc
     * @param MethodNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        $actualPhpDocNode = $this->_getPhpDocNode($node);
        if (empty($actualPhpDocNode)) {
            return;
        }

        $parameters = $actualPhpDocNode->getTagsByName('@param');

        if (count($node->getNode()->getParameters()) !== count($parameters)
            && empty($actualPhpDocNode->getTagsByName(self::INHERIT_DOC_TAGS[0]))
            && empty($actualPhpDocNode->getTagsByName(self::INHERIT_DOC_TAGS[1]))) {
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
}
