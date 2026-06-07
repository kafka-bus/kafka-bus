<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushNextDevReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker;

return static function (MBConfig $config): void {
    // Директории с пакетами
    $config->packageDirectories([
        __DIR__ . '/packages',
    ]);

    // Пакеты, версии которых синхронизируются автоматически
    $config->packageAliasFormat('<major>.<minor>.x-dev');

    // Воркеры релиза — выполняются по порядку при `monorepo-builder release x.y.z`
    $config->workers([
        // 1. Обновить взаимные зависимости до точной версии (^x.y.z)
        SetCurrentMutualDependenciesReleaseWorker::class,
        // 2. Обновить поле "replace" в composer.json
        UpdateReplaceReleaseWorker::class,
        // 3. Добавить запись в CHANGELOG
        AddTagToChangelogReleaseWorker::class,
        // 4. Создать git-тег
        TagVersionReleaseWorker::class,
        // 5. Запушить тег — триггер для split-воркфлоу
        PushTagReleaseWorker::class,
        // 6. Переключить взаимные зависимости на следующую dev-версию
        SetNextMutualDependenciesReleaseWorker::class,
        // 7. Обновить alias ветки
        UpdateBranchAliasReleaseWorker::class,
        // 8. Запушить dev-коммит
        PushNextDevReleaseWorker::class,
    ]);
};
