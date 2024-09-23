<?php

namespace App\Tests\Functional\Manager;

use App\Dto\Command\CommandContext;
use App\Entity\Enum\HistoryEntryEvent;
use App\Entity\HistoryEntry;
use App\Entity\User;
use App\Factory\HistoryEntryFactory;
use App\Manager\HistoryEntryManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class HistoryEntryManagerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private CommandContext $commandContext;
    private HistoryEntryFactory $historyEntryFactory;
    private HistoryEntryManager $historyEntryManager;

    protected ManagerRegistry $managerRegistry;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->managerRegistry = static::getContainer()->get(ManagerRegistry::class);
        $this->historyEntryFactory = static::getContainer()->get(HistoryEntryFactory::class);
        $this->requestStack = static::getContainer()->get(RequestStack::class);
        $this->commandContext = static::getContainer()->get(CommandContext::class);
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->historyEntryManager = new HistoryEntryManager(
            $this->historyEntryFactory,
            $this->requestStack,
            $this->commandContext,
            $this->managerRegistry,
            HistoryEntry::class,
        );
    }

    public function testCreateHistoryEntry()
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'company-01@punaises.fr']);

        $historyEntry = $this->historyEntryManager->create(
            historyEntryEvent: HistoryEntryEvent::LOGIN,
            entityHistory: $user,
        );

        $this->assertInstanceOf(HistoryEntry::class, $historyEntry);
        $this->assertEquals(HistoryEntryEvent::LOGIN, $historyEntry->getEvent());
    }
}
