<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// =============================================
// Import Excel — TikTok OrderSKUList
// =============================================
$routes->get('/import', 'Import::index');
$routes->post('/import/process', 'Import::process');

// =============================================
// Withdrawal Dashboard (CEO)
// =============================================
$routes->get('/withdrawal', 'Withdrawal::index');
$routes->get('/withdrawal/data', 'Withdrawal::data');
$routes->post('/withdrawal/tarik', 'Withdrawal::tarik');
$routes->post('/withdrawal/reset', 'Withdrawal::reset');

// Temporary route for bulk resetting
$routes->get('/temp-reset-semua', 'Withdrawal::tempResetSemua');

// =============================================
// Finance & HRD Modules (Kas, Absensi, Gaji)
// =============================================
$routes->group('finance', function ($routes) {
    $routes->get('pengeluaran', 'Finance::index');
    $routes->post('store', 'Finance::store');
});

$routes->group('hrd', function ($routes) {
    // Karyawan
    $routes->get('karyawan', 'HRD::karyawan');
    $routes->post('store-karyawan', 'HRD::storeKaryawan');
    $routes->post('delete-karyawan/(:num)', 'HRD::deleteKaryawan/$1');
    
    // Absensi
    $routes->get('absensi', 'HRD::absensi');
    $routes->post('store-absensi', 'HRD::storeAbsensi');
    $routes->post('delete-absensi/(:num)', 'HRD::deleteAbsensi/$1');
    
    // Penggajian
    $routes->get('penggajian', 'HRD::penggajian');
    $routes->post('generate-gaji', 'HRD::generateGaji');
    $routes->post('bayar-gaji/(:num)', 'HRD::bayarGaji/$1');
});
