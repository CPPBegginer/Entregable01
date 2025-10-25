<?php
// Sistema de rutas para el grifo

// Página de inicio
$router->add('GET', '/', 'HomeController@index');

// Rutas de autenticación
$router->add('GET', '/login', 'AuthController@showLogin');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/register', 'AuthController@showRegister');
$router->add('POST', '/register', 'AuthController@register');
$router->add('POST', '/logout', 'AuthController@logout');

// Rutas del despachador
$router->add('GET', '/attendant/dashboard', 'AttendantController@dashboard');
$router->add('GET', '/attendant/new-sale', 'AttendantController@newSale');
$router->add('POST', '/attendant/process-sale', 'AttendantController@processSale');
$router->add('GET', '/attendant/sales', 'AttendantController@sales');
$router->add('GET', '/attendant/customers', 'AttendantController@customers');
$router->add('GET', '/attendant/customers/create', 'AttendantController@createCustomer');
$router->add('POST', '/attendant/store-customer', 'AttendantController@storeCustomer');

// Rutas del administrador
$router->add('GET', '/admin/dashboard', 'AdminController@dashboard');
$router->add('GET', '/admin/users', 'AdminController@users');
$router->add('GET', '/admin/inventory', 'AdminController@inventory');
$router->add('POST', '/admin/update-price', 'AdminController@updatePrice');
$router->add('GET', '/admin/sales', 'AdminController@sales');
$router->add('GET', '/admin/customers', 'AdminController@customers');
$router->add('GET', '/admin/suppliers', 'AdminController@suppliers');
$router->add('GET', '/admin/supplies', 'AdminController@supplies');

// Rutas de la API (para búsquedas rápidas)
$router->add('GET', '/api/customers/search', 'ApiController@searchCustomers');