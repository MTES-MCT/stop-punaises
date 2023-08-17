<?php

namespace App\Command;

use App\Event\InterventionRemindedEvent;
use App\Event\SignalementRemindedEvent;
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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        private EventDispatcherInterface $eventDispatcher,
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
            $this->io->success(sprintf('Signalement id %s to notify',
                $signalement->getUuid()
            ));
            $signalement->setReminderAutotraitementAt(new \DateTimeImmutable());
            $this->signalementManager->save($signalement);
            $this->mailerProvider->sendSignalementSuiviTraitementAuto($signalement);

            $this->eventDispatcher->dispatch(
                new SignalementRemindedEvent(
                    $signalement
                ),
                SignalementRemindedEvent::NAME
            );
        }

        $interventionsToNotifyUsager = $this->interventionRepository->findToNotifyUsager();
        $countInterventionsToNotifyUsager = \count($interventionsToNotifyUsager);
        foreach ($interventionsToNotifyUsager as $intervention) {
            $this->io->success(sprintf('Intervention id %s to notify for usager',
                $intervention->getId()
            ));
            $intervention->setReminderResolvedByEntrepriseAt(new \DateTimeImmutable());
            $this->interventionManager->save($intervention);
            $this->mailerProvider->sendSignalementSuiviTraitementPro($intervention);

            $this->eventDispatcher->dispatch(
                new InterventionRemindedEvent(
                    $intervention
                ),
                InterventionRemindedEvent::NAME
            );
        }

        $interventionsToNotifyPro = $this->interventionRepository->findToNotifyPro();
        $countInterventionsToNotifyPro = \count($interventionsToNotifyPro);
        foreach ($interventionsToNotifyPro as $intervention) {
            $this->io->success(sprintf('Intervention id %s to notify for pro',
                $intervention->getId()
            ));
            $intervention->setReminderPendingEntrepriseConclusionAt(new \DateTimeImmutable());
            $this->interventionManager->save($intervention);
            $this->mailerProvider->sendSignalementSuiviTraitementProForPro($intervention);
        }

        $this->io->success(sprintf('%s signalements were notified, %s interventions were notified for usager, %s interventions were notified for pro',
            $countSignalementsToNotify,
            $countInterventionsToNotifyUsager,
            $countInterventionsToNotifyPro
        ));

        return Command::SUCCESS;
    }
}
