<?php

namespace App\Rules;

use App\Models\QuestionType\QuestionType;
use Illuminate\Contracts\Validation\Rule;

class QuestionDataRule implements Rule
{
    protected $questionTypeId;
    protected $message;

    public function __construct($questionTypeId)
    {
        $this->questionTypeId = $questionTypeId;
        $this->message = __('validation.invalid');
    }

    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $typeKeyword = QuestionType::query()
            ->where('id', $this->questionTypeId)
            ->value('keyword');

        if (!$typeKeyword) {
            return false;
        }

        switch (strtolower($typeKeyword)) {
            case 'multiple_choice':
                return $this->validateMultipleChoice($value);
            case 'true_false':
                return $this->validateTrueFalse($value);
            case 'fill_in_blank':
                return $this->validateFillBlank($value);
            case 'matching':
                return $this->validateMatching($value);
            default:
                return false;
        }
    }

    public function message(): string
    {
        return $this->message;
    }

    protected function validateMultipleChoice(array $value): bool
    {
        if (!isset($value['options']) || !is_array($value['options']) || count($value['options']) < 2) {
            return false;
        }

        foreach ($value['options'] as $option) {
            if (!is_string($option) || $option === '') {
                return false;
            }
        }

        if (!array_key_exists('correct', $value)) {
            return false;
        }

        if (is_int($value['correct'])) {
            return $value['correct'] >= 0 && $value['correct'] < count($value['options']);
        }

        if (is_string($value['correct'])) {
            return in_array($value['correct'], $value['options'], true);
        }

        return false;
    }

    protected function validateTrueFalse(array $value): bool
    {
        return array_key_exists('correct', $value) && is_bool($value['correct']);
    }

    protected function validateFillBlank(array $value): bool
    {
        return isset($value['answer']) && is_string($value['answer']) && $value['answer'] !== '';
    }

    protected function validateMatching(array $value): bool
    {
        if (!isset($value['pairs']) || !is_array($value['pairs']) || count($value['pairs']) === 0) {
            return false;
        }

        foreach ($value['pairs'] as $pair) {
            if (!is_array($pair)) {
                return false;
            }
            if (!isset($pair['left'], $pair['right'])) {
                return false;
            }
            if (!is_string($pair['left']) || $pair['left'] === '') {
                return false;
            }
            if (!is_string($pair['right']) || $pair['right'] === '') {
                return false;
            }
        }

        return true;
    }
}
