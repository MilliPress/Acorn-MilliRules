<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use MilliRules\Packages\PackageManager;

class RulesListCommand extends Command
{
    protected $signature = 'rules:list
                            {--package= : Filter by package name}
                            {--id= : Filter by rule ID pattern}';

    protected $description = 'List all registered rules across loaded packages';

    public function handle(): int
    {
        $packageFilter = $this->option('package');
        $idFilter = $this->option('id');

        $rows = [];

        foreach (PackageManager::get_loaded_packages() as $package) {
            $packageName = $package->get_name();

            if ($packageFilter && $packageName !== $packageFilter) {
                continue;
            }

            $rules = $this->flattenRules($package->get_rules(), $packageName);

            foreach ($rules as $rule) {
                $ruleId = $rule['id'] ?? 'unnamed';

                if ($idFilter && ! str_contains($ruleId, $idFilter)) {
                    continue;
                }

                $metadata = $rule['_metadata'] ?? [];

                $rows[] = [
                    $ruleId,
                    $packageName,
                    $metadata['order'] ?? 10,
                    isset($rule['enabled']) && $rule['enabled'] === false ? 'No' : 'Yes',
                    $rule['match_type'] ?? 'all',
                    count($rule['conditions'] ?? []),
                    count($rule['actions'] ?? []),
                ];
            }
        }

        if (empty($rows)) {
            $this->components->info('No rules found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Package', 'Order', 'Enabled', 'Match', 'Conditions', 'Actions'],
            $rows
        );

        return self::SUCCESS;
    }

    /**
     * Flatten rules from a package, handling WP's grouped structure.
     *
     * @param array<mixed> $rules
     * @return array<int, array<string, mixed>>
     */
    private function flattenRules(array $rules, string $packageName): array
    {
        // WP package returns rules grouped by hook: ['hook_name' => [rule1, rule2, ...]]
        if ($packageName === 'WP') {
            $flat = [];

            foreach ($rules as $hookOrIndex => $value) {
                if (is_string($hookOrIndex) && is_array($value)) {
                    foreach ($value as $rule) {
                        if (is_array($rule) && isset($rule['id'])) {
                            $flat[] = $rule;
                        }
                    }
                } elseif (is_array($value) && isset($value['id'])) {
                    $flat[] = $value;
                }
            }

            return $flat;
        }

        return $rules;
    }
}
