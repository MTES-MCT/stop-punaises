<?php

namespace App\Command;

use App\Entity\Signalement;
use App\Event\InterventionEntrepriseAcceptedEvent;
use App\Event\InterventionEntrepriseRefusedEvent;
use App\Event\InterventionEntrepriseResolvedEvent;
use App\Event\InterventionEstimationSentEvent;
use App\Event\InterventionRemindedEvent;
use App\Event\InterventionUsagerAcceptedEvent;
use App\Event\InterventionUsagerRefusedEvent;
use App\Event\SignalementAddedEvent;
use App\Event\SignalementClosedEvent;
use App\Event\SignalementRemindedEvent;
use App\Event\SignalementResolvedEvent;
use App\Event\SignalementSwitchedEvent;
use App\Repository\SignalementRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:init-signalement-events',
    description: 'Initialize the missing events from a signalement'
)]
class InitSignalementEventsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private SignalementRepository $signalementRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('signalement_uuid', InputArgument::REQUIRED, 'Signalement UUID');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uuid = $input->getArgument('signalement_uuid');
        if (empty($uuid)) {
            $this->io->error('UUID is missing');

            return Command::FAILURE;
        }

        $signalements = $this->signalementRepository->findBy(['uuid' => $uuid]);
        if (empty($signalements)) {
            $this->io->error('Signalement not found');

            return Command::FAILURE;
        }

        $signalement = $signalements[0];
        $this->initEvents($signalement);
        $this->io->success('Events created for signalement');

        return Command::SUCCESS;
    }

    private function initEvents(Signalement $signalement)
    {
        $this->eventDispatcher->dispatch(
            new SignalementAddedEvent(
                $signalement,
                $this->parameterBag->get('base_url').'/build/'.$this->parameterBag->get('doc_autotraitement'),
                $signalement->getCreatedAt(),
            ),
            SignalementAddedEvent::NAME
        );

        foreach ($signalement->getInterventions() as $intervention) {
            if ($intervention->getChoiceByEntrepriseAt()) {
                if ($intervention->isAccepted()) {
                    $this->eventDispatcher->dispatch(
                        new InterventionEntrepriseAcceptedEvent(
                            $intervention,
                            $intervention->getEntreprise()->getUser()->getId(),
                            $intervention->getChoiceByEntrepriseAt(),
                        ),
                        InterventionEntrepriseAcceptedEvent::NAME
                    );
                } else {
                    $this->eventDispatcher->dispatch(
                        new InterventionEntrepriseRefusedEvent(
                            $intervention,
                            $intervention->getEntreprise()->getUser()->getId(),
                            $intervention->getChoiceByEntrepriseAt(),
                        ),
                        InterventionEntrepriseRefusedEvent::NAME
                    );
                }
            }

            if ($intervention->getEstimationSentAt()) {
                $this->eventDispatcher->dispatch(
                    new InterventionEstimationSentEvent(
                        $intervention,
                        $intervention->getEntreprise()->getUser()->getId(),
                        $intervention->getEstimationSentAt(),
                    ),
                    InterventionEstimationSentEvent::NAME
                );
            }

            if ($intervention->getChoiceByUsagerAt()) {
                if ($intervention->isAcceptedByUsager()) {
                    $this->eventDispatcher->dispatch(
                        new InterventionUsagerAcceptedEvent(
                            $intervention,
                            $intervention->getChoiceByUsagerAt(),
                        ),
                        InterventionUsagerAcceptedEvent::NAME
                    );
                } else {
                    $this->eventDispatcher->dispatch(
                        new InterventionUsagerRefusedEvent(
                            $intervention,
                            $intervention->getChoiceByUsagerAt(),
                        ),
                        InterventionUsagerRefusedEvent::NAME
                    );
                }
            }

            if ($intervention->getResolvedByEntrepriseAt()) {
                $this->eventDispatcher->dispatch(
                    new InterventionEntrepriseResolvedEvent(
                        $intervention,
                        $intervention->getResolvedByEntrepriseAt(),
                    ),
                    InterventionEntrepriseResolvedEvent::NAME
                );
            }

            if ($intervention->getReminderResolvedByEntrepriseAt()) {
                $this->eventDispatcher->dispatch(
                    new InterventionRemindedEvent(
                        $intervention,
                        $intervention->getResolvedByEntrepriseAt(),
                    ),
                    InterventionRemindedEvent::NAME
                );
            }
        }

        if ($signalement->getSwitchedTraitementAt()) {
            if ($signalement->isAutotraitement()) {
                $this->eventDispatcher->dispatch(
                    new SignalementSwitchedEvent(
                        $signalement,
                        $signalement->getSwitchedTraitementAt(),
                    ),
                    SignalementSwitchedEvent::NAME_AUTOTRAITEMENT
                );
            } else {
                $this->eventDispatcher->dispatch(
                    new SignalementSwitchedEvent(
                        $signalement,
                        $signalement->getSwitchedTraitementAt(),
                    ),
                    SignalementSwitchedEvent::NAME_PRO
                );
            }
        }

        if ($signalement->getReminderAutotraitementAt()) {
            $this->eventDispatcher->dispatch(
                new SignalementRemindedEvent(
                    $signalement,
                    $signalement->getClosedAt(),
                ),
                SignalementRemindedEvent::NAME
            );
        }

        if ($signalement->getResolvedAt()) {
            $this->eventDispatcher->dispatch(
                new SignalementResolvedEvent(
                    $signalement,
                    $signalement->getResolvedAt(),
                ),
                SignalementResolvedEvent::NAME
            );
        }

        if ($signalement->getClosedAt()) {
            $this->eventDispatcher->dispatch(
                new SignalementClosedEvent(
                    $signalement,
                    $signalement->getClosedAt(),
                ),
                SignalementClosedEvent::NAME
            );
        }
    }
}
