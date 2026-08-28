<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Command\User;

use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
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
    name: 'keycloak:user:create',
    description: 'Create a new user in Keycloak'
)]
final class UserCreateCommand extends Command
{
    public function __construct(
        private readonly KeycloakClientRegistry $registry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username of the new user')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email of the new user')
            ->addOption('first-name', null, InputOption::VALUE_REQUIRED, 'First name')
            ->addOption('last-name', null, InputOption::VALUE_REQUIRED, 'Last name')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Initial password')
            ->addOption('temporary-password', null, InputOption::VALUE_NONE, 'Require password change on first login')
            ->addOption('email-verified', null, InputOption::VALUE_NONE, 'Mark email as verified')
            ->addOption('realm', 'r', InputOption::VALUE_REQUIRED, 'Target realm name')
            ->addOption('client', 'c', InputOption::VALUE_REQUIRED, 'Keycloak client configuration name', $this->registry->getDefaultClientName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $clientName */
        $clientName = $input->getOption('client');
        /** @var string|null $realm */
        $realm = $input->getOption('realm');

        try {
            $client = $this->registry->getClient($clientName);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $username = $input->getArgument('username');
        if ($username === null) {
            $username = $io->ask('Username');
        }

        $email = $input->getArgument('email');
        if ($email === null) {
            $email = $io->ask('Email (optional)', null);
        }

        $firstName = $input->getOption('first-name') ?? $io->ask('First Name (optional)', null);
        $lastName = $input->getOption('last-name') ?? $io->ask('Last Name (optional)', null);
        $password = $input->getOption('password');

        if ($password === null && $io->confirm('Set password for this user now?', false)) {
            $password = $io->askHidden('Password');
        }

        $emailVerified = (bool) $input->getOption('email-verified');
        $tempPassword = (bool) $input->getOption('temporary-password');

        $user = UserRepresentation::create($email, (string) $username)
            ->withEmailVerified($emailVerified);

        if ($firstName !== null && $lastName !== null) {
            $user = $user->withName((string) $firstName, (string) $lastName);
        }

        if ($password !== null && $password !== '') {
            $user = $user->withPassword((string) $password, $tempPassword);
        }

        $usersResource = $client->users($realm);

        try {
            $createdId = $usersResource->create($user);
            $io->success(sprintf('User "%s" successfully created with ID: %s', $username, $createdId));
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->error(sprintf('Failed to create user: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
