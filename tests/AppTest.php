<?php
declare(strict_types=1);

namespace App;

use PHPUnit\Framework\TestCase;

abstract class AppTest extends TestCase
{
    /**
     * Запуск файла $testFile на проверку по правилу $testRule
     *
     * @param string $testFile
     * @param string $testRule
     * @return string[]
     */
    protected function _executePhpmd(string $testFile, string $testRule): array
    {
        $curDir = __DIR__;
        chdir($curDir . '/..');

        exec('vendor/bin/phpmd "' . $testFile . '" xml rules/phpmd-ruleset.xml', $execOutput);

        $xml = simplexml_load_string(implode("\n", $execOutput));
        $messages = [];
        if (!empty($xml->file)) {
            foreach ($xml->file as $xmlFile) {
                foreach ($xmlFile->violation as $errorMessage) {
                    if ((string)$errorMessage['rule'] === $testRule) {
                        $messages[] = trim((string)$errorMessage);
                    }
                }
            }
        }

        chdir($curDir);
        return $messages;
    }

    /**
     * Запуск phpcs
     *
     * @param string $test
     * @param ?string $rule
     * @return array
     */
    protected function _executePhpcs(string $test, ?string $rule): array
    {
        $curDir = __DIR__;
        chdir($curDir . '/..');
        exec('vendor/bin/phpcs --report=json "' . $test . '"', $execOutput);

        $result = json_decode(implode("\n", $execOutput), true);
        $messages = [];
        foreach ($result['files'] as $file) {
            foreach ($file['messages'] as $message) {
                if (empty($rule) || $message['source'] === $rule) {
                    $messages[] = $message['message'];
                }
            }
        }
        chdir($curDir);
        return $messages;
    }
}
