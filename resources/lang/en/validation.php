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
    'attributes' => [
        'flashcard_ids' => 'Flashcard list',
        'flashcard' => [
            'original_word' => 'Original word',
            'translated_word' => 'Translated word',
            'word_type_id' => 'Word type',
        ],
        'flashcard_collection' => [
            'collection_name' => 'Collection name',
            'description' => 'Description',
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
