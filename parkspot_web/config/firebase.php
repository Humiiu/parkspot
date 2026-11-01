<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Realtime Database Configuration
    |--------------------------------------------------------------------------
    |
    | Base URL of the Firebase Realtime Database for the ParkSpot application.
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://parkspot-f925f-default-rtdb.firebaseio.com/'),
    'database_secret' => env('FIREBASE_DATABASE_SECRET', null),
];

