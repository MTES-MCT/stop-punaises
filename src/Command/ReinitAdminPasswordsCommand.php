<?php

namespace App\Command;

use App\Entity\Enum\Status;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reinit-admin-passwords',
    description: 'Reinitialize admin passwords'
)]
class ReinitAdminPasswordsCommand extends Command
{
    private SymfonyStyle $io;

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public function __construct(
        private UserManager $userManager,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->findActiveAdmins();

        foreach ($users as $user) {
            $user->setPassword('')->setStatus(Status::INACTIVE);
            $this->userManager->save($user);

            $this->userManager->requestActivationFrom($user->getEmail());
        }

        $this->io->success(\sprintf(
            '%s admin users were successfully reinitialized',
            \count($users)
        ));

        return Command::SUCCESS;
    }
}
