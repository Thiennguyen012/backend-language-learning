<?php

namespace App\Services\SpeakingAssessment;

use App\Models\SpeakingAssessment\SpeakingAssessment;

interface PronunciationScorer
{
    /** @return array<string, mixed> */
    public function score(SpeakingAssessment $assessment): array;
}
