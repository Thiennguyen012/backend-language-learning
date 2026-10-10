<?php

return [
    'preparation_seconds' => (int) env('SPEAKING_LEVEL_PREPARATION_SECONDS', 15),
    'response_seconds' => (int) env('SPEAKING_LEVEL_RESPONSE_SECONDS', 90),
    'topics' => [
        'daily-life' => [
            'label' => 'Daily life',
            'questions' => [
                'Describe a typical day in your life and explain which part you enjoy most.',
                'Tell me about a habit that has improved your daily life.',
            ],
        ],
        'education' => [
            'label' => 'Education',
            'questions' => [
                'Describe a teacher who influenced you and explain why.',
                'Do you think online learning can replace classroom learning? Why or why not?',
            ],
        ],
        'technology' => [
            'label' => 'Technology',
            'questions' => [
                'How has technology changed the way people learn languages?',
                'What are the advantages and disadvantages of artificial intelligence in daily life?',
            ],
        ],
        'travel' => [
            'label' => 'Travel',
            'questions' => [
                'Describe a place you would like to visit and explain your reasons.',
                'What can people learn by travelling to another country?',
            ],
        ],
        'work' => [
            'label' => 'Work',
            'questions' => [
                'What skills are most important for success at work today?',
                'Would you prefer to work from home or in an office? Explain your choice.',
            ],
        ],
        'environment' => [
            'label' => 'Environment',
            'questions' => [
                'What can individuals do to protect the environment?',
                'Should governments limit the use of private cars in large cities? Why or why not?',
            ],
        ],
    ],
];
