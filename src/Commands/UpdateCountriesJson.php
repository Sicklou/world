<?php

namespace Nnjeim\World\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateCountriesJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world:json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the world data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Chemin du fichier JSON
        $jsonFilePath = './vendor/sicklou/world/resources/json/countries.json';
        $jsonFilePathNew = './vendor/sicklou/world/resources/json/countries_new.json';
        if (file_exists($jsonFilePathNew)) {
            unlink($jsonFilePathNew);
        }

        // Lire le fichier JSON
        $jsonData = file_get_contents($jsonFilePath);
        $countries = json_decode($jsonData, true);

        if ($countries === null) {
            die('Erreur de lecture du fichier JSON');
        }

        $nationalitiesFile = './vendor/sicklou/world/nationalities.csv';
        $nationalities = $this->getCsvAsArray($nationalitiesFile);
        // Lire le fichier CSV

        // Ajouter la nationalité à chaque pays
        foreach ($countries as &$country) {
            $countryName = $country['name'];
            if (isset($nationalities[$countryName])) {
                $country['nationality'] = $nationalities[$countryName];
            } else {
                dump($countryName);
                $country['nationality'] = 'Unknown'; // Ou gérer les cas où la nationalité n'est pas trouvée
            }
        }

        // Encoder les données mises à jour en JSON
        $newJsonData = json_encode($countries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($newJsonData === false) {
            die('Erreur lors de l\'encodage des données JSON');
        }

        // Sauvegarder le fichier JSON mis à jour
        if (file_put_contents($jsonFilePathNew, $newJsonData) === false) {
            die('Erreur lors de l\'écriture du fichier JSON');
        }

    }

    private function getCsvAsArray(string $file)
    {

        $nationalities = [];
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($csvData = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $countryName = Str::of($csvData[0])->before('[')->trim()->toString();
                $nationality = Str::of($csvData[1])->before('[')->trim()->toString();
                $nationalities[$countryName] = $nationality;
            }
            fclose($handle);
        }
        return $nationalities;
    }
}
