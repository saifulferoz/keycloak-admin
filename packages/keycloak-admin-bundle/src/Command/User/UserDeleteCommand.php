<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Command\User;

use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'keycloak:user:delete',
    description: 'Delete a user in Keycloak by UUID'
)]
final class UserDeleteCommand extends Command
{
    public function __construct(
        private readonly KeycloakClientRegistry $registry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user-id', InputArgument::REQUIRED, 'User UUID to delete')
            ->addOption('realm', 'r', InputOption::VALUE_REQUIRED, 'Target realm name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Bypass interactive confirmation')
            ->addOption('client', 'c', InputOption::VALUE_REQUIRED, 'Keycloak client configuration name', $this->registry->getDefaultClientName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $userId */
        $userId = $input->getArgument('user-id');
        /** @var string $clientName */
        $clientName = $input->getOption('client');
        /** @var string|null $realm */
        $realm = $input->getOption('realm');
        $force = (bool) $input->getOption('force');

        try {
            $client = $this->registry->getClient($clientName);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $usersResource = $client->users($realm);

        if (!$force && !$io->confirm(sprintf('Are you sure you want to delete user "%s"? This cannot be undone.', $userId), false)) {
            $io->comment('Deletion cancelled.');
            return Command::SUCCESS;
        }

        try {
            $usersResource->delete($userId);
            $io->success(sprintf('User "%s" was successfully deleted.', $userId));
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->error(sprintf('Failed to delete user: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
