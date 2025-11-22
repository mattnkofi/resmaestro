<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Default Routes
|--------------------------------------------------------------------------
*/
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

// Document Status Update Route (The POST handler for Approve/Reject/Archive)
$router->post('/org/documents/update_status', 'OrgController::update_document_status');

$router->get('/org/documents/pending', 'OrgController::documents_pending');

$router->get('/org/documents/approved', 'OrgController::documents_approved');

$router->get('/org/documents/rejected', 'OrgController::documents_rejected');
$router->get('/org/documents/edit/(:num)', 'OrgController::documents_edit/$1');
$router->post('/org/documents/resubmit', 'OrgController::documents_resubmit');

$router->get('/org/documents/archived', 'OrgController::documents_archived');




/// Review
$router->get('/org/review/queue', 'OrgController::review_queue');
$router->get('/org/review/history', 'OrgController::review_history');
$router->get('/org/review/comments/(:num)', 'OrgController::review_comments/$1'); 
$router->get('/org/review/comments', 'OrgController::review_comments');
$router->get('/org/review/fetch_comments/(:num)', 'OrgController::fetch_comments_json/$1');
$router->post('/org/review/add_comment', 'OrgController::review_add_comment');




// Members
$router->get('/org/members/list', 'OrgController::members_list');
$router->get('/org/members/add', 'OrgController::members_add');
$router->post('/org/members/store', 'OrgController::members_store');

$router->post('/org/members/update', 'OrgController@members_update');
$router->post('/org/members/delete', 'OrgController@members_delete');

// Departments & Roles
$router->get('/org/departments', 'OrgController::departments');
$router->post('/org/departments/store', 'OrgController::departments_store'); // <-- NEW ROUTE
$router->get('/org/roles', 'OrgController::roles');

// --- Department CRUD Endpoints ---
$router->get('/org/departments/members/(:num)', 'OrgController::fetch_dept_members/$1');
$router->post('/org/departments/update', 'OrgController::departments_update');
$router->post('/org/departments/delete', 'OrgController::departments_delete');
$router->post('/api/store_delete_code', 'OrgController::store_delete_code');
// --- End Department CRUD Endpoints ---


// Reports
$router->get('/org/reports/overview', 'OrgController::reports_overview');
$router->get('/org/reports/documents', 'OrgController::reports_documents');
$router->get('/org/reports/reviewers', 'OrgController::reports_reviewers');
$router->get('/org/reports/storage', 'OrgController::reports_storage');

// Settings & Profile
$router->get('/org/settings', 'OrgController::settings');
$router->get('/org/profile', 'OrgController::profile');