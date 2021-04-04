<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude([
                'doc',
                'test',
                'tests',
                'Tests',
                'vendor-bin',
            ])
            ->in(__DIR__ . '/vendor/'),
        Finder::create()->append([
            __DIR__ . '/composer.json',
        ]),
    ],
    'whitelist' => [
        'ray',
        'rd'
    ],
    'whitelist-global-constants' => true,
    'whitelist-global-classes' => false,
    'whitelist-global-functions' => false,
];
