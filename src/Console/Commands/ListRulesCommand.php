<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use MilliRules\Packages\PackageManager;

class ListRulesCommand extends Command
{
    protected $signature = 'rules:list
                            {--package= : Filter by package name}
                            {--id= : Filter by rule ID pattern}';

    protected $description = 'List all registered rules across loaded packages';

    public function handle(): int
    {
        /** @var string $packageFilter */
        $packageFilter = $this->option('package');
        /** @var string $idFilter */
        $idFilter = $this->option('id');

        $rows = [];

        foreach (PackageManager::get_all_rules() as $rule) {
            $packageName = $rule['_package'] ?? 'unknown';

            if ($packageFilter && $packageName !== $packageFilter) {
                continue;
            }

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
}
