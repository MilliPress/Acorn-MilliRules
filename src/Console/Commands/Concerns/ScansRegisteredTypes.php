<?php

namespace MilliPress\AcornMilliRules\Console\Commands\Concerns;

use Composer\Autoload\ClassLoader;
use Illuminate\Support\Str;
use MilliRules\Actions\ActionInterface;
use MilliRules\Conditions\ConditionInterface;
use MilliRules\Context;
use MilliRules\Packages\PackageManager;
use MilliRules\RuleEngine;
use MilliRules\Rules;

trait ScansRegisteredTypes
{
    /**
     * Discover all registered types (actions or conditions) grouped by package.
     *
     * Uses RuleEngine::get_registered_namespaces() to enumerate all namespaces
     * (from packages and app-level), then scans each for class-based types.
     * Also lists custom callback-based types registered via Rules::register_action()
     * or Rules::register_condition().
     *
     * @param  string $kind 'Actions' or 'Conditions'
     * @return array<string, array<int, array{type: string, builder: string, class: string}>>
     */
    private function discoverTypes(string $kind): array
    {
        $loader = $this->getComposerClassLoader();

        /** @var list<string> $namespaces */
        $namespaces = RuleEngine::get_registered_namespaces($kind);
        $packageMap = $this->buildPackageMap();
        $results = [];

        foreach ($namespaces as $namespace) {
            $package = $this->resolvePackageName($namespace, $packageMap);

            foreach ($this->scanNamespaceForClasses($namespace, $loader) as $className) {
                $typeInfo = $this->extractTypeInfo($className, $kind);

                if ($typeInfo) {
                    $results[$package][] = $typeInfo;
                }
            }
        }

        // Custom callback-based types.
        $customs = $kind === 'Actions'
            ? Rules::get_custom_actions()
            : Rules::get_custom_conditions();

        foreach ($customs as $type => $callback) {
            $results['Callback'][] = [
                'type' => $type,
                'builder' => '->'.Str::camel($type).'(...)',
                'class' => '(callback)',
            ];
        }

        // Sort packages: Core first, then alphabetically, App/Callback last.
        uksort($results, function ($a, $b) {
            $order = ['Core' => 0, 'PHP' => 1, 'WP' => 2];
            $aOrder = $order[$a] ?? (in_array($a, ['App', 'Callback']) ? 99 : 50);
            $bOrder = $order[$b] ?? (in_array($b, ['App', 'Callback']) ? 99 : 50);

            return $aOrder <=> $bOrder ?: strcmp($a, $b);
        });

        return $results;
    }

    /**
     * Build namespace → package map from registered packages.
     *
     * @return array<string, string>
     */
    private function buildPackageMap(): array
    {
        $map = [];

        foreach (PackageManager::get_all_packages() as $package) {
            foreach ($package->get_namespaces() as $ns) {
                $map[$ns] = $package->get_name();
            }
        }

        return $map;
    }

    /**
     * Resolve a namespace to its package name.
     *
     * @param  array<string, string>  $packageMap
     */
    private function resolvePackageName(string $namespace, array $packageMap): string
    {
        if (isset($packageMap[$namespace])) {
            return $packageMap[$namespace];
        }

        if (str_starts_with($namespace, 'App\\')) {
            return 'App';
        }

        if (str_starts_with($namespace, 'MilliRules\\')) {
            return 'Core';
        }

        return 'Custom';
    }

    /**
     * Instantiate a class and extract its type info.
     *
     * @return array{type: string, builder: string, class: string}|null
     */
    private function extractTypeInfo(string $className, string $kind): ?array
    {
        try {
            $reflection = new \ReflectionClass($className); // @phpstan-ignore argument.type

            $interface = $kind === 'Actions'
                ? ActionInterface::class
                : ConditionInterface::class;

            if (! $reflection->implementsInterface($interface) || $reflection->isAbstract()) {
                return null;
            }

            /** @var ActionInterface|ConditionInterface $instance */
            $instance = new $className([], new Context());
            $type = $instance->get_type();

            return [
                'type' => $type,
                'builder' => '->'.Str::camel($type).'(...)',
                'class' => $className,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get the project's Composer ClassLoader.
     *
     * WordPress sites may have multiple ClassLoaders (e.g. plugins with bundled
     * vendors). Requiring the project's autoload.php returns the correct one
     * via Composer's idempotent getLoader() singleton.
     */
    private function getComposerClassLoader(): ClassLoader
    {
        return require $this->laravel->basePath('vendor/autoload.php');
    }

    /**
     * Find the directory for a namespace using Composer's PSR-4 prefixes.
     */
    private function findNamespaceDirectory(ClassLoader $loader, string $namespace): ?string
    {
        $psr4 = $loader->getPrefixesPsr4();
        $ns = rtrim($namespace, '\\').'\\';

        // Direct match.
        if (isset($psr4[$ns])) {
            foreach ($psr4[$ns] as $dir) {
                if (is_dir($dir)) {
                    return $dir;
                }
            }
        }

        // Find parent prefix and derive subdirectory.
        foreach ($psr4 as $prefix => $dirs) {
            if (str_starts_with($ns, $prefix)) {
                $subPath = str_replace('\\', '/', substr($ns, strlen($prefix)));

                foreach ($dirs as $dir) {
                    $fullPath = rtrim($dir, '/').'/'.rtrim($subPath, '/');

                    if (is_dir($fullPath)) {
                        return $fullPath;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Scan a namespace's directory for PHP classes.
     *
     * @return list<string>
     */
    private function scanNamespaceForClasses(string $namespace, ClassLoader $loader): array
    {
        $dir = $this->findNamespaceDirectory($loader, $namespace);

        if ($dir === null) {
            return [];
        }

        $files = glob($dir.'/*.php');

        if (empty($files)) {
            return [];
        }

        $ns = rtrim($namespace, '\\');
        $classes = [];

        foreach ($files as $file) {
            $className = $ns.'\\'.pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($className)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }
}
