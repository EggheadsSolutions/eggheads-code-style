# Установка

- В composer.json в require-dev прописываем:
  `"eggheads/eggheads-code-style": "dev-master"`
- Не забываем указывать там же:
  `"minimum-stability": "dev"`
- Подключаем репозиторий:

```json
{
    "type": "vcs",
    "url": "git://github.com/vovantune/eggheads-code-style"
}
```

# Настройка Php code sniffer

- Создаём в корне проекта файл phpcs.xml.dist вида:

```xml
<?xml version="1.0" encoding="UTF-8"?>

<ruleset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/squizlabs/php_codesniffer/phpcs.xsd">

    <arg name="basepath" value="."/>
    <arg name="cache" value=".phpcs-cache"/>
    <arg name="colors"/>
    <arg name="extensions" value="php,ctp"/>
    <arg name="parallel" value="75"/>

    <rule ref="vendor/eggheads/eggheads-code-style/rules/phpcs-rules.xml"/>

    <file>src/</file>
    <file>tests/</file>
</ruleset>
```

- Запуск сниффера:
  `vendor/bin/phpcs`
- Автоисправление:
  `vendor/bin/phpcbf`
- Запуск в TeamCity:
  `vendor/bin/phpcs --report=\\setasign\\PhpcsTeamcityReport\\TeamcityReport`

# Настройка Php Mess Detector

- Создаём в корне проекта файл phpmd-ruleset.xml вида:

```xml
<?xml version="1.0"?>
<ruleset name="Eggheads code style rule set"
         xmlns="http://pmd.sf.net/ruleset/1.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0
                     http://pmd.sf.net/ruleset_xml_schema.xsd"
         xsi:noNamespaceSchemaLocation="
                     http://pmd.sf.net/ruleset_xml_schema.xsd">

    <rule ref="vendor/eggheads/eggheads-code-style/rules/phpmd-ruleset.xml"/>
</ruleset>
```

- Запуск phpmd:
  `vendor/bin/phpmd src/,tests/ text phpmd-ruleset.xml`
