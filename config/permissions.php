<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission Modules
    |--------------------------------------------------------------------------
    |
    | Every module listed here gets a permission generated for each action
    | below (module.action), e.g. "bookings.view", "bookings.create".
    |
    */

    'modules' => [
        'dashboard' => 'Dashboard',
        'reports' => 'Reports',
        'bookings' => 'Bookings',
        'pricing' => 'Pricing',
        'payments' => 'Payments',
        'vehicles' => 'Vehicles',
        'drivers' => 'Drivers',
        'customers' => 'Customers',
        'content' => 'Content',
        'blog' => 'Blog',
        'settings' => 'Settings',
        'roles' => 'Roles & Permissions',
        'languages' => 'Languages',
        'currencies' => 'Currencies',
        'locations' => 'Locations',
        'routes' => 'Popular Routes',
        'areas' => 'Service Areas',
        'reviews' => 'Reviews',
        'messages' => 'Contact Messages',
        'support' => 'Support Tickets',
        'coupons' => 'Coupons',
        'promotions' => 'Promotions',
        'system' => 'System Tools',
    ],

    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'export' => 'Export',
    ],

];
