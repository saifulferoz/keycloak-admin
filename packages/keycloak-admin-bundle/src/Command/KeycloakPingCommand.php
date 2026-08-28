<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Command;

use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'keycloak:ping',
    description: 'Test connectivity and credentials for Keycloak Admin API'
)]
final class KeycloakPingCommand extends Command
{
    public function __construct(
        private readonly KeycloakClientRegistry $registry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('client', InputArgument::OPTIONAL, 'The name of the Keycloak client to test', $this->registry->getDefaultClientName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $clientName */
        $clientName = $input->getArgument('client');

        $io->title(sprintf('Testing Keycloak Client "%s"', $clientName));

        try {
            $client = $this->registry->getClient($clientName);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $config = $client->getConfiguration();
        $io->horizontalTable(
            ['Base URL', 'Auth Realm', 'Target Realm', 'Grant Type'],
            [[
                $config->getBaseUrl(),
                $config->getAuthRealm(),
                $config->getDefaultTargetRealm(),
                (new \ReflectionClass($config->getCredentials()))->getShortName(),
            ]]
        );

        $io->section('Connecting and authenticating...');
        $start = microtime(true);

        try {
            $token = $client->getTokenManager()->getAccessToken();
            $duration = round((microtime(true) - $start) * 1000, 2);

            $io->success(sprintf('Successfully authenticated with Keycloak in %s ms!', $duration));
            $io->comment('Bearer Token acquired successfully (length: ' . strlen($token) . ' chars).');

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->error(sprintf('Authentication failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
