<?php

declare(strict_types=1);

const CONTRACT_PATH = __DIR__ . '/../contracts/listings.v1.json';

$options            = getopt('', ['openapi:']);
$openApiPath        = $options['openapi'] ?? null;

if (! is_string($openApiPath) || $openApiPath === '') {
    fwrite(STDERR, "Usage: php scripts/check-api-contracts.php --openapi=/path/to/openapi.json\n");
    exit(2);
}

try {
    $contract = readJsonObject(CONTRACT_PATH);
    $openApi  = readJsonObject($openApiPath);

    assertSameValue('snabix.listings', $contract['contract'] ?? null, 'contract name');
    assertSameValue(1, $contract['version'] ?? null, 'contract version');
    assertSameValue('3.1.0', $openApi['openapi'] ?? null, 'OpenAPI version');

    validateListingContract($contract, 'publicListing');
    validateListingContract($contract, 'privateListing');
    validatePrivacyBoundary($contract);
    validateOpenApiOperations($contract, $openApi);

    fwrite(STDOUT, "Backend/frontend listing contract and OpenAPI artifact are valid.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

/**
 * @return array<string, mixed>
 */
function readJsonObject(string $path): array
{
    if (! is_file($path)) {
        throw new RuntimeException(sprintf('Contract file does not exist: %s', $path));
    }

    $contents = file_get_contents($path);

    if (! is_string($contents)) {
        throw new RuntimeException(sprintf('Cannot read contract file: %s', $path));
    }

    $decoded  = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

    if (! is_array($decoded)) {
        throw new RuntimeException(sprintf('Contract file must contain an object: %s', $path));
    }

    return $decoded;
}

/**
 * @param array<string, mixed> $contract
 */
function validateListingContract(array $contract, string $section): void
{
    $definition = $contract[$section] ?? null;

    if (! is_array($definition)) {
        throw new RuntimeException(sprintf('Missing %s contract section.', $section));
    }

    $required   = stringList($definition['requiredFields'] ?? null, $section . '.requiredFields');
    $example    = $definition['example'] ?? null;

    if (! is_array($example)) {
        throw new RuntimeException(sprintf('%s.example must be an object.', $section));
    }

    foreach ($required as $field) {
        if (! array_key_exists($field, $example)) {
            throw new RuntimeException(sprintf('%s example misses required field %s.', $section, $field));
        }
    }
}

/**
 * @param array<string, mixed> $contract
 */
function validatePrivacyBoundary(array $contract): void
{
    $public         = $contract['publicListing'] ?? null;
    $private        = $contract['privateListing'] ?? null;
    $forbidden      = stringList(
        is_array($public) ? ($public['forbiddenFields'] ?? null) : null,
        'publicListing.forbiddenFields',
    );
    $publicExample  = is_array($public) ? ($public['example'] ?? null) : null;
    $privateExample = is_array($private) ? ($private['example'] ?? null) : null;

    if (! is_array($publicExample) || ! is_array($privateExample)) {
        throw new RuntimeException('Listing examples must be objects.');
    }

    foreach ($forbidden as $field) {
        if (array_key_exists($field, $publicExample)) {
            throw new RuntimeException(sprintf('Public listing example leaks %s.', $field));
        }

        if (! array_key_exists($field, $privateExample)) {
            throw new RuntimeException(sprintf('Private listing example misses %s.', $field));
        }
    }
}

/**
 * @param array<string, mixed> $contract
 * @param array<string, mixed> $openApi
 */
function validateOpenApiOperations(array $contract, array $openApi): void
{
    $operations = $contract['openApiOperations'] ?? null;

    if (! is_array($operations)) {
        throw new RuntimeException('openApiOperations must be a list.');
    }

    foreach ($operations as $operation) {
        if (! is_array($operation)) {
            throw new RuntimeException('Every OpenAPI operation contract must be an object.');
        }

        $method   = $operation['method'] ?? null;
        $path     = $operation['path'] ?? null;
        $schema   = $operation['responseSchema'] ?? null;
        $expected = is_string($schema) ? '#/components/schemas/' . $schema : null;
        $actual   = is_string($path) && is_string($method)
            ? nestedValue($openApi, [
                'paths',
                $path,
                $method,
                'responses',
                '200',
                'content',
                'application/json',
                'schema',
                'properties',
                'data',
                '$ref',
            ])
            : null;

        if ($actual !== $expected) {
            throw new RuntimeException(sprintf(
                'OpenAPI response drift for %s %s: expected %s, got %s.',
                strtoupper((string) $method),
                (string) $path,
                (string) $expected,
                json_encode($actual, JSON_UNESCAPED_SLASHES),
            ));
        }
    }
}

/**
 * @param array<string, mixed> $value
 * @param list<string>         $path
 */
function nestedValue(array $value, array $path): mixed
{
    $current = $value;

    foreach ($path as $segment) {
        if (! is_array($current) || ! array_key_exists($segment, $current)) {
            return null;
        }

        $current = $current[$segment];
    }

    return $current;
}

/**
 * @return list<string>
 */
function stringList(mixed $value, string $path): array
{
    if (! is_array($value) || ! array_is_list($value)) {
        throw new RuntimeException(sprintf('%s must be a list.', $path));
    }

    foreach ($value as $item) {
        if (! is_string($item) || $item === '') {
            throw new RuntimeException(sprintf('%s must contain non-empty strings.', $path));
        }
    }

    if (count($value) !== count(array_unique($value))) {
        throw new RuntimeException(sprintf('%s contains duplicate fields.', $path));
    }

    return array_values($value);
}

function assertSameValue(mixed $expected, mixed $actual, string $label): void
{
    if ($actual !== $expected) {
        throw new RuntimeException(sprintf(
            'Unexpected %s: expected %s, got %s.',
            $label,
            json_encode($expected),
            json_encode($actual),
        ));
    }
}
