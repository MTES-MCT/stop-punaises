<?php

namespace App\Command;

use App\Entity\Event;
use App\Manager\EventManager;
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
        private EventManager $eventManager,
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

            $this->eventManager->createEventReminderAutotraitement(
                signalement: $signalement,
                description: 'Votre problème de punaises est-il résolu ?',
                recipient: $signalement->getEmailOccupant(),
                userId: null,
                label: 'Nouveau',
                actionLabel: 'En savoir plus',
                modalToOpen: 'probleme-resolu',
            );
            $this->eventManager->createEventReminderAutotraitement(
                signalement: $signalement,
                description: 'L\'email de suivi post-traitement a été envoyé à l\'usager',
                recipient: null,
                userId: Event::USER_ADMIN,
                label: null,
                actionLabel: null,
                modalToOpen: null,
            );
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

            $this->eventManager->createEventReminderPro(
                signalement: $intervention->getSignalement(),
                description: 'Votre problème de punaises est-il résolu ?',
                recipient: $intervention->getSignalement()->getEmailOccupant(),
                userId: null,
                label: 'Nouveau',
                actionLabel: 'En savoir plus',
                modalToOpen: 'probleme-resolu-pro',
            );
            $this->eventManager->createEventReminderPro(
                signalement: $intervention->getSignalement(),
                description: 'L\'email de suivi post-traitement a été envoyé à l\'usager',
                recipient: null,
                userId: Event::USER_ADMIN,
                label: null,
                actionLabel: null,
                modalToOpen: null,
            );
        }

        $this->io->success(sprintf('%s signalements were notified, %s interventions were notified',
            $countSignalementsToNotify,
            $countInterventionsToNotify,
        ));

        return Command::SUCCESS;
    }
}
