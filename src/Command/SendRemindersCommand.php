<?php

namespace App\Command;

use App\Manager\InterventionManager;
use App\Manager\SignalementManager;
use App\Repository\InterventionRepository;
use App\Repository\SignalementRepository;
use App\Service\Mailer\MailerProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-reminders',
    description: 'Send reminders'
)]
class SendRemindersCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private SignalementRepository $signalementRepository,
        private SignalementManager $signalementManager,
        private InterventionRepository $interventionRepository,
        private InterventionManager $interventionManager,
        private MailerProvider $mailerProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $signalementsToNotify = $this->signalementRepository->findToNotify();
        $countSignalementsToNotify = \count($signalementsToNotify);
        foreach ($signalementsToNotify as $signalement) {
            $this->io->success(sprintf('%s to notify',
                $signalement->getUuid()
            ));
            $signalement->setReminderAutotraitementAt(new \DateTimeImmutable());
            $this->signalementManager->save($signalement);
            $this->mailerProvider->sendSignalementSuiviTraitementAuto($signalement);
        }

        $interventionsToNotify = $this->interventionRepository->findToNotify();
        $countInterventionsToNotify = \count($interventionsToNotify);
        foreach ($interventionsToNotify as $intervention) {
            $this->io->success(sprintf('%s to notify',
                $intervention->getId()
            ));
            $intervention->setReminderResolvedByEntrepriseAt(new \DateTimeImmutable());
            $this->interventionManager->save($intervention);
            $this->mailerProvider->sendSignalementSuiviTraitementPro($intervention);
        }

        $this->io->success(sprintf('%s signalements were notified, %s interventions were notified',
            $countSignalementsToNotify,
            $countInterventionsToNotify,
        ));

        return Command::SUCCESS;
    }
}
