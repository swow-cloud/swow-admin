<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames(true, false);

    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/cloud-admin'
    ]);

     $rectorConfig->sets([
         LevelSetList::UP_TO_PHP_81,
     ]);
    // register a single rule
    // $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    // $rectorConfig->sets([
    //     LevelSetList::UP_TO_PHP_80,
    // ]);

    $rectorConfig
        ->ruleWithConfiguration(RenameClassRector::class, [
            'Hyperf\Utils\ApplicationContext' => 'Hyperf\Context\ApplicationContext',
            'Hyperf\Utils\Arr' => 'Hyperf\Collection\Arr',
            'Hyperf\Utils\Collection' => 'Hyperf\Collection\Collection',
            'Hyperf\Utils\Coroutine' => 'Hyperf\Coroutine\Coroutine',
            'Hyperf\Utils\Coroutine\Concurrent' => 'Hyperf\Coroutine\Concurrent',
            'Hyperf\Utils\Str' => 'Hyperf\Stringable\Str',
            'Hyperf\Utils\Stringable' => 'Hyperf\Stringable\Stringable',
        ]);

    $rectorConfig
        ->ruleWithConfiguration(RenameFunctionRector::class, [
            'collect' => 'Hyperf\Collection\collect',
            'data_fill' => 'Hyperf\Collection\data_fill',
            'data_get' => 'Hyperf\Collection\data_get',
            'data_set' => 'Hyperf\Collection\data_set',
            'value' => 'Hyperf\Collection\value',

            'tap' => 'Hyperf\Tappable\tap',

            'co' => 'Hyperf\Coroutine\co',
            'defer' => 'Hyperf\Coroutine\defer',
            'go' => 'Hyperf\Coroutine\go',
            'parallel' => 'Hyperf\Coroutine\parallel',
            'run' => 'Hyperf\Coroutine\run',
            'wait' => 'Hyperf\Coroutine\wait',
        ]);
};