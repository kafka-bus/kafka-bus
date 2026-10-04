#!/usr/bin/env bash
# Подставляет в packages/*/composer.json вместо "*" ограничение вида ^MAJOR.MINOR для зависимостей kafka-bus/*.
# Запускается в split-воркфлоу перед раскладкой пакетов по репозиториям: правка попадает только в сплит-репозитории,
# в монорепозитории остаётся "*" (локально пакеты подключаются автозагрузкой корня).
#
# Использование: .github/scripts/pin-interdependencies.sh v1.3.0   # → "^1.3"
set -euo pipefail

tag="${1:?Usage: $0 <tag, e.g. v1.3.0>}"
version="${tag#v}"

if [[ ! $version =~ ^([0-9]+)\.([0-9]+)\.[0-9]+ ]]; then
    echo "Tag [$tag] is not a semantic version" >&2
    exit 1
fi

constraint="^${BASH_REMATCH[1]}.${BASH_REMATCH[2]}"
cd "$(dirname "$0")/../.."

for file in packages/*/composer.json; do
    tmp="$(mktemp)"

    jq --indent 4 --arg constraint "$constraint" '
        def pin: with_entries(if (.key | startswith("kafka-bus/")) then .value = $constraint else . end);
        (if .require then .require |= pin else . end)
        | (if .["require-dev"] then .["require-dev"] |= pin else . end)
    ' "$file" > "$tmp"

    mv "$tmp" "$file"
    echo "pinned $file → $constraint"
done
