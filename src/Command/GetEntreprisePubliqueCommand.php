<?php

namespace App\Command;

use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:get-entreprise-publique',
    description: 'Get entreprise publique to csv file',
)]
class GetEntreprisePubliqueCommand extends Command
{
    private const DISTANT_URL = 'https://cs3d-expertise-punaises.fr/zone/';
    private const SLUGS = [
        '01' => '01-ain',
        '02' => '02-aisne',
        '03' => '03-allier',
        '04' => '04-alpes-de-haute-provence',
        '05' => '05-hautes-alpes',
        '06' => '06-alpes-maritimes',
        '07' => '07-ardeche',
        '08' => '08-ardennes',
        '09' => '09-ariege',
        '10' => '10-aube',
        '11' => '11-aude',
        '12' => '12-aveyron',
        '13' => '13-bouches-du-rhone',
        '14' => '14-calvados',
        '15' => '15-cantal',
        '16' => '16-charente',
        '17' => '17-charente-maritime',
        '18' => '18-cher',
        '19' => '19-correze',
        '21' => '21-cote-dor',
        '22' => '22-cotes-darmor',
        '23' => '23-creuse',
        '24' => '24-dordogne',
        '25' => '25-doubs',
        '26' => '26-drome',
        '27' => '27-eure',
        '28' => '28-eure-et-loir',
        '29' => '29-finistere',
        '2A' => '2a-corse-du-sud',
        '2B' => '2b-haute-corse',
        '30' => '30-gard',
        '31' => '31-haute-garonne',
        '32' => '32-gers',
        '33' => '33-gironde',
        '34' => '34-herault',
        '35' => '35-ille-et-vilaine',
        '36' => '36-indre',
        '37' => '37-indre-et-loire',
        '38' => '38-isere',
        '39' => '39-jura',
        '40' => '40-landes',
        '41' => '41-loir-et-cher',
        '42' => '42-loire',
        '43' => '43-haute-loire',
        '44' => '44-loire-atlantique',
        '45' => '45-loiret',
        '46' => '46-lot',
        '47' => '47-lot-et-garonne',
        '48' => '48-lozere',
        '49' => '49-maine-et-loire',
        '50' => '50-manche',
        '51' => '51-marne',
        '52' => '52-haute-marne',
        '53' => '53-mayenne',
        '54' => '54-meurthe-et-moselle',
        '55' => '55-meuse',
        '56' => '56-morbihan',
        '57' => '57-moselle',
        '58' => '58-nievre',
        '59' => '59-nord',
        '60' => '60-oise',
        '61' => '61-orne',
        '62' => '62-pas-de-calais',
        '63' => '63-puy-de-dome',
        '64' => '64-pyrenees-atlantiques',
        '65' => '65-hautes-pyrenees',
        '66' => '66-pyrenees-orientales',
        '67' => '67-bas-rhin',
        '68' => '68-haut-rhin',
        '69' => '69-rhone',
        '70' => '70-haute-saone',
        '71' => '71-saone-et-loire',
        '72' => '72-sarthe',
        '73' => '73-savoie',
        '74' => '74-haute-savoie',
        '75' => '75-paris',
        '76' => '76-seine-maritime',
        '77' => '77-seine-et-marne',
        '78' => '78-yvelines',
        '79' => '79-deux-sevres',
        '80' => '80-somme',
        '81' => '81-tarn',
        '82' => '82-tarn-et-garonne',
        '83' => '83-var',
        '84' => '84-vaucluse',
        '85' => '85-vendee',
        '86' => '86-vienne',
        '87' => '87-haute-vienne',
        '88' => '88-vosges',
        '89' => '89-yonne',
        '90' => '90-territoire-de-belfort',
        '91' => '91-essonne',
        '92' => '92-hauts-de-seine',
        '93' => '93-seine-st-denis',
        '94' => '94-val-de-marne',
        '95' => '95-val-doise',
        '971' => '971-guadeloupe',
        '972' => '972-martinique',
        '973' => '973-guyane',
        '974' => '974-la-reunion',
        '976' => '976-mayotte',
    ];

    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    /**
     * @throws FilesystemException
     * @throws NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dataToSave = [];
        foreach (self::SLUGS as $zip => $slug) {
            $this->parseUrl($dataToSave, $zip, $slug);
        }

        $csvFileName = $this->parameterBag->get('uploads_tmp_dir').'entreprises.csv';
        $file = fopen($csvFileName, 'w');
        $entete = ['zip', 'nom', 'adresse', 'url', 'telephone'];
        fputcsv($file, $entete);
        foreach ($dataToSave as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return Command::SUCCESS;
    }

    private function parseUrl(array &$dataToSave, $zip, $slug)
    {
        $htmlContent = file_get_contents(self::DISTANT_URL.$slug.'/');

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($htmlContent);
        $xpath = new \DOMXPath($dom);
        $postsDiv = $xpath->query('//div[@id="posts"]')->item(0);
        if ($postsDiv) {
            $childDivs = $xpath->query('./div', $postsDiv);

            foreach ($childDivs as $childDiv) {
                $h1Query = $xpath->query('.//h1', $childDiv);
                if ($h1Query->item(0)) {
                    $entrepriseItem = [
                        'zip' => $zip,
                        'nom' => '',
                        'adresse' => '',
                        'url' => '',
                        'telephone' => '',
                    ];

                    $name = $h1Query->item(0)->textContent;
                    $entrepriseItem['nom'] = $name;

                    $locationQuery = $xpath->query('.//li[@class="location"]//a', $childDiv);
                    if ($locationQuery->item(0)) {
                        $location = $locationQuery->item(0)->textContent;
                        $entrepriseItem['adresse'] = $location;
                    }

                    $websiteQuery = $xpath->query('.//li[@class="website"]/a', $childDiv);
                    if ($websiteQuery->item(0)) {
                        /** @var \DOMElement $item */
                        $item = $websiteQuery->item(0);
                        $websiteLink = $item->getAttribute('href');
                        $entrepriseItem['url'] = $websiteLink;
                    }

                    $telephoneQuery = $xpath->query('.//li[@class="telephone"]', $childDiv);
                    if ($telephoneQuery->item(0)) {
                        /** @var \DOMElement $item */
                        $item = $telephoneQuery->item(0);
                        $telephone = $item->getAttribute('data-tel');
                        $entrepriseItem['telephone'] = $telephone;
                    }

                    $dataToSave[] = $entrepriseItem;
                }
            }
        }
    }
}
