<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use MilliRules\Packages\PackageManager;

class RulesShowCommand extends Command
{
    protected $signature = 'rules:show {id : The rule ID to display}';

    protected $description = 'Show detailed information about a specific rule';

    public function handle(): int
    {
        $ruleId = $this->argument('id');
        $found = null;
        $foundPackage = null;

        foreach (PackageManager::get_loaded_packages() as $package) {
            $packageName = $package->get_name();
            $rules = $this->flattenRules($package->get_rules(), $packageName);

            foreach ($rules as $rule) {
                if (($rule['id'] ?? null) === $ruleId) {
                    $found = $rule;
                    $foundPackage = $packageName;
                    break 2;
                }
            }
        }

        if (! $found) {
            $this->components->error("Rule '{$ruleId}' not found.");

            return self::FAILURE;
        }

        $metadata = $found['_metadata'] ?? [];

        $this->components->twoColumnDetail('Rule ID', $found['id'] ?? 'unnamed');
        $this->components->twoColumnDetail('Package', $foundPackage);
        $this->components->twoColumnDetail('Order', (string) ($metadata['order'] ?? 10));
        $this->components->twoColumnDetail('Enabled', isset($found['enabled']) && $found['enabled'] === false ? 'No' : 'Yes');
        $this->components->twoColumnDetail('Match Type', $found['match_type'] ?? 'all');

        if (! empty($metadata['hook'])) {
            $this->components->twoColumnDetail('WP Hook', $metadata['hook']);
            $this->components->twoColumnDetail('Hook Priority', (string) ($metadata['priority'] ?? 10));
        }

        // Conditions.
        $conditions = $found['conditions'] ?? [];
        $this->newLine();
        $this->components->info('Conditions ('.count($conditions).')');

        if (empty($conditions)) {
            $this->line('  <fg=gray>No conditions (always matches)</>');
        } else {
            foreach ($conditions as $i => $condition) {
                $type = $condition['type'] ?? 'unknown';
                $operator = $condition['operator'] ?? '=';
                $value = $this->formatValue($condition['value'] ?? '');
                $name = isset($condition['name']) ? " [{$condition['name']}]" : '';

                $this->line("  <fg=blue>{$type}</>{$name} <fg=yellow>{$operator}</> {$value}");
            }
        }

        // Actions.
        $actions = $found['actions'] ?? [];
        $this->newLine();
        $this->components->info('Actions ('.count($actions).')');

        if (empty($actions)) {
            $this->line('  <fg=gray>No actions</>');
        } else {
            foreach ($actions as $action) {
                $type = $action['type'] ?? 'unknown';
                $args = $action;
                unset($args['type']);
                $argsStr = empty($args) ? '' : ' '.json_encode($args, JSON_UNESCAPED_SLASHES);

                $this->line("  <fg=green>{$type}</>{$argsStr}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Format a condition value for display.
     */
    private function formatValue(mixed $value): string
    {
        if (is_array($value)) {
            return '['.implode(', ', array_map('strval', $value)).']';
        }

        return (string) $value;
    }

    /**
     * Flatten rules from a package, handling WP's grouped structure.
     *
     * @param array<mixed> $rules
     * @return array<int, array<string, mixed>>
     */
    private function flattenRules(array $rules, string $packageName): array
    {
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
