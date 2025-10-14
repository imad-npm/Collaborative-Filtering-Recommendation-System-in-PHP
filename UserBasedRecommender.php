<?php

require_once 'Recommender.php';
require_once 'Similarity.php';

class UserBasedRecommender extends Recommender
{
    public function getRecommendations($user, $similarityMeasure = 'pearson')
    {
        $totals = [];
        $simSums = [];

        $userRatings = null;
        foreach ($this->dataset as $userData) {
            if ($userData['user'] === $user) {
                $userRatings = $userData;
                unset($userRatings['user']);
                break;
            }
        }

        if ($userRatings === null) {
            return []; // User not found
        }

        $userAverageRating = $this->getAverageRating($userRatings);

        foreach ($this->dataset as $otherUserData) {
            if ($otherUserData['user'] === $user) {
                continue;
            }

            $otherUserRatings = $otherUserData;
            unset($otherUserRatings['user']);

            $sim = Similarity::pearsonCorrelation($userRatings, $otherUserRatings);

            if ($sim <= 0) {
                continue;
            }

            $otherUserAverageRating = $this->getAverageRating($otherUserRatings);

            foreach ($otherUserRatings as $item => $rating) {
                if (!isset($userRatings[$item]) || $userRatings[$item] === '') {
                    if (!isset($totals[$item])) {
                        $totals[$item] = 0;
                    }
                    $totals[$item] += ((float)$rating - $otherUserAverageRating) * $sim;

                    if (!isset($simSums[$item])) {
                        $simSums[$item] = 0;
                    }
                    $simSums[$item] += $sim;
                }
            }
        }

        $rankings = [];
        foreach ($totals as $item => $total) {
            if ($simSums[$item] > 0) {
                $rankings[$item] = $userAverageRating + ($total / $simSums[$item]);
            }
        }

        arsort($rankings);

        return $rankings;
    }
}