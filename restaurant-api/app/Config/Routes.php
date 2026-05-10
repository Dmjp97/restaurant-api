<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ─────────────────────────────────────────────
//  API v1 — Public routes (no auth required)
// ─────────────────────────────────────────────
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], function ($routes) {

    // Auth
    $routes->post('auth/login',   'AuthController::login');
    $routes->post('auth/refresh', 'AuthController::refresh');

    // ─────────────────────────────────────────────
    //  API v1 — Protected routes (JWT required)
    // ─────────────────────────────────────────────
    $routes->group('', ['filter' => 'jwt'], function ($routes) {

        // Auth
        $routes->post('auth/logout', 'AuthController::logout');
        $routes->get('auth/me',      'AuthController::me');

        // ── Tenants (superadmin only) ──
        $routes->group('tenants', ['filter' => 'role:superadmin'], function ($routes) {
            $routes->get('',          'TenantController::index');
            $routes->post('',         'TenantController::create');
            $routes->get('(:num)',    'TenantController::show/$1');
            $routes->put('(:num)',    'TenantController::update/$1');
            $routes->delete('(:num)', 'TenantController::delete/$1');
        });

        // ── Products ──
        $routes->group('products', function ($routes) {
            $routes->get('',          'ProductController::index');
            $routes->get('(:num)',    'ProductController::show/$1');
            $routes->post('',         'ProductController::create',  ['filter' => 'role:superadmin,manager']);
            $routes->put('(:num)',    'ProductController::update/$1', ['filter' => 'role:superadmin,manager']);
            $routes->delete('(:num)', 'ProductController::delete/$1', ['filter' => 'role:superadmin,manager']);
        });

        // ── Orders ──
        $routes->group('orders', function ($routes) {
            $routes->get('',              'OrderController::index');
            $routes->get('(:num)',        'OrderController::show/$1');
            $routes->get('(:num)/timeline', 'OrderController::timeline/$1');
            $routes->post('',             'OrderController::create');
            $routes->put('(:num)/status', 'OrderController::updateStatus/$1');
            $routes->delete('(:num)',     'OrderController::cancel/$1');
        });

        // ── Reports (manager & superadmin) ──
        $routes->group('reports', ['filter' => 'role:superadmin,manager'], function ($routes) {
            $routes->get('sales',        'ReportController::sales');
            $routes->get('top-products', 'ReportController::topProducts');
            $routes->get('orders-by-status', 'ReportController::ordersByStatus');
            $routes->get('revenue-by-tenant', 'ReportController::revenueByTenant');
        });

        // ── Users ──
        $routes->group('users', ['filter' => 'role:superadmin,manager'], function ($routes) {
            $routes->get('',          'UserController::index');
            $routes->post('',         'UserController::create');
            $routes->get('(:num)',    'UserController::show/$1');
            $routes->put('(:num)',    'UserController::update/$1');
            $routes->delete('(:num)', 'UserController::delete/$1');
        });
    });
});
