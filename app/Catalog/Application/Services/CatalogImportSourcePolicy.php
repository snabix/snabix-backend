<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use InvalidArgumentException;
use RuntimeException;

use function array_map;
use function array_values;
use function is_array;
use function is_bool;
use function is_file;
use function is_int;
use function is_string;
use function parse_url;
use function realpath;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use function strtolower;
use function trim;

use const DIRECTORY_SEPARATOR;
use const PHP_URL_FRAGMENT;
use const PHP_URL_HOST;
use const PHP_URL_PASS;
use const PHP_URL_PORT;
use const PHP_URL_QUERY;
use const PHP_URL_SCHEME;
use const PHP_URL_USER;

class CatalogImportSourcePolicy
{
    public function requiresExplicitExternalIds(string $source): bool
    {
        return ($this->definition($source)['require_explicit_external_ids'] ?? true) === true;
    }

    public function version(string $source): string
    {
        $version = $this->definition($source)['version'] ?? null;

        if (! is_string($version) || trim($version) === '') {
            throw new RuntimeException(sprintf('Для источника [%s] не задана версия контракта.', $source));
        }

        return trim($version);
    }

    public function authorizeNetworkUrl(string $source, ?string $url = null): string
    {
        $definition      = $this->definition($source);
        $networkEnabled  = $definition['network_enabled'] ?? false;
        $rightsReference = $definition['rights_reference'] ?? null;

        if (! is_bool($networkEnabled) || ! $networkEnabled) {
            throw new RuntimeException(sprintf(
                'Сетевой импорт [%s] отключен. Используйте утвержденную fixture или отдельное письменное разрешение.',
                $source,
            ));
        }

        if (! is_string($rightsReference) || trim($rightsReference) === '') {
            throw new RuntimeException(sprintf(
                'Сетевой импорт [%s] требует CATALOG_IMPORT_PROM_RIGHTS_REFERENCE с идентификатором письменного разрешения.',
                $source,
            ));
        }

        $resolvedUrl     = $url ?? ($definition['url'] ?? null);

        if (! is_string($resolvedUrl) || trim($resolvedUrl) === '') {
            throw new InvalidArgumentException(sprintf('Для источника [%s] не задан URL.', $source));
        }

        $resolvedUrl     = trim($resolvedUrl);

        if (mb_strlen($resolvedUrl) > 2048) {
            throw new InvalidArgumentException('URL источника превышает допустимую длину.');
        }

        $scheme          = parse_url($resolvedUrl, PHP_URL_SCHEME);
        $host            = parse_url($resolvedUrl, PHP_URL_HOST);
        $port            = parse_url($resolvedUrl, PHP_URL_PORT);

        if ($scheme !== 'https' || ! is_string($host) || trim($host) === '') {
            throw new InvalidArgumentException('Источник категорий должен использовать абсолютный HTTPS URL.');
        }

        if (
            parse_url($resolvedUrl, PHP_URL_USER) !== null
            || parse_url($resolvedUrl, PHP_URL_PASS) !== null
            || parse_url($resolvedUrl, PHP_URL_QUERY) !== null
            || parse_url($resolvedUrl, PHP_URL_FRAGMENT) !== null
            || ($port !== null && $port !== 443)
        ) {
            throw new InvalidArgumentException('URL источника не может содержать credentials, query, fragment или нестандартный порт.');
        }

        $allowedHosts    = $definition['allowed_hosts'] ?? [];

        if (! is_array($allowedHosts)) {
            $allowedHosts = [];
        }

        $normalizedHosts = array_values(array_filter(array_map(
            static fn(mixed $allowedHost): string => is_string($allowedHost)
                ? strtolower(trim($allowedHost))
                : '',
            $allowedHosts,
        )));

        if (! in_array(strtolower($host), $normalizedHosts, true)) {
            throw new InvalidArgumentException(sprintf('Host [%s] не входит в allowlist источника [%s].', $host, $source));
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            throw new InvalidArgumentException('IP-адрес нельзя использовать как host источника категорий.');
        }

        return $resolvedUrl;
    }

    public function authorizeFixturePath(string $path): string
    {
        $realPath       = realpath($path);

        if ($realPath === false || ! is_file($realPath)) {
            throw new InvalidArgumentException('Fixture категорий не найдена.');
        }

        $normalizedPath = strtolower($realPath);

        if (! str_ends_with($normalizedPath, '.html') && ! str_ends_with($normalizedPath, '.htm')) {
            throw new InvalidArgumentException('Fixture категорий должна быть HTML-файлом.');
        }

        $fixtureRoots   = config('catalog-import.fixture_roots', []);

        if (! is_array($fixtureRoots)) {
            $fixtureRoots = [];
        }

        foreach ($fixtureRoots as $fixtureRoot) {
            if (! is_string($fixtureRoot)) {
                continue;
            }

            $realRoot = realpath($fixtureRoot);

            if ($realRoot !== false && str_starts_with($realPath, $realRoot . DIRECTORY_SEPARATOR)) {
                return $realPath;
            }
        }

        throw new InvalidArgumentException('Fixture находится вне разрешенных catalog import директорий.');
    }

    public function maxResponseBytes(): int
    {
        $configured = config('catalog-import.max_response_bytes', 5 * 1024 * 1024);

        return is_int($configured) && $configured > 0
            ? $configured
            : 5 * 1024 * 1024;
    }

    public function userAgent(): string
    {
        $configured = config('catalog-import.user_agent', 'SnabixCatalogImporter/1.0');

        return is_string($configured) && trim($configured) !== ''
            ? trim($configured)
            : 'SnabixCatalogImporter/1.0';
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(string $source): array
    {
        $sources    = config('catalog-import.sources', []);

        if (! is_array($sources) || ! array_key_exists($source, $sources) || ! is_array($sources[$source])) {
            throw new InvalidArgumentException(sprintf('Источник категорий [%s] не зарегистрирован.', $source));
        }

        $definition = [];

        foreach ($sources[$source] as $key => $value) {
            if (is_string($key)) {
                $definition[$key] = $value;
            }
        }

        return $definition;
    }
}
