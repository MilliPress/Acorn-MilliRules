<?php

namespace MilliPress\AcornMilliRules\Console\Commands\Concerns;

use Composer\Autoload\ClassLoader;
use Illuminate\Support\Str;
use MilliRules\Actions\ActionInterface;
use MilliRules\Conditions\ConditionInterface;
use MilliRules\Context;
use MilliRules\Packages\PackageManager;
use MilliRules\Rules;

trait ScansRegisteredTypes
{
    /**
     * Discover all registered types (actions or conditions) grouped by package.
     *
     * Scans loaded packages and the app-level namespace for class-based types,
     * plus any custom callback-based types registered via Rules::register_action()
     * or Rules::register_condition().
     *
     * @param  string $kind 'Actions' or 'Conditions'
     * @return array<string, array<int, array{type: string, builder: string, class: string}>>
     */
    private function discoverTypes(string $kind): array
    {
        $loader = $this->getComposerClassLoader();

        if (! $loader) {
            return [];
        }

        $results = [];

        // 1. Scan loaded packages for action/condition namespaces.
        foreach (PackageManager::get_loaded_packages() as $package) {
            $packageName = $package->get_name();

            foreach ($package->get_namespaces() as $namespace) {
                if (! str_contains($namespace, '\\'.$kind)) {
                    continue;
                }

                $this->scanAndCollect($namespace, $packageName, $kind, $loader, $results);
            }
        }

        // 2. Scan app-level namespace (App\Rules\Actions or App\Rules\Conditions).
        $appNamespace = 'App\\Rules\\'.$kind;
        $this->scanAndCollect($appNamespace, 'App', $kind, $loader, $results);

        // 3. Custom callback-based types (requires MilliRules 0.8.0+).
        $this->collectCustomCallbacks($kind, $results);

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
     * Scan a namespace and add discovered types to results.
     */
    private function scanAndCollect(
        string $namespace,
        string $packageName,
        string $kind,
        ClassLoader $loader,
        array &$results
    ): void {
        foreach ($this->scanNamespaceForClasses($namespace, $loader) as $className) {
            $typeInfo = $this->extractTypeInfo($className, $kind);

            if ($typeInfo) {
                $results[$packageName][] = $typeInfo;
            }
        }
    }

    /**
     * Collect custom callback-based types if the API is available.
     */
    private function collectCustomCallbacks(string $kind, array &$results): void
    {
        $method = $kind === 'Actions' ? 'get_custom_actions' : 'get_custom_conditions';

        if (! method_exists(Rules::class, $method)) {
            return;
        }

        foreach (Rules::$method() as $type => $callback) {
            $results['Callback'][] = [
                'type' => $type,
                'builder' => '->'.Str::camel($type).'(...)',
                'class' => '(callback)',
            ];
        }
    }

    /**
     * Instantiate a class and extract its type info.
     */
    private function extractTypeInfo(string $className, string $kind): ?array
    {
        try {
            $reflection = new \ReflectionClass($className);

            $interface = $kind === 'Actions'
                ? ActionInterface::class
                : ConditionInterface::class;

            if (! $reflection->implementsInterface($interface) || $reflection->isAbstract()) {
                return null;
            }

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

    private function getComposerClassLoader(): ?ClassLoader
    {
        foreach (spl_autoload_functions() as $loader) {
            if (is_array($loader) && $loader[0] instanceof ClassLoader) {
                return $loader[0];
            }
        }

        return null;
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
