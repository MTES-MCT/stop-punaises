<?php

namespace App\Command;

use App\Entity\Enum\Role;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Manager\UserManager;
use App\Service\Mailer\MailerProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:add-user',
    description: 'Create a user'
)]
class AddUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private UserFactory $userFactory,
        private UserManager $userManager,
        private ValidatorInterface $validator,
        private MailerProviderInterface $mailer,
        private TokenGeneratorInterface $tokenGenerator,
        private ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email required to login');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        if (empty($email)) {
            $this->io->error('Email is missing');

            return Command::FAILURE;
        }

        /** @var User $user */
        $user = $this->userManager->findOneBy(['email' => $email]);
        if (null !== $user) {
            $this->io->error('Email exists, please choose an another email');
        }

        $user = $this->userFactory->createInstanceFrom(Role::ROLE_ADMIN, $email);
        $user
            ->setToken($this->tokenGenerator->generateToken())
            ->setTokenExpiredAt(
                (new \DateTimeImmutable())->modify($this->parameterBag->get('token_lifetime'))
            );

        $errors = $this->validator->validate($user);
        if (\count($errors) > 0) {
            $this->io->error((string) $errors);

            return Command::FAILURE;
        }

        $this->userManager->save($user);
        $this->mailer->sendActivateMessage($user);

        $this->io->success(sprintf('%s was successfully created',
            $user->getEmail()
        ));

        return Command::SUCCESS;
    }
}
