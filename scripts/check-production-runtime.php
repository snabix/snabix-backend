<?php

declare(strict_types=1);

const VERSIONED_IMAGE_PATTERN = '/(?:@sha256:[a-f0-9]{64}|:(?:sha-[a-f0-9]{7,40}|v?\d+\.\d+\.\d+(?:[-+][a-zA-Z0-9.-]+)?))$/';

/**
 * @return array<string, string>
 */
function readEnvironmentFile(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException(sprintf('Cannot read runtime env file [%s].', $path));
    }

    $values = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $values[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }

    return $values;
}

function isVersionedImage(string $reference): bool
{
    return preg_match(VERSIONED_IMAGE_PATTERN, $reference) === 1
        && ! str_ends_with(strtolower($reference), ':latest');
}

function assertRuntimeContract(string $root, string $runtimeEnvPath): void
{
    $environment = readEnvironmentFile($runtimeEnvPath);

    foreach (['BACKEND_IMAGE', 'FRONTEND_IMAGE', 'BOT_IMAGE'] as $key) {
        $reference = $environment[$key] ?? '';

        if (! isVersionedImage($reference)) {
            throw new RuntimeException(sprintf(
                '%s must use an immutable digest, sha-* tag or semantic version; got [%s].',
                $key,
                $reference,
            ));
        }
    }

    $composePath = $root . '/compose.production.yaml';
    $compose     = file_get_contents($composePath);

    if ($compose === false) {
        throw new RuntimeException(sprintf('Cannot read [%s].', $composePath));
    }

    foreach (['db:', 'postgres:', 'redis:', 'rabbitmq:'] as $forbiddenService) {
        if (preg_match('/^  ' . preg_quote($forbiddenService, '/') . '/m', $compose) === 1) {
            throw new RuntimeException(sprintf(
                'Production Compose must not own the stateful service [%s].',
                rtrim($forbiddenService, ':'),
            ));
        }
    }

    foreach (['build:', 'volumes:'] as $forbiddenDirective) {
        if (preg_match('/^  ' . preg_quote($forbiddenDirective, '/') . '/m', $compose) === 1) {
            throw new RuntimeException(sprintf(
                'Production Compose must not contain a top-level [%s] directive.',
                rtrim($forbiddenDirective, ':'),
            ));
        }
    }

    foreach (['app', 'worker', 'scheduler', 'frontend', 'web', 'bot'] as $component) {
        if (! str_contains($compose, sprintf('  %s:', $component))) {
            throw new RuntimeException(sprintf('Missing production component [%s].', $component));
        }
    }

    if (! str_contains($compose, "profiles:\n      - operations")) {
        throw new RuntimeException('Migrations must remain behind the operations profile.');
    }
}

function runSelfTest(): void
{
    $valid = [
        'registry.example/app@sha256:' . str_repeat('a', 64),
        'registry.example/app:sha-0123456789abcdef',
        'registry.example/app:v1.2.3',
    ];
    $invalid = [
        '',
        'registry.example/app',
        'registry.example/app:latest',
        'registry.example/app:dev',
    ];

    foreach ($valid as $reference) {
        if (! isVersionedImage($reference)) {
            throw new RuntimeException(sprintf('Self-test rejected valid image [%s].', $reference));
        }
    }

    foreach ($invalid as $reference) {
        if (isVersionedImage($reference)) {
            throw new RuntimeException(sprintf('Self-test accepted unsafe image [%s].', $reference));
        }
    }

    fwrite(STDOUT, "Production runtime guard self-test passed.\n");
}

$root = dirname(__DIR__);

if (($argv[1] ?? null) === '--self-test') {
    runSelfTest();
    exit(0);
}

if (($argv[1] ?? null) !== '--env-file' || ! isset($argv[2])) {
    fwrite(STDERR, "Usage: php scripts/check-production-runtime.php --env-file <runtime.env>\n");
    exit(2);
}

try {
    assertRuntimeContract($root, $argv[2]);
    fwrite(STDOUT, "Production runtime contract is valid.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
