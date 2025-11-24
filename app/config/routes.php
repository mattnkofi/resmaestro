<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

/// Removing lines 19-23 (the entire Review section)
$router->get('/', 'Welcome::index');
$router->match('/login', 'Auth::login', ['GET', 'POST']);
$router->match('/register', 'Auth::register', ['GET', 'POST']);
$router->get('/verify-email', 'Auth::verify_email');
$router->get('/test-email', 'Auth::test_email');
$router->get('/logout', 'Auth::logout');

/*
|--------------------------------------------------------------------------
| Organization Routes (Sidebar Pages)
|--------------------------------------------------------------------------
*/

// Dashboard
$router->get('/org/dashboard', 'OrgController::dashboard');
$router->get('/org/sidebar', 'OrgController::sidebar');

// Documents
$router->get('/org/documents/all', 'OrgController::documents_all');
// Upload
$router->get('/org/documents/upload', 'OrgController::documents_upload');
$router->post('/org/documents/store', 'OrgController::documents_store');

// Document Status Update Route (The POST handler for Approve/Reject)
$router->post('/org/documents/update_status', 'OrgController::update_document_status');

$router->get('/org/documents/approved', 'OrgController::documents_approved');
$router->get('/org/documents/rejected', 'OrgController::documents_rejected');
$router->get('/org/documents/department_review', 'OrgController::documents_department_review');

// Document Delete (POST handler for permanent deletion)
$router->post('/org/documents/delete', 'OrgController::documents_delete'); 

// Members
$router->get('/org/members/list', 'OrgController::members_list');
$router->get('/org/members/add', 'OrgController::members_add');
$router->post('/org/members/store', 'OrgController::members_store');

$router->post('/org/members/update', 'OrgController::members_update');
$router->post('/org/members/delete', 'OrgController::members_delete');

// Departments & Roles
$router->get('/org/departments', 'OrgController::departments');
$router->post('/org/departments/store', 'OrgController::departments_store'); 

// --- Department CRUD Endpoints ---
$router->get('/org/departments/members/(:num)', 'OrgController::fetch_dept_members/$1');
$router->post('/org/departments/update', 'OrgController::departments_update');
$router->post('/org/departments/delete', 'OrgController::departments_delete');
// --- End Department CRUD Endpoints ---

// Settings & Profile
$router->get('/org/settings', 'OrgController::settings');
$router->get('/org/profile', 'OrgController::profile');
$router->post('/org/profile/update', 'OrgController::profile_update');
$router->post('/org/profile/leave-department', 'OrgController::leave_department');