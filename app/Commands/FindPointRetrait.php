<?php

namespace App\Commands;

use App\Http\Integrations\Colissimo\ApiConnector;
use App\Http\Integrations\Colissimo\Requests\FindRDVPointRetraitAcheminement;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use LaravelZero\Framework\Commands\Command;
use Saloon\XmlWrangler\XmlReader;

class FindPointRetrait extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:point-retrait';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get config
        $config = $this->getConfig();

        // Ask for user input
        $address = $this->ask("Entrez l'adresse du client", '12 rue Ernest Renan', ['required']);
        $zipCode = $this->ask('Entrez le code postal', '13005');
        $city = $this->ask('Entrez la ville', 'Marseille');
        $countryCode = $this->choice('Entrez le pays', array_keys($config['country']), 0);
        $optionInter = $config['country'][$countryCode]['option_inter'];
        $filter = $this->choice('Filtrez les résultats', array_values($config['filter']), 1);
        $filterRelay = array_search($filter, $config['filter']);
        // $weight = $this->ask('Entrez le poids du colis', '250');

        // Send the request
        $apiConnector = new ApiConnector;
        $request = new FindRDVPointRetraitAcheminement($address, $zipCode, $city, $countryCode, $filterRelay, $optionInter, $weight = 1);
        $response = $apiConnector->send($request);

        // Handle the response
        if ($response->status() === 200) {
            // Encode response body in UTF-8
            $body = utf8_encode($response->body());
            $reader = XmlReader::fromString($body);
            $result = $reader->value('soap:Envelope.soap:Body.ns2:findRDVPointRetraitAcheminementResponse.return')->sole();

            if ($result['errorCode'] == 0) {

                if (isset($result['listePointRetraitAcheminement'])) {
                    // Create an empty table
                    $table = [];

                    // Fill-in the table
                    foreach ($result['listePointRetraitAcheminement'] as $data) {
                        $table[] = [
                            $data['identifiant'],
                            $data['nom'],
                            $data['adresse1'],
                            $data['adresse2'],
                            $data['adresse3'],
                            $data['localite'],
                            $data['codePostal'],
                            $data['codePays'],
                        ];
                    }

                    // Display the table
                    $this->table(
                        ['Identifiant', 'Nom', 'Adresse 1', 'Adresse 2', 'Adresse 3', 'Localité', 'Code postal', 'Code pays'],
                        $table,
                    );
                }
            } else {
                $this->error($result['errorMessage']);
            }

            return Command::SUCCESS;
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    protected function getConfig()
    {
        return [
            'country' => [
                'FR' => [
                    'name' => 'France',
                    'option_inter' => '0',
                    'max_weight' => 30000,
                ],
                'BE' => [
                    'name' => 'Belgique',
                    'option_inter' => '2',
                    'max_weight' => 20000,
                ],
                'NL' => [
                    'name' => 'Pays-Bas',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'DE' => [
                    'name' => 'Allemagne',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'GB' => [
                    'name' => 'Angleterre',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'IT' => [
                    'name' => 'Italie',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'IE' => [
                    'name' => 'Irlande',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'LU' => [
                    'name' => 'Luxembourg',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'ES' => [
                    'name' => 'Espagne',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'PT' => [
                    'name' => 'Portugal',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'AT' => [
                    'name' => 'Autriche',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'LT' => [
                    'name' => 'Lituanie',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'LV' => [
                    'name' => 'Lettonie',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'EE' => [
                    'name' => 'Estonie',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'SE' => [
                    'name' => 'Suede',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'PL' => [
                    'name' => 'Pologne',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'DK' => [
                    'name' => 'Danemark',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'HU' => [
                    'name' => 'Hongrie',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'CZ' => [
                    'name' => 'République tchèque',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'SK' => [
                    'name' => 'SK',
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
            ],
            'filter' => [
                '0' => 'Bureaux de poste seulement',
                '1' => 'Bureaux de poste et boutiques',
            ],
        ];
    }
}
