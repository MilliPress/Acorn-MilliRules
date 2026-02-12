<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rules:conditions')]
class ListConditionsCommand extends Command
{
    use Concerns\ScansRegisteredTypes;

    protected $signature = 'rules:conditions
                            {--package= : Filter by package name}';

    protected $description = 'List all registered condition types across loaded packages';

    public function handle(): int
    {
        /** @var string $packageFilter */
        $packageFilter = $this->option('package');
        $groups = $this->discoverTypes('Conditions');

        $rows = [];

        foreach ($groups as $package => $types) {
            if ($packageFilter && $package !== $packageFilter) {
                continue;
            }

            foreach ($types as $type) {
                $rows[] = [
                    $type['type'],
                    "<fg=blue>{$type['builder']}</>",
                    $package,
                    "<fg=gray>{$type['class']}</>",
                ];
            }
        }

        if (empty($rows)) {
            $this->components->info('No conditions found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Type', 'Builder', 'Package', 'Class'],
            $rows
        );

        return self::SUCCESS;
    }
}
