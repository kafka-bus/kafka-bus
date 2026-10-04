<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;

// Релизы идут через GitHub Release (тег v*.*.* → split.yml, CHANGELOG → update-changelog.yml),
// поэтому release-воркеры здесь не нужны — monorepo-builder используется только для `validate`.
return static function (MBConfig $config): void {
    $config->packageDirectories([
        __DIR__ . '/packages',
    ]);
};
