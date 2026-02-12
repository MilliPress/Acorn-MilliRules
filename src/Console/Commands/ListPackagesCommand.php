<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use MilliRules\Packages\PackageManager;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rules:packages')]
class ListPackagesCommand extends Command
{
    protected $signature = 'rules:packages';

    protected $description = 'List all registered MilliRules packages';

    public function handle(): int
    {
        // Pre-compute rule counts per package using the flattened rules API.
        $ruleCounts = [];

        foreach (PackageManager::get_all_rules() as $rule) {
            $pkg = $rule['_package'] ?? 'unknown';
            $ruleCounts[$pkg] = ($ruleCounts[$pkg] ?? 0) + 1;
        }

        $rows = [];

        foreach (PackageManager::get_all_packages() as $package) {
            $name = $package->get_name();
            $isLoaded = PackageManager::is_package_loaded($name);
            $isAvailable = $package->is_available();
            $dependencies = $package->get_required_packages();

            $rows[] = [
                $name,
                $isAvailable ? '<fg=green>Yes</>' : '<fg=red>No</>',
                $isLoaded ? '<fg=green>Yes</>' : '<fg=gray>No</>',
                empty($dependencies) ? '-' : implode(', ', $dependencies),
                $isLoaded ? ($ruleCounts[$name] ?? 0) : '-',
            ];
        }

        if (empty($rows)) {
            $this->components->warn('No packages registered.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Available', 'Loaded', 'Dependencies', 'Rules'],
            $rows
        );

        return self::SUCCESS;
    }
}
