<?php
namespace App\Services;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Pipeline;
use Rubix\ML\Regressors\MLPRegressor;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Datasets\Labeled;

class DeepLearningPredictionService
{
    private Pipeline $estimator; // Le réseau de neurones utilisé pour faire la prédiction
    private array $samples = []; // Les données d'entraînement (shape : [[meteoType, temp, meteoData], ...])
    private array $labels = [];  // Les étiquettes d'entraînement (shape : [energyProducted, ...])

    /**
     * @param array $meteoType Type d'énergie utilisé pour la prédiction (format : ['sun', 'rain', 'wind', ...])
     * @param array $temp Température utilisé pour la prédiction (format : [temp, ...])
     * @param array $meteoData Données météo utilisé pour la prédiction (format : [meteoData, ...])
     * @param array $archiveData Production d'énergie associé à chaque entrée de données météo (format : [energyProducted, ...])
     *
     * @brief Construit et entraine le modèle de prédiction
     */
    public function __construct(array $meteoType, array $temp, array $meteoData, array $archiveData)
    {
        if (count($meteoType) !== count($temp) || count($meteoType) !== count($meteoData) || count($meteoType) !== count($archiveData)) {
            throw new \InvalidArgumentException('All input arrays must have the same length');
        }
        $this->shapeData($meteoType, $temp, $meteoData, $archiveData);

        // Définition de la structure du réseaux de neurones
        $this->estimator = new Pipeline([
            // Dit à l'estimateur de transformer les données catégoriques
            // (ex : 'éolien', 'solaire', 'hydrolique')
            // en données numériques (ex : [1, 0, 0], [0, 1, 0], [0, 0, 1])
            new OneHotEncoder(),
            ],
            // Ensuite, on utilise un réseau de neurones pour faire la prédiction
            new MLPRegressor([
                new Dense(10),
                new Activation(new ReLu()),
                new Dense(5),
                new Activation(new ReLu()),
                new Dense(1),
            ], 1000, new Adam(0.001))
        );

        $this->train();
    }

    /**
     * @param array $meteoType      Type d'énergie utilisé pour la prédiction (format : ['sun', 'rain', 'wind', ...])
     * @param array $temp           Température utilisé pour la prédiction (format : [temp, ...])
     * @param array $meteoData      Données météo utilisé pour la prédiction (format : [meteoData, ...])
     * @param array $archiveData    Production d'énergie associé à chaque entrée de données météo (format : [energyProducted, ...])
     * @return void
     *
     * @brief Formate les données d'entrée pour les rendre compatible avec le modèle de prédiction
     */
    private function shapeData(array $meteoType, array $temp, array $meteoData, array $archiveData) : void
    {
        for ($i = 0; $i < count($archiveData); $i++) {
            $this->samples[] = [$meteoType[$i], $temp[$i], $meteoData[$i]];
            $this->labels[] = $archiveData[$i];
        }
    }

    /**
     * @return void
     *
     * @brief Train the model with the given archive data and meteo data
     */
    private function train() : void
    {
        $dataset = new Labeled($this->samples, $this->labels);
        $this->estimator->train($dataset);
    }

    /**
     * @param array $meteoType      Type d'énergie utilisé pour la prédiction (format : ['sun', 'rain', 'wind', ...])
     * @param array $temp           Température utilisé pour la prédiction (format : [temp, ...])
     * @param array $meteoData      Données météo utilisé pour la prédiction (format : [meteoData, ...])
     * @param array $dateString     Les dates de la prédiction (format : [date, ...])
     * @param string $city          Le cite associé à la prédiction (ex : 'Paris', 'Lyon', 'Marseille')
     *
     * @return array                The predicted energy production (format : [[date, production, ville, meteo, temp, status], ...], ...])
     *
     * @warning Faites attention à bien correler les données d'entrée entre elles ( $meteoType[)
     *
     * @brief Prédit la production d'énergie en fonction des données météo, puis le formeate s'addapter au projet
     */
    public function predict(array $meteoType, array $temp, array $meteoData, array $dateString, string $city) : array
    {
        /*
         * Formatage des donné pour la prévision
         */
        $samples = [];
        for($i = 0; $i < count($meteoType); $i++) {
            $samples[] = [$meteoType[$i], $temp[$i], $meteoData[$i]];
        }
        $dataset = new Unlabeled($samples);

        /*
         * Prédiction de la production
         */
        $previewData = $this->estimator->predict($dataset);

        /*
         * Formatage des données revoyé pour faciliter l'incertion au projet
         */
        $prediction = [];
        for ($i = 0; $i < count($samples); $i++) {
            $prediction[$i] = [
                'date' => $dateString[$i],
                'production' => round($previewData[$i], 2),
                'ville' => $city,
                'meteo' => $meteoData[$i],
                'temp' => $temp[$i],
                'statut' => 'prevision'
            ];
        }

        /*
         * Renvoie des données
         */
        return $prediction;
    }
}