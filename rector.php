<?php

declare(strict_types=1);

use OpenErpByJsonRpc\Rector\SnakeCasePropertyToCamelCaseRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpVersion(Rector\ValueObject\PhpVersion::PHP_85)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
    )
    ->withRules([
        SnakeCasePropertyToCamelCaseRector::class,
    ])
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    // Entre en conflit avec le renommage snake_case → camelCase :
    // il croit que les propriétés renommées sont dynamiques et les redéclare.
    ->withSkip([
        CompleteDynamicPropertiesRector::class,
    ])
;
