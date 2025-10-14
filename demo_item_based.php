<?php

require_once 'ItemBasedRecommender.php';
require_once 'utils/SimpleCsv.php';

if ($argc < 2) {
    echo "Usage: php demo_item_based.php <user>\n";
    exit(1);
}

$user = $argv[1];

// Read the data from the CSV file and convert it to a PHP array
$dataset = SimpleCsv::load("datasets/data_item_based.csv")->all();

$recommender = new ItemBasedRecommender($dataset);

// Load item similarities from a file, or compute and save them if the file doesn't exist.
$recommender->loadItemSimilarities('cache/item_similarities.csv');

$recommendations = $recommender->getRecommendations($user);

if (empty($recommendations)) {
    echo "No personalized recommendations found for $user. Recommending most popular items instead:\n";
   
} else {
    echo "Recommendations for $user:\n";
    foreach ($recommendations as $item => $score) {
        echo "- $item (Score: " . round($score, 2) . ")\n";
    }
}
