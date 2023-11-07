<?php

namespace App\Tests\Functional\Service;

use App\Service\Signalement\ZipCodeProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ZipCodeProviderTest extends KernelTestCase
{
    private ZipCodeProvider $zipcodeProvider;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->zipcodeProvider = $container->get(ZipcodeProvider::class);
    }

    public function testGetZipCodeTerritory(): void
    {
        $this->assertEquals(
            '2A',
            $this->zipcodeProvider->getByCodePostal('20167'),
        );
        $this->assertEquals(
            '2B',
            $this->zipcodeProvider->getByCodePostal('20600'),
        );
        $this->assertEquals(
            '974',
            $this->zipcodeProvider->getByCodePostal('97400')
        );
        $this->assertEquals(
            '972',
            $this->zipcodeProvider->getByCodePostal('97200')
        );
        $this->assertEquals(
            '13',
            $this->zipcodeProvider->getByCodePostal('13002')
        );
    }
}
