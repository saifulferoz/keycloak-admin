<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Command\User;

use Feroz\KeycloakAdmin\Query\UserQuery;
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
    name: 'keycloak:user:list',
    description: 'List users in a Keycloak realm'
)]
final class UserListCommand extends Command
{
    public function __construct(
        private readonly KeycloakClientRegistry $registry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('realm', InputArgument::OPTIONAL, 'Target realm name (defaults to client configured realm)')
            ->addOption('client', 'c', InputOption::VALUE_REQUIRED, 'Keycloak client configuration name', $this->registry->getDefaultClientName())
            ->addOption('search', 's', InputOption::VALUE_REQUIRED, 'Search string for username, first/last name or email')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max users to display', 20)
            ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, 'Pagination offset', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $clientName */
        $clientName = $input->getOption('client');
        /** @var string|null $realm */
        $realm = $input->getArgument('realm');
        /** @var string|null $search */
        $search = $input->getOption('search');
        $limit = (int) $input->getOption('limit');
        $offset = (int) $input->getOption('offset');

        try {
            $client = $this->registry->getClient($clientName);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $usersResource = $client->users($realm);

        $query = UserQuery::create()
            ->withSearch($search)
            ->withFirst($offset)
            ->withMax($limit);

        try {
            $results = $usersResource->list($query);
            $total = $usersResource->count($query);
        } catch (Throwable $e) {
            $io->error(sprintf('Failed to retrieve users: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        $io->title(sprintf('Users in Realm (Showing %d / Total %d)', $results->count(), $total));

        if ($results->count() === 0) {
            $io->warning('No users found matching query criteria.');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($results as $user) {
            $rows[] = [
                $user->id,
                $user->username,
                $user->email,
                trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
                $user->enabled ? '✓ Enabled' : '✗ Disabled',
                $user->emailVerified ? '✓ Verified' : '✗ Unverified',
            ];
        }

        $io->table(['ID', 'Username', 'Email', 'Full Name', 'Status', 'Email Verified'], $rows);

        return Command::SUCCESS;
    }
}
