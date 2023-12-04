<?php

namespace App\Command;

use App\Entity\Enum\Status;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use App\Service\Token\GeneratorToken;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $hasher,
        private UserRepository $userRepository,
        private GeneratorToken $tokenGenerator
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
            $password = $this->hasher->hashPassword($user, $this->tokenGenerator->generateToken());
            $user->setPassword($password)->setStatus(Status::INACTIVE);

            /** @var ConstraintViolationList $errors */
            $errors = $this->validator->validate($user);

            if (\count($errors) > 0) {
                $this->io->error((string) $errors);

                return Command::FAILURE;
            }

            $this->userManager->save($user);

            $this->userManager->requestActivationFrom($user->getEmail());
        }

        $this->io->success(sprintf(
            '%s admin users were successfully reinitialized',
            \count($users)
        ));

        return Command::SUCCESS;
    }
}
