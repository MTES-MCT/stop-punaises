<?php

namespace App\Command;

use App\Entity\Signalement;
use App\Event\InterventionRemindedEvent;
use App\Event\SignalementClosedEvent;
use App\Event\SignalementRemindedEvent;
use App\Manager\InterventionManager;
use App\Manager\SignalementManager;
use App\Repository\EventRepository;
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
    private const NB_DAYS_BEFORE_CLOSING_PRO = 30;

    private SymfonyStyle $io;

    public function __construct(
        private SignalementRepository $signalementRepository,
        private SignalementManager $signalementManager,
        private InterventionRepository $interventionRepository,
        private InterventionManager $interventionManager,
        private EventRepository $eventRepository,
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
        $countSignalementsTraitementProToClose = $this->closeSignalementsTraitementPro();
        $countSignalementsToNotify = $this->notifySignalementsTraitementAuto();
        $countSignalementsTraitementAutoToClose = $this->closeSignalementsTraitementAuto();
        $countInterventionsToNotifyUsager = $this->notifyAskInterventionCompleteForUsager();
        $countInterventionsToNotifyPro = $this->notifyAskInterventionCompleteForPro();

        $this->io->success(\sprintf(
            '%s signalements were notified, %s auto-traitement signalements closed, %s traitement pro signalement closed, %s interventions were notified for usager, %s interventions were notified for pro',
            $countSignalementsToNotify,
            $countSignalementsTraitementAutoToClose,
            $countSignalementsTraitementProToClose,
            $countInterventionsToNotifyUsager,
            $countInterventionsToNotifyPro
        ));

        return Command::SUCCESS;
    }

    private function notifySignalementsTraitementAuto(): int
    {
        $signalementsToNotify = $this->signalementRepository->findToNotify();
        $countSignalementsToNotify = \count($signalementsToNotify);
        foreach ($signalementsToNotify as $signalement) {
            $this->io->success(\sprintf('Signalement id %s to notify',
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

        return $countSignalementsToNotify;
    }

    private function closeSignalementsTraitementAuto(): int
    {
        $signalementsToClose = $this->signalementRepository->findTraitementAutoToClose();
        $countCloseSignalementsTraitementAuto = \count($signalementsToClose);
        foreach ($signalementsToClose as $signalement) {
            $this->io->success(\sprintf('Signalement autotraitement id %s is closed',
                $signalement->getUuid()
            ));
            $this->closeSignalement($signalement);
        }

        return $countCloseSignalementsTraitementAuto;
    }

    private function closeSignalementsTraitementPro(): int
    {
        $signalementsWithNoEstimationAccepted = $this->signalementRepository->findTraitementProWithNoEstimationAccepted();
        $countCloseSignalementsTraitementPro = 0;
        foreach ($signalementsWithNoEstimationAccepted as $signalement_id) {
            $todayDate = new \DateTimeImmutable();
            $signalement = $this->signalementManager->findOneBy(['id' => $signalement_id]);

            // if signalement's last event was created less than NB_DAYS_BEFORE_CLOSING_PRO days ago, we don't close
            $usagersEvents = $this->eventRepository->findUsagerEvents($signalement->getUuid());
            $lastEvent = \count($usagersEvents) > 0 ? $usagersEvents[0] : null;
            $lastEventDate = $lastEvent ? $lastEvent['date'] : null;
            if ($lastEvent && $todayDate->sub(new \DateInterval('P'.self::NB_DAYS_BEFORE_CLOSING_PRO.'D')) < $lastEventDate) {
                continue;
            }

            ++$countCloseSignalementsTraitementPro;
            $this->io->success(\sprintf('Signalement pro id %s is closed',
                $signalement->getUuid()
            ));
            $this->closeSignalement($signalement);
        }

        return $countCloseSignalementsTraitementPro;
    }

    private function closeSignalement(Signalement $signalement)
    {
        $signalement->setClosedAt(new \DateTimeImmutable());
        $signalement->updateUuidPublic();
        $this->signalementManager->save($signalement);
        $this->mailerProvider->sendSignalementClosedByWebsite($signalement);

        $this->eventDispatcher->dispatch(
            new SignalementClosedEvent(
                signalement: $signalement,
                isAdminAction: true,
            ),
            SignalementClosedEvent::NAME
        );
    }

    private function notifyAskInterventionCompleteForUsager(): int
    {
        $interventionsToNotifyUsager = $this->interventionRepository->findToNotifyUsager();
        $countInterventionsToNotifyUsager = \count($interventionsToNotifyUsager);
        foreach ($interventionsToNotifyUsager as $intervention) {
            $this->io->success(\sprintf('Intervention id %s to notify for usager',
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

        return $countInterventionsToNotifyUsager;
    }

    private function notifyAskInterventionCompleteForPro(): int
    {
        $interventionsToNotifyPro = $this->interventionRepository->findToNotifyPro();
        $countInterventionsToNotifyPro = \count($interventionsToNotifyPro);
        foreach ($interventionsToNotifyPro as $intervention) {
            $this->io->success(\sprintf('Intervention id %s to notify for pro',
                $intervention->getId()
            ));
            $intervention->setReminderPendingEntrepriseConclusionAt(new \DateTimeImmutable());
            $this->interventionManager->save($intervention);
            $this->mailerProvider->sendSignalementSuiviTraitementProForPro($intervention);
        }

        return $countInterventionsToNotifyPro;
    }
}
