<?php
/**
 * Fichier : DeepLearningPrédictionService.php
 * Rôle : Fichier contenant le service de prédiction énergétique basé sur le Machine Learning.
 */

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

/** 
 * Service utilisant l'Intelligence Artificielle (Deep Learning) via Rubix ML pour générer 
 * des prédictions sur la production d'énergie en fonction de données météorologiques.
 */
class DeepLearningPredictionService
{
    /** @var Pipeline $estimator Le réseau de neurones artificiels utilisé pour faire l'apprentissage et la prédiction. */
    private Pipeline $estimator;

    /** @var array<int, array<int, mixed>> $samples Le tableau des données d'entrainement. */
    private array $samples = [];

    /** @var array<int, float|int|string> $labels Le tableau des étiquettes d'entrainement cible. */
    private array $labels = [];

    /**
     * Contruit le modèle de prédiction et l'entraine immédiatement avec les données d'archives fournies.
     * @param array<int, string> $meteoType Le tableau listant les types d'énergie associés aux archives (ex : ['solaire', 'eolien', ...]).
     * @param array<int, float|int> $temp Le tableau des températures archivées.
     * @param array<int, float|int> $meteoData Le tableau des données météo principales (vent, pluie, soleil) archivées.
     * @param array<int, float|int> $archiveData Le tableau de la production d'énergie réelle (label/cible) associée à chaque entrée météo.
     * @throws \InvalidArgumentException Si les tableaux d'entrée n'ont pas la même taille (incohérence des données).
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
     * Formate les tableaux bruts d'entrée pour les transformer en structures lisibles par l'estimateur ($samples et $labels).
     * @param array<int, string> $meteoType Le tableau des types d'énergies.
     * @param array<int, float|int> $temp Le tableau des températures correspondantes.
     * @param array<int, float|int> $meteoData Le tableau des valeurs météos spécifiques correspondantes.
     * @param array<int, float|int> $archiveData Le tableau de la production d'énergie cible.
     * @return void
     */
    private function shapeData(array $meteoType, array $temp, array $meteoData, array $archiveData) : void
    {
        for ($i = 0; $i < count($archiveData); $i++) {
            $this->samples[] = [$meteoType[$i], $temp[$i], $meteoData[$i]];
            $this->labels[] = $archiveData[$i];
        }
    }

    /**
     * Déclenche l'entraînement (training) du réseau de neurones avec le jeu de données configuré.
     * @return void
     */
    private function train() : void
    {
        $dataset = new Labeled($this->samples, $this->labels);
        $this->estimator->train($dataset);
    }

    /**
     * Prédit la production d'énergie future à partir des prévisions météorologiques en utilisant le modèle entraîné.
     * @param array<int, string> $meteoType Le tableau des types d'énergies pour lesquels on veut prédire.
     * @param array<int, float> $temp Le tableau des prévisions de températures.
     * @param array<int, float> $meteoData Le tableau des prévisions de données météo principales (soleil, pluie, vent).
     * @param array<int, string> $dateString Le tableau des dates et heures associées aux prédictions.
     * @param string $city La ville ciblée pour la prédiction (sera injectée dans la réponse).
     * @return array<int, array<string, mixed>> Un tableau structuré contenant les productions horaires estimées formatées pour le tableau de bord.
     */
    public function predict(array $meteoType, array $temp, array $meteoData, array $dateString, string $city) : array
    {
        if (empty($meteoType)) {
            return [];
        }

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
            $val = $previewData[$i] ?? 0.0;
            $productionValue = is_numeric($val) ? (float)$val : 0.0;

            $prediction[$i] = [
                'date' => $dateString[$i],
                'production' => round($productionValue, 2),
                'ville' => $city,
                'meteo' => $meteoData[$i],
                'temp' => $temp[$i],
                'statut' => 'prevision',
                'algo' => 'lstm'
            ];
        }

        /*
         * Renvoie des données
         */
        return $prediction;
    }
}