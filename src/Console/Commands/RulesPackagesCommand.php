<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use MilliRules\Packages\PackageManager;

class RulesPackagesCommand extends Command
{
    protected $signature = 'rules:packages';

    protected $description = 'List all registered MilliRules packages';

    public function handle(): int
    {
        $rows = [];

        foreach (PackageManager::get_all_packages() as $package) {
            $name = $package->get_name();
            $isLoaded = PackageManager::is_package_loaded($name);
            $isAvailable = $package->is_available();
            $dependencies = $package->get_required_packages();

            $ruleCount = 0;

            if ($isLoaded) {
                $rules = $package->get_rules();

                // Handle WP's grouped structure.
                if ($name === 'WP') {
                    foreach ($rules as $hookRules) {
                        if (is_array($hookRules)) {
                            $ruleCount += count($hookRules);
                        }
                    }
                } else {
                    $ruleCount = count($rules);
                }
            }

            $rows[] = [
                $name,
                $isAvailable ? '<fg=green>Yes</>' : '<fg=red>No</>',
                $isLoaded ? '<fg=green>Yes</>' : '<fg=gray>No</>',
                empty($dependencies) ? '-' : implode(', ', $dependencies),
                $isLoaded ? $ruleCount : '-',
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
