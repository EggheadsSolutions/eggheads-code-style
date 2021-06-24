<?php
/**
 * Parses and verifies the variable doc comment.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Util\Common;

class VariableCommentSniff extends AbstractVariableSniff
{
    /**
     * @inheritDoc
     */
    public function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $ignore = [
            T_PUBLIC,
            T_PRIVATE,
            T_PROTECTED,
            T_VAR,
            T_STATIC,
            T_WHITESPACE,
            T_STRING,
            T_NS_SEPARATOR,
            T_NULLABLE,
        ];

        $commentEnd = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if ($commentEnd === false
            || ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
                && $tokens[$commentEnd]['code'] !== T_COMMENT)
        ) {
            $phpcsFile->addError('Missing member variable doc comment', $stackPtr, 'Missing');
            return;
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a member variable comment', $stackPtr, 'WrongStyle');
            return;
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        $foundVar = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if (in_array(strtolower($tokens[$tag]['content']), ['@inheritdoc', '@deprecated', '@SerializedName'])) {
                return;
            }
            if (strpos($tokens[$tag]['content'], '@OA\Property') === 0) {
                return;
            }
            if ($tokens[$tag]['content'] === '@var') {
                if ($foundVar !== null) {
                    $error = 'Only one @var tag is allowed in a member variable comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateVar');
                } else {
                    $foundVar = $tag;
                }
            } elseif ($tokens[$tag]['content'] === '@see') {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $error = 'Content missing for @see tag in member variable comment';
                    $phpcsFile->addError($error, $tag, 'EmptySees');
                }
            } else {
                $error = '%s tag is not allowed in member variable comment';
                $data = [$tokens[$tag]['content']];
                $phpcsFile->addWarning($error, $tag, 'TagNotAllowed', $data);
            }//end if
        }//end foreach

        // The @var tag is the only one we require.
        if ($foundVar === null) {
            $error = 'Missing @var tag in member variable comment';
            $phpcsFile->addError($error, $commentEnd, 'MissingVar');
            return;
        }

        $firstTag = $tokens[$commentStart]['comment_tags'][0];
        if ($foundVar !== null && $tokens[$firstTag]['content'] !== '@var') {
            $error = 'The @var tag must be the first tag in a member variable comment';
            $phpcsFile->addError($error, $foundVar, 'VarOrder');
        }

        // Make sure the tag isn't empty and has the correct padding.
        $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $foundVar, $commentEnd);
        if ($string === false || $tokens[$string]['line'] !== $tokens[$foundVar]['line']) {
            $error = 'Content missing for @var tag in member variable comment';
            $phpcsFile->addError($error, $foundVar, 'EmptyVar');
            return;
        }

        // Support both a var type and a description.
        preg_match('`^((?:\|?(?:array\([^\)]*\)|[\\\\a-z0-9\[\]]+))*)( .*)?`i', $tokens[($foundVar + 2)]['content'], $varParts);
        if (isset($varParts[1]) === false) {
            return;
        }

        $varType = $varParts[1];

        // Check var type (can be multiple, separated by '|').
        $typeNames = explode('|', $varType);
        $suggestedNames = [];
        foreach ($typeNames as $i => $typeName) {
            $suggestedName = $this->suggestType($typeName);
            if (in_array($suggestedName, $suggestedNames, true) === false) {
                $suggestedNames[] = $suggestedName;
            }
        }

        $suggestedType = implode('|', $suggestedNames);
        if ($varType !== $suggestedType) {
            $error = 'Expected "%s" but found "%s" for @var tag in member variable comment';
            $data = [
                $suggestedType,
                $varType,
            ];
            $fix = $phpcsFile->addFixableError($error, $foundVar, 'IncorrectVarType', $data);
            if ($fix === true) {
                $replacement = $suggestedType;
                if (empty($varParts[2]) === false) {
                    $replacement .= $varParts[2];
                }

                $phpcsFile->fixer->replaceToken(($foundVar + 2), $replacement);
                unset($replacement);
            }
        }

    }//end processMemberVar()

    /**
     * @inheritDoc
     */
    protected function suggestType($varType)
    {
        if ($varType === '') {
            return '';
        }

        $allowedTypes = [
            'array',
            'boolean',
            'float',
            'integer',
            'mixed',
            'object',
            'string',
            'resource',
            'callable',
        ];

        if (in_array($varType, $allowedTypes, true) === true) {
            return $varType;
        } else {
            $lowerVarType = strtolower($varType);
            switch ($lowerVarType) {
                case 'bool':
                case 'boolean':
                    return 'bool';
                case 'double':
                case 'real':
                case 'float':
                    return 'float';
                case 'int':
                case 'integer':
                    return 'int';
                case 'array()':
                case 'array':
                    return 'array';
            }//end switch

            if (strpos($lowerVarType, 'array(') !== false) {
                // Valid array declaration:
                // array, array(type), array(type1 => type2).
                $matches = [];
                $pattern = '/^array\(\s*([^\s^=^>]*)(\s*=>\s*(.*))?\s*\)/i';
                if (preg_match($pattern, $varType, $matches) !== 0) {
                    $type1 = '';
                    if (isset($matches[1]) === true) {
                        $type1 = $matches[1];
                    }

                    $type2 = '';
                    if (isset($matches[3]) === true) {
                        $type2 = $matches[3];
                    }

                    $type1 = $this->suggestType($type1);
                    $type2 = $this->suggestType($type2);
                    if ($type2 !== '') {
                        $type2 = ' => ' . $type2;
                    }

                    return "array($type1$type2)";
                } else {
                    return 'array';
                }//end if
            } elseif (in_array($lowerVarType, $allowedTypes, true) === true) {
                // A valid type, but not lower cased.
                return $lowerVarType;
            } else {
                // Must be a custom type name.
                return $varType;
            }//end if
        }//end if

    }//end suggestType()


    /**
     * @inheritDoc
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {

    }//end processVariable()


    /**
     * @inheritDoc
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {

    }//end processVariableInString()


}//end class
