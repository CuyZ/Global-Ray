<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'RayGlobalScoped',

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
        'rd',
    ],
    'patchers' => [
        function (string $filePath, string $prefix, string $content): string {
            // Adding configuration to `composer.json` file that will override
            // the autoloader suffix, otherwise it will conflict with the one
            // from this package.
            if ($filePath === __DIR__ . '/composer.json') {
                $json = json_decode($content, true);
                $json['config']['autoloader-suffix'] = 'RayGlobalScoped';

                return json_encode($json);
            }

            return $content;
        },
    ],
    'whitelist-global-constants' => true,
    'whitelist-global-classes' => false,
    'whitelist-global-functions' => false,
];
