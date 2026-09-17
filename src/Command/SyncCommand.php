<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Command;

use Agusedyc\Admin\Sync\RouteSynchronizer;

use function count;
use function implode;
use function sprintf;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    name: 'admin:sync',
    description: 'Synchronize application routes with RBAC permissions and route metadata',
)]
final class SyncCommand extends Command
{
    public function __construct(private readonly RouteSynchronizer $synchronizer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Synchronizing routes with RBAC');

        $result = $this->synchronizer->sync();

        if ($result->isEmpty()) {
            $io->success('Nothing to synchronize.');

            return ExitCode::OK;
        }

        $io->table(
            ['Metric', 'Count'],
            [
                ['New permissions', count($result->getAddedPermissions())],
                ['New / re-enabled routes', count($result->getAddedRoutes())],
                ['New group permissions', count($result->getAddedGroups())],
                ['Disabled routes', count($result->getDisabled())],
                ['Removed permissions', count($result->getRemovedPermissions())],
            ],
        );

        if ($result->getAddedPermissions() !== []) {
            $io->writeln('<info>Added permissions:</info> ' . implode(', ', $result->getAddedPermissions()));
        }

        $io->success(sprintf(
            'Synchronization finished: %d permissions, %d routes, %d disabled.',
            count($result->getAddedPermissions()),
            count($result->getAddedRoutes()),
            count($result->getDisabled()),
        ));

        return ExitCode::OK;
    }
}
