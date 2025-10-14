<?php

class Similarity
{
    public static function pearsonCorrelation($ratings1, $ratings2)
    {
        $sim = [];
        foreach ($ratings1 as $item => $rating) {
            if (isset($ratings2[$item]) && $ratings1[$item] !== '' && $ratings2[$item] !== '') {
                $sim[$item] = 1;
            }
        }

        $n = count($sim);

        if ($n === 0) {
            return 0;
        }

        $sum1 = 0;
        $sum2 = 0;
        $sum1Sq = 0;
        $sum2Sq = 0;
        $pSum = 0;

        foreach ($sim as $item => $v) {
            $sum1 += (float)$ratings1[$item];
            $sum2 += (float)$ratings2[$item];
            $sum1Sq += pow((float)$ratings1[$item], 2);
            $sum2Sq += pow((float)$ratings2[$item], 2);
            $pSum += (float)$ratings1[$item] * (float)$ratings2[$item];
        }

        $num = $pSum - ($sum1 * $sum2 / $n);
        $den = sqrt(($sum1Sq - pow($sum1, 2) / $n) * ($sum2Sq - pow($sum2, 2) / $n));

        if ($den === 0.0) {
            return 0;
        }

        return $num / $den;
    }
}
