<?php

return [
    'invalid' => 'Invalid data',
    'required' => ':attribute is required.',
    'string' => ':attribute must be a string.',
    'email' => ':attribute must be a valid email address.',
    'numeric' => ':attribute must be a number.',
    'integer' => ':attribute must be an integer.',
    'boolean' => ':attribute must be true or false.',
    'date' => ':attribute is not a valid date.',
    'array' => ':attribute must be an array.',
    'in' => ':attribute must be one of the following: :values.',
    'exists' => ':attribute is invalid.',
    'min' => [
        'numeric' => ':attribute must be at least :min.',
        'string' => ':attribute must be at least :min characters.',
    ],
    'max' => [
        'numeric' => ':attribute may not be greater than :max.',
        'string' => ':attribute may not be greater than :max characters.',
    ],
    'unique' => ':attribute has already been taken.',
    'confirmed' => ':attribute confirmation does not match.',
    'attributes' => [
        'flashcard_ids' => 'Flashcard list',
        'question_ids' => 'Question list',
        'flashcard' => [
            'original_word' => 'Original word',
            'translated_word' => 'Translated word',
            'explanation' => 'Explanation',
            'word_type_id' => 'Word type',
        ],
        'flashcard_collection' => [
            'collection_name' => 'Collection name',
            'description' => 'Description',
        ],
        'collection_test' => [
            'test_type_id' => 'Test type',
            'collection_id' => 'Flashcard collection',
            'test_name' => 'Test name',
            'description' => 'Description',
            'total_questions' => 'Total questions',
            'status' => 'Status',
            'started_at' => 'Start time',
            'finished_at' => 'Finish time',
            'question_ids' => 'Question list',
        ],
        'user_test_attempt' => [
            'user_id' => 'User',
            'collection_test_id' => 'Collection test',
            'status' => 'Status',
            'correct_count' => 'Correct count',
            'total_score' => 'Total score',
            'started_time' => 'Start time',
            'finished_time' => 'Finish time',
            'total_time' => 'Total time',
        ],
        'user_test_answer' => [
            'user_test_attempt_id' => 'User test attempt',
            'question_id' => 'Question',
            'user_answer' => 'User answer',
            'is_correct' => 'Is correct',
        ],
        'test_type' => [
            'test_type' => 'Test type',
        ],
        'question' => [
            'question_type_id' => 'Question type',
            'question_text' => 'Question text',
            'question_data' => 'Question data',
        ],
    ],
];
