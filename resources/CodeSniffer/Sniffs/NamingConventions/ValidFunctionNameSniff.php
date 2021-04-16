<?php
declare(strict_types=1);

/**
 * Проверка названия методов на наличие underscore
 */

namespace App\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;

class ValidFunctionNameSniff extends AbstractScopeSniff
{

    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    protected $_magicMethods = [
        'construct',
        'destruct',
        'call',
        'callStatic',
        'debugInfo',
        'get',
        'set',
        'isset',
        'unset',
        'sleep',
        'wakeup',
        'toString',
        'set_state',
        'clone',
        'invoke',
    ];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct([T_CLASS, T_INTERFACE, T_TRAIT], [T_FUNCTION], true);
    }

    /**
     * @inheritDoc
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        $className = $phpcsFile->getDeclarationName($currScope);
        $errorData = [$className . '::' . $methodName];

        // Ignore magic methods
        if (preg_match('/^__(' . implode('|', $this->_magicMethods) . ')$/', $methodName)) {
            return;
        }

        $methodProps = $phpcsFile->getMethodProperties($stackPtr);
        if ($methodProps['scope_specified'] === false) {
            // Let another sniffer take care of that
            return;
        }

        $isPublic = $methodProps['scope'] === 'public';

        if ($isPublic === true && $methodName[0] === '_') {
            $error = 'Public method name "%s" must not be prefixed with underscore';
            $phpcsFile->addError($error, $stackPtr, 'PublicWithUnderscore', $errorData);

            return;
        } elseif ($isPublic === false && $methodName[0] !== '_') {
            $error = 'Protected or private method name "%s" must contain a leading underscore';
            $phpcsFile->addError($error, $stackPtr, 'PublicWithUnderscore', $errorData);

            return;
        }
    }

    /**
     * @inheritDoc
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
    }
}
