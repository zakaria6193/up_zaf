<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Free Trial Duration (minutes)
    |--------------------------------------------------------------------------
    |
    | New free business accounts can publish their public client page for this
    | many minutes after registration. Premium accounts are unlimited.
    | Keep this low for testing; raise it when you go live.
    |
    */

    'free_trial_minutes' => (int) env('FREE_TRIAL_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Free Business Limit
    |--------------------------------------------------------------------------
    |
    | Maximum number of enterprises a non-premium business user may create.
    |
    */

    'free_business_limit' => (int) env('FREE_BUSINESS_LIMIT', 1),

];
