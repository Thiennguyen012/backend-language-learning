<?php

namespace App\Services\SpeakingAssessment;

use App\Models\SpeakingAssessment\SpeakingLevelAssessment;

interface SpeakingLevelEvaluator
{
    /** @return array<string, mixed> */
    public function evaluate(SpeakingLevelAssessment $assessment): array;
}
