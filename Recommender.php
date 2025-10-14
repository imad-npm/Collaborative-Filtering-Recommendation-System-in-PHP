<?php

class Recommender
{
    protected $dataset;

    public function __construct($dataset)
    {
        $this->dataset = $dataset;
    }

    protected function getAverageRating($ratings)
    {
        // Filter out non-numeric and empty ratings before calculating the average
        $numericRatings = array_filter($ratings, 'is_numeric');
        if (count($numericRatings) === 0) {
            return 0;
        }
        return array_sum($numericRatings) / count($numericRatings);
    }

    

    public function getPopularItems()
    {
        $itemRatings = [];
        $itemCounts = [];

        foreach ($this->dataset as $userData) {
            unset($userData['user']);
            foreach ($userData as $item => $rating) {
                if ($rating !== '') {
                    if (!isset($itemRatings[$item])) {
                        $itemRatings[$item] = 0;
                        $itemCounts[$item] = 0;
                    }
                    $itemRatings[$item] += $rating;
                    $itemCounts[$item]++;
                }
            }
        }

        $popularities = [];
        foreach ($itemRatings as $item => $totalRating) {
            if ($itemCounts[$item] > 0) {
                $popularities[$item] = $totalRating / $itemCounts[$item];
            }
        }

        arsort($popularities);

        return $popularities;
    }
}
