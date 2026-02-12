<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rules:actions')]
class ListActionsCommand extends Command
{
    use Concerns\ScansRegisteredTypes;

    protected $signature = 'rules:actions
                            {--package= : Filter by package name}';

    protected $description = 'List all registered action types across loaded packages';

    public function handle(): int
    {
        /** @var string $packageFilter */
        $packageFilter = $this->option('package');
        $groups = $this->discoverTypes('Actions');

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
            $this->components->info('No actions found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Type', 'Builder', 'Package', 'Class'],
            $rows
        );

        return self::SUCCESS;
    }
}
