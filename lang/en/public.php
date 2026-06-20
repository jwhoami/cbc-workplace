<?php

declare(strict_types=1);

return [
    'listing' => [
        'title' => 'Job Board',
        'subtitle' => 'Find active opportunities to serve and work.',
        'result_count' => '{0} No results|{1} 1 offer|[2,*] :count offers',
        'row' => [
            'published_on' => 'Published :date',
            'work_mode' => 'Mode',
            'contract_type' => 'Contract',
            'city' => 'City',
            'organization' => 'Organization',
        ],
        'empty' => [
            'title' => 'No active offers right now',
            'message' => 'Check back soon. New opportunities appear here as soon as they are approved.',
            'with_filters' => [
                'title' => 'No offers match those filters',
                'message' => 'Try clearing a filter or broadening your search.',
                'cta' => 'Clear filters',
            ],
        ],
    ],

    'detail' => [
        'title' => 'Offer detail',
        'description' => 'Description',
        'requirements' => 'Requirements',
        'category' => 'Category',
        'contract_type' => 'Contract type',
        'work_mode' => 'Work mode',
        'location' => 'Location',
        'salary' => 'Salary range',
        'salary_unspecified' => 'Not specified',
        'publication_date' => 'Publication date',
        'application_deadline' => 'Application deadline',
        'organization' => 'About the organization',
        'organization_website' => 'Website',
    ],

    'filters' => [
        'title' => 'Filters',
        'category' => 'Category',
        'work_mode' => 'Work mode',
        'contract' => 'Contract type',
        'city' => 'City',
        'clear_all' => 'Clear all filters',
        'apply' => 'Apply',
        'sort' => [
            'label' => 'Sort by',
            'recent' => 'Most recent',
            'deadline' => 'Nearest deadline',
        ],
        'search_placeholder' => 'Search by keyword',
    ],

    'cta' => [
        'anonymous' => [
            'title' => 'Interested in this offer?',
            'message' => 'Sign in or register to apply.',
            'sign_in' => 'Sign in',
            'register' => 'Register',
        ],
        'member_no_profile' => [
            'title' => 'Almost ready to apply',
            'message' => 'Complete your candidate profile to apply for this offer.',
            'complete_profile' => 'Complete profile',
        ],
        'member_candidate' => [
            'button' => 'Apply',
        ],
    ],

    'gone' => [
        'title' => 'This offer is no longer available',
        'message' => 'It may have expired or been withdrawn by the organization.',
        'cta' => 'See other active offers',
    ],

    'not_found' => [
        'title' => 'We could not find this offer',
        'message' => 'The address you opened does not match any offer in the system.',
        'cta' => 'Back to the job board',
    ],

    'error' => [
        'title' => 'We could not load the results',
        'message' => 'We are having a technical issue. Please try again in a few seconds.',
        'retry' => 'Retry',
    ],

    'too_many_requests' => [
        'title' => 'Too many requests',
        'message' => 'You have made many searches in a short time. Please wait a moment and try again.',
        'retry_in' => 'Try again in :seconds seconds.',
    ],
];
