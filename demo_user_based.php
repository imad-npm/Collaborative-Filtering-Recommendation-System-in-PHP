<?php

require_once 'UserBasedRecommender.php';
require_once './SimpleCsv.php';

if ($argc < 2) {
    echo "Usage: php demo_user_based.php <user>\n";
    exit(1);
}

$user = $argv[1];

// Read the data from the CSV file and convert it to a PHP array
$dataset = SimpleCsv::load("datasets/data_user_based.csv")->all();

$recommender = new UserBasedRecommender($dataset);
$recommendations = $recommender->getRecommendations($user);

if (empty($recommendations)) {
    echo "No personalized recommendations found for $user. Recommending most popular items instead:\n";
    $popularItems = $recommender->getPopularItems();
    foreach ($popularItems as $item => $score) {
        echo "- $item (Average Rating: " . round($score, 2) . ")\n";
    }
} else {
    echo "Recommendations for $user:\n";
    foreach ($recommendations as $item => $score) {
        echo "- $item (Score: " . round($score, 2) . ")\n";
    }
}
