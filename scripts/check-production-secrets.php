<?php

declare(strict_types=1);

/**
 * Production env guard.
 *
 * Local examples may keep docker-friendly placeholders, but staging/production
 * env files must never reuse them.
 */

/** @return array<string, string> */
function parseEnvFile(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES);

    if ($lines === false) {
        throw new RuntimeException(sprintf('Cannot read env file: %s', $path));
    }

    $values = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
    }

    return $values;
}

/** @param array<string, string> $values */
function findUnsafeProductionSecrets(array $values): array
{
    $findings = [];
    $placeholderValues = [
        'change-me',
        'replace-me',
        'replace-with-backend-service-token',
        'replace-with-bot-token',
        'replace-with-random-webhook-secret',
        'long-random-token',
    ];
    $placeholderVariables = [
        'APP_KEY',
        'DB_PASSWORD',
        'DB_TEST_PASSWORD',
        'RABBITMQ_PASSWORD',
        'MAIL_PASSWORD',
        'SNABIX_BOT_SERVICE_TOKEN',
        'SNABIX_BACKEND_SERVICE_TOKEN',
        'SNABIX_BOT_TOKEN',
        'SNABIX_BOT_WEBHOOK_SECRET',
    ];

    foreach ($placeholderVariables as $variable) {
        $value = $values[$variable] ?? null;

        if ($value === null) {
            continue;
        }

        if ($value === '') {
            $findings[] = sprintf('%s is empty; production must use a generated value.', $variable);

            continue;
        }

        if (in_array(strtolower($value), $placeholderValues, true)) {
            $findings[] = sprintf('%s uses unsafe placeholder "%s".', $variable, $value);
        }
    }

    foreach (['DB_USERNAME', 'DB_TEST_USERNAME'] as $variable) {
        if (($values[$variable] ?? null) === 'root') {
            $findings[] = sprintf('%s uses local-only value "root".', $variable);
        }
    }

    foreach (['DB_PASSWORD', 'DB_TEST_PASSWORD'] as $variable) {
        if (($values[$variable] ?? null) === '1234') {
            $findings[] = sprintf('%s uses local-only password "1234".', $variable);
        }
    }

    if (($values['RABBITMQ_USER'] ?? null) === 'guest') {
        $findings[] = 'RABBITMQ_USER uses local-only value "guest".';
    }

    if (($values['RABBITMQ_PASSWORD'] ?? null) === 'guest') {
        $findings[] = 'RABBITMQ_PASSWORD uses local-only password "guest".';
    }

    foreach (['SNABIX_BOT_SERVICE_TOKEN', 'SNABIX_BACKEND_SERVICE_TOKEN', 'SNABIX_BOT_WEBHOOK_SECRET'] as $variable) {
        $value = $values[$variable] ?? null;

        if ($value !== null && $value !== '' && strlen($value) < 32) {
            $findings[] = sprintf('%s is too short; use at least 32 random characters.', $variable);
        }
    }

    return $findings;
}

function runSelfTest(): int
{
    $unsafeFindings = findUnsafeProductionSecrets([
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => '1234',
        'RABBITMQ_USER' => 'guest',
        'RABBITMQ_PASSWORD' => 'guest',
        'SNABIX_BOT_SERVICE_TOKEN' => 'change-me',
        'SNABIX_BACKEND_SERVICE_TOKEN' => 'replace-with-backend-service-token',
    ]);

    $safeFindings = findUnsafeProductionSecrets([
        'DB_USERNAME' => 'snabix_app',
        'DB_PASSWORD' => 'production-db-password-from-secret-store',
        'RABBITMQ_USER' => 'snabix_queue',
        'RABBITMQ_PASSWORD' => 'production-rabbitmq-password-from-secret-store',
        'SNABIX_BOT_SERVICE_TOKEN' => str_repeat('a', 64),
        'SNABIX_BACKEND_SERVICE_TOKEN' => str_repeat('b', 64),
    ]);

    if ($unsafeFindings === [] || $safeFindings !== []) {
        fwrite(STDERR, "Production secret guard self-test failed.\n");

        return 1;
    }

    echo "Production secret guard self-test passed.\n";

    return 0;
}

/** @return array<string, string|true> */
function parseArguments(array $arguments): array
{
    $options = [];

    foreach (array_slice($arguments, 1) as $argument) {
        if ($argument === '--self-test') {
            $options['self-test'] = true;

            continue;
        }

        if (str_starts_with($argument, '--env-file=')) {
            $options['env-file'] = substr($argument, strlen('--env-file='));

            continue;
        }

        throw new InvalidArgumentException(sprintf('Unknown argument: %s', $argument));
    }

    return $options;
}

try {
    $options = parseArguments($argv);

    if (($options['self-test'] ?? false) === true) {
        exit(runSelfTest());
    }

    $envFile = (string) ($options['env-file'] ?? getenv('PRODUCTION_ENV_FILE') ?: '');

    if ($envFile === '') {
        fwrite(STDERR, "Usage: php scripts/check-production-secrets.php --env-file=/path/to/.env.production\n");

        exit(1);
    }

    $findings = findUnsafeProductionSecrets(parseEnvFile($envFile));

    if ($findings !== []) {
        fwrite(STDERR, sprintf("Unsafe production secrets found in %s:\n", $envFile));

        foreach ($findings as $finding) {
            fwrite(STDERR, sprintf("- %s\n", $finding));
        }

        exit(1);
    }

    echo sprintf("Production secrets check passed: %s\n", $envFile);
} catch (Throwable $exception) {
    fwrite(STDERR, sprintf("%s\n", $exception->getMessage()));

    exit(1);
}
