<?php

namespace App\Commands;

use App\Http\Integrations\Colissimo\ApiConnector;
use App\Http\Integrations\Colissimo\Requests\FindRDVPointRetraitAcheminement;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Validator;
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
        $address = $this->ask('What is the address?', '12 rue Ernest Renan', ['required']);
        $zipCode = $this->ask('What is the zip code?', '13005');
        $city = $this->ask('What is the city?', 'Marseille');
        $countryCode = $this->choice('What is the country code?', array_keys($config['country']), 0);
        $optionInter = $config['country'][$countryCode]['option_inter'];
        $filterRelay = ($countryCode === 'FR') ? 1 : 0;
        $weight = $this->ask('What is the weight in grams?', '250');

        // Validate the weight
        $validator = Validator::make([
            $weight,
        ], [
            'weight' => ['max:'.$config['country'][$countryCode]['max_weight']],
        ]);
        if ($validator->fails()) {
            $validator->errors()->all();
        }

        // Send the request
        $apiConnector = new ApiConnector;
        $request = new FindRDVPointRetraitAcheminement($address, $zipCode, $city, $countryCode, $filterRelay, $optionInter, $weight);
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
                            $data['codePostal'],
                            $data['codePays'],
                        ];
                    }

                    // Display the table
                    $this->table(
                        ['Identifiant', 'Nom', 'Adresse 1', 'Adresse 2', 'Adresse 3', 'Code postal', 'Code pays'],
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
                    'option_inter' => '0',
                    'max_weight' => 30000,
                ],
                'BE' => [
                    'option_inter' => '2',
                    'max_weight' => 20000,
                ],
                'NL' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'DE' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'GB' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'IT' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'IE' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'LU' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'ES' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'PT' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'AT' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'LT' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'LV' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'EE' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'SE' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'PL' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'DK' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'HU' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'CZ' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
                'SK' => [
                    'option_inter' => '1',
                    'max_weight' => 20000,
                ],
            ],
        ];
    }
}
