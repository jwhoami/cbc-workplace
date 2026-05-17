<?php

return [
    'stats' => [
        'candidates' => [
            'label' => 'Candidates',
            'description' => 'Candidates with professional profile',
        ],
        'organizations' => [
            'label' => 'Organizations',
            'description' => 'Total and verified',
        ],
        'offers' => [
            'label' => 'Active offers',
            'description' => 'Published and active offers',
        ],
        'applications' => [
            'label' => 'Applications (24h)',
            'description' => 'Received in the last 24 hours',
        ],
    ],

    'pending_verifications' => [
        'heading' => 'Organizations pending verification',
        'empty' => 'No organizations pending verification.',
        'ver_todas' => 'See all →',
    ],

    'pending_offers' => [
        'heading' => 'Offers pending approval',
        'empty' => 'No offers pending approval.',
        'ver_todas' => 'See all →',
    ],

    'recent_applications' => [
        'heading' => 'Recent applications',
        'empty' => 'No applications received yet.',
    ],

    'columns' => [
        'display_name' => 'Organization',
        'title' => 'Offer',
        'organization' => 'Organization',
        'member' => 'Candidate',
        'submitted_at' => 'Submitted at',
        'created_at' => 'Created at',
    ],
];
