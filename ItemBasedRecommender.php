<?php

require_once 'Recommender.php';
require_once 'Similarity.php';
require_once 'utils/SimpleCsv.php';

class ItemBasedRecommender extends Recommender
{
    private $itemSimilarities = [];

    public function getRecommendations($user)
    {
        if (!$this->userExists($user)) {
            return [];
        }

        if (empty($this->itemSimilarities)) {
            $this->itemSimilarities = $this->calculateSimilarItems();
        }

        $userRatings = $this->getUserRatings($user);
      
        return $this->getRecommendedItems($userRatings, $this->itemSimilarities);
    }

    public function loadItemSimilarities(string $file): void
    {
        if (!file_exists($file)) {
            $this->itemSimilarities = $this->calculateSimilarItems();
            $this->saveSimilarities($file);
        } else {
            $this->itemSimilarities = $this->loadSimilaritiesFromFile($file);
        }
    }

    private function loadSimilaritiesFromFile(string $file): array
    {
        $csv = SimpleCsv::load($file);
        $similarities = [];
        foreach ($csv->all() as $row) {
            $item1 = $row['item1'];
            $item2 = $row['item2'];
            $similarity = (float) $row['similarity'];
            $similarities[$item1][$item2] = $similarity;
        }
        return $similarities;
    }

    private function saveSimilarities(string $file): void
    {
        $rows = [];
        foreach ($this->itemSimilarities as $item1 => $sims) {
            foreach ($sims as $item2 => $similarity) {
                $rows[] = ['item1' => $item1, 'item2' => $item2, 'similarity' => $similarity];
            }
        }

        if (!empty($rows)) {
            SimpleCsv::createFromArray($file, $rows);
        }
    }

    private function userExists($user)
    {
        foreach ($this->dataset as $itemData) {
            if (isset($itemData[$user])) {
                return true;
            }
        }
        return false;
    }

    private function getUserRatings($user)
    {
        $userRatings = [];
        foreach ($this->dataset as $itemData) {
            if (isset($itemData['item'])) {
                $itemName = $itemData['item'];
                if (isset($itemData[$user])) {
                    $userRatings[$itemName] = $itemData[$user];
                }
            }
        }
        return $userRatings;
    }

    private function getRecommendedItems($userRatings, $itemSimilarities)
    {
        $scores = [];
        $totalSim = [];
        $userAverageRating = $this->getAverageRating($userRatings);


        foreach ($userRatings as $item => $rating) {
            if ($rating === '') {
                continue;
            }

            if (isset($itemSimilarities[$item]) && is_array($itemSimilarities[$item])) {
                foreach ($itemSimilarities[$item] as $item2 => $similarity) {
                    if (isset($userRatings[$item2]) && $userRatings[$item2] !== '') {
                        continue;
                    }

                    if (!isset($scores[$item2])) {
                        $scores[$item2] = 0;
                        $totalSim[$item2] = 0;
                    }

                    $scores[$item2] += (float)($rating-$userAverageRating) * $similarity;
                    $totalSim[$item2] += $similarity;
                }
            }
        }

        $rankings = [];
        foreach ($scores as $item => $score) {
            if ($totalSim[$item] > 0) {
                $rankings[$item] = $userAverageRating+$score / $totalSim[$item];
            }
        }

        arsort($rankings);

        return $rankings;
    }

    private function calculateSimilarItems()
    {
        $itemSimilarities = [];

        // In this context, the dataset is already item-based, so we can calculate similarity directly.
        $itemData = [];
        foreach ($this->dataset as $row) {
            $itemName = $row['item'];
            unset($row['item']);
            $itemData[$itemName] = $row;
        }

        foreach ($itemData as $item1 => $ratings1) {
            foreach ($itemData as $item2 => $ratings2) {
                if ($item1 === $item2) {
                    continue;
                }

                $sim = Similarity::pearsonCorrelation($ratings1, $ratings2);

                if ($sim > 0) {
                    $itemSimilarities[$item1][$item2] = $sim;
                }
            }
        }

        return $itemSimilarities;
    }
}