<?php
namespace App\Models;

use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Pipeline;
use Rubix\ML\Regressors\MLPRegressor;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Datasets\Labeled;

class Prev_Deep_Learning
{
    private Pipeline $estimator;
    private array $samples; // Les données d'entraînement (shape : [[meteoType, temp, meteoData], ...])
    private array $labels;  // Les étiquettes d'entraînement (shape : [energyProducted, ...])
    /**
     * @param array $meteoType    The type of energy to predict (ex : 'sun', 'rain', 'wind')
     * @param array $temp          The temperature data to use for the prediction (shape : [temp, ...])
     * @param array $meteoData     The meteorological data to use for the prediction (shape : [meteoData, ...])
     * @param array $archiveData   The archive data to use for the training (shape : [energyProducted, ...])
     *
     * @brief Construct the model with the given energy type, meteo data and archive data
     */
    public function __construct(array $meteoType, array $temp, array $meteoData, array $archiveData)
    {
        if (count($meteoType) !== count($temp) || count($meteoType) !== count($meteoData) || count($meteoType) !== count($archiveData)) {
            throw new \InvalidArgumentException('All input arrays must have the same length');
        }
        $this->shapeData($meteoType, $temp, $meteoData, $archiveData);

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
     * @param int $start            The begenning of the period to predict (format: 'Y-m-d')
     * @param int $end              The end of the period to predict (format: 'Y-m-d')
     * @param array $meteoType      Type of energy to predict (ex : 'sun', 'rain', 'wind')
     * @param array $temp           Temperature data to use for the prediction (format : [temp, ...])
     * @param array $meteoData      Data to use for the prediction (format : [meteoData, ...])
     * @return void
     *
     * @brief Predict the energy production for the given meteo data
     */
    public function predict(int $start, int $end, array $meteoType, array $temp, array $meteoData)
    {

    }
}