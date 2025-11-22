<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// The class must extend the base Controller class
class OrgController extends Controller
{
    public function __construct() {
        parent::__construct();
        $this->call->model('OrgModel');
        
        // 1. Attempt to load the three most likely helpers using array syntax
        $this->call->helper(['common', 'auth', 'session']); 
    }

    public function dashboard()
    {
        $stats = $this->OrgModel->get_dashboard_stats();
        $recent_activity = [];
        $this->call->view('org/dashboard', [
            'stats' => $stats,
            'recent_activity' => $recent_activity
        ]);
    }

    public function sidebar() { 
        $this->call->view('org/sidebar'); 
    }

    // Documents
    public function documents_all() { 
    // NEW: Get filters from URL
    $q = $this->io->get('q');
    $status = $this->io->get('status');
    
    // Pass filters to the Model
    $docs = $this->OrgModel->getAllDocuments($q, $status); 
    
    // Pass results AND filters back to the view
    $this->call->view('org/documents/all', [
        'docs' => $docs,
        'q' => $q,
        'status' => $status
    ]); 
}

    public function documents_upload() {
        
        $reviewers = $this->OrgModel->getPotentialReviewers();
        $user_id = get_user_id();
        $recent_uploads = $this->OrgModel->getRecentUserUploads($user_id);

        $this->call->view('org/documents/upload', [
            'reviewers' => $reviewers,
            'recent_uploads' => $recent_uploads
        ]);
    }
    
    public function documents_store() {

        ini_set('upload_max_filesize', '64M');
        ini_set('post_max_size', '64M');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        // Dependencies must be loaded explicitly for non-autoloaded components
        $this->call->library('Form_validation'); 
        $this->call->library('Upload'); 
        $this->call->helper(['directory']); 
        
        $file_input_name = 'document_file';
        $uploaded_file = $_FILES[$file_input_name] ?? null;
        $upload_dir = ROOT_DIR . 'public/uploads/documents/'; 
        $user_id = get_user_id(); 

        // 0. Preliminary Checks (Folder and Validation Setup)
        if (!is_dir_usable($upload_dir)) {
            set_flash_alert('danger', 'System error: Upload directory not found or is not writable. Check folder permissions.');
            redirect(BASE_URL . '/org/documents/upload');
            return;
        }

        $this->form_validation->name('title|Document Title')->required()->max_length(255);
        $this->form_validation->name('type|Document Type')->required();
        
        if (empty($uploaded_file) || $uploaded_file['error'] === UPLOAD_ERR_NO_FILE) {
             $this->form_validation->set_error_message('', '%s is required', 'Document File');
        } elseif (isset($uploaded_file['error']) && $uploaded_file['error'] !== UPLOAD_ERR_OK) {
             $this->form_validation->set_error_message('', 'File upload failed with error code: ' . $uploaded_file['error']);
        }
        
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->errors();
            set_flash_alert('danger', $errors);
            redirect(BASE_URL . '/org/documents/upload');
            return;
        }

        // 1. File Upload Execution
        $allowed_mimes = [
            'application/pdf', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword', 
            'application/vnd.ms-excel',
            'image/jpeg', 
            'image/pjpeg', 
            'image/png', 
        ];

        $this->upload->file = $uploaded_file; 
        $this->upload->set_dir($upload_dir);
        
        $this->upload->allowed_extensions(['pdf', 'docx', 'xlsx', 'jpg', 'png']);
        $this->upload->allowed_mimes($allowed_mimes); 
        
        $this->upload->max_size(25);
        $this->upload->encrypt_name();

        if (!$this->upload->do_upload(FALSE)) {
            $errors = implode(' ', $this->upload->get_errors());
            set_flash_alert('danger', 'File upload failed: ' . $errors);
            redirect(BASE_URL . '/org/documents/upload');
            return;
        }
        
        $uploaded_file_name = $this->upload->get_filename();

        // 2. Database Insertion
        $data = [
            'title'         => $this->io->post('title'),
            'type'          => $this->io->post('type'),
            'status'        => 'Pending Review', 
            'description'   => $this->io->post('description'),
            'tags'          => $this->io->post('tags') ?: '',
            'reviewer_id'   => $this->io->post('reviewer') ?: NULL, 
            'file_name'     => $uploaded_file_name, 
            'user_id'       => get_user_id(),
        ];

        $new_doc_id = $this->OrgModel->insertDocument($data);

        if ($new_doc_id) {
            set_flash_alert('success', 'Document "' . htmlspecialchars($data['title']) . '" uploaded successfully and submitted for review.');
            redirect(BASE_URL . '/org/documents/pending');
            return;
        } else {
            set_flash_alert('danger', 'Document uploaded but failed to save record in database. Please contact IT.');
            redirect(BASE_URL . '/org/documents/upload');
            return;
        }
    }

    public function update_document_status() {
    // 1. Get POST data
    $doc_id = $this->io->post('document_id');
    $new_status = $this->io->post('new_status');
    $doc_title = $this->io->post('document_title') ?? 'Document';
    
    // Safety check for user ID (though not used in the unarchive case, good practice)
    $user_id = (int)get_user_id();
    $reviewer_id_to_send = ($user_id > 0) ? $user_id : NULL;

    // (Authentication checks omitted for brevity)
    $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

    if (!$is_authenticated || ((int)$doc_id) <= 0 || empty($new_status)) {
        set_flash_alert('danger', 'Invalid session or missing data.');
        redirect(BASE_URL . '/org/documents/all'); 
        return;
    }

    // --- CRITICAL FIX START: Build Minimal Payload (Modified to also handle Unarchive) ---
    $data_to_update = [
        'status' => $new_status,
        
        // Always clear all timestamps by default when a status update occurs
        'approved_at' => NULL,
        'rejected_at' => NULL,
        'deleted_at' => NULL,
        // Set reviewer ID for 'Approved' or 'Pending Review' (unarchive/new submission)
        'reviewer_id' => ($new_status === 'Approved' || $new_status === 'Pending Review') ? $reviewer_id_to_send : NULL, 
    ];
    
    $current_datetime = date('Y-m-d H:i:s');
    
    // Customize the payload based on the requested status
    if ($new_status === 'Approved') {
        $data_to_update['approved_at'] = $current_datetime;
    } elseif ($new_status === 'Rejected') {
        $data_to_update['rejected_at'] = $current_datetime;
    } elseif ($new_status === 'Archived') {
        $data_to_update['deleted_at'] = $current_datetime;
    } 
    // Note: For Pending Review (unarchive), status dates remain NULL (correct).
    // --- CRITICAL FIX END ---

    // 2. Call Model to update.
    $success_indicator = $this->OrgModel->updateDocument((int)$doc_id, $data_to_update);
    
    // 3. Handle response and redirect
    if ($success_indicator !== FALSE) {
        $message = "Status for '{$doc_title}' successfully changed to {$new_status}.";
        set_flash_alert('success', $message);
        
        $redirect_segment = strtolower(str_replace(' ', '', $new_status));
        $redirect_segment = $redirect_segment === 'pendingreview' ? 'pending' : $redirect_segment;
        $redirect_segment = $redirect_segment === 'archived' ? 'archived' : $redirect_segment;
        // If the new status is Pending Review, redirect to /pending.
        // If the new status is Archived, redirect to /archived.
        // If the new status is anything else (Approved, Rejected), redirect to the corresponding page.

        header('Location: ' . BASE_URL . '/org/documents/' . $redirect_segment);
        exit(); 
    } else {
        set_flash_alert('danger', 'Failed to update document status in the database. Please check DB logs.');
        // Redirect back to the ARCHIVED page on failure for an unarchive attempt.
        redirect(BASE_URL . '/org/documents/archived'); 
        return; 
    }
}

    // ----------------------------------------------------------------------
    //  RESUBMISSION LOGIC
    // ----------------------------------------------------------------------

    /**
     * Displays the edit/resubmit form for a specific document.
     */
    public function documents_edit($doc_id) {
        // FIX: Use robust session check
        $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

        if (!$is_authenticated || ((int)$doc_id) <= 0) {
            set_flash_alert('danger', 'Invalid document ID or please log in.');
            redirect(BASE_URL . '/org/documents/rejected');
            return;
        }
        
        // Fetch document details including the original submitter (via getDocumentById)
        $doc = $this->OrgModel->getDocumentById((int)$doc_id); // Assumes getDocumentById returns an object/array
        
        if (empty($doc)) {
            set_flash_alert('danger', 'Document not found.');
            redirect(BASE_URL . '/org/documents/rejected');
            return;
        }

        // Only the original submitter should be able to edit/resubmit
        // NOTE: $doc is likely an object if fetched by getDocumentById
        if (($doc->user_id ?? $doc['user_id'] ?? null) !== get_user_id()) {
            set_flash_alert('danger', 'You do not have permission to edit this document.');
            redirect(BASE_URL . '/org/dashboard');
            return;
        }
        
        $reviewers = $this->OrgModel->getPotentialReviewers();
        
        $this->call->view('org/documents/edit', [ // User needs to create 'edit.php' view
            'doc' => $doc,
            'reviewers' => $reviewers
        ]);
    }

    /**
     * Handles the POST request to resubmit an edited document.
     */
    public function documents_resubmit() {
        $original_doc_id = $this->io->post('document_id'); // Kept for redirect on validation failure
        $user_id = get_user_id(); 
        
        // 1. Initial Checks (Authentication only)
        $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

        if (!$is_authenticated) {
            set_flash_alert('danger', 'Please log in to submit a document.');
            redirect(BASE_URL . '/login');
            return;
        }

        // 2. Setup and Dependencies (Copied from documents_store)
        ini_set('upload_max_filesize', '64M');
        ini_set('post_max_size', '64M');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        $this->call->library('Form_validation'); 
        $this->call->library('Upload'); 
        $this->call->helper(['directory']); 
        
        $file_input_name = 'document_file';
        $uploaded_file = $_FILES[$file_input_name] ?? null;
        $upload_dir = ROOT_DIR . 'public/uploads/documents/'; 

        // 3. Preliminary Checks (Folder and Validation Setup)
        if (!is_dir_usable($upload_dir)) {
            set_flash_alert('danger', 'System error: Upload directory not found or is not writable. Check folder permissions.');
            redirect(BASE_URL . '/org/documents/edit/' . $original_doc_id);
            return;
        }

        $this->form_validation->name('title|Document Title')->required()->max_length(255);
        $this->form_validation->name('type|Document Type')->required();
        
        // A resubmit must require a new file to be uploaded.
        if (empty($uploaded_file) || $uploaded_file['error'] === UPLOAD_ERR_NO_FILE) {
             $this->form_validation->set_error_message('', '%s is required for resubmission.', 'Document File');
        } elseif (isset($uploaded_file['error']) && $uploaded_file['error'] !== UPLOAD_ERR_OK) {
             $this->form_validation->set_error_message('', 'File upload failed with error code: ' . $uploaded_file['error']);
        }
        
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->errors();
            set_flash_alert('danger', $errors);
            redirect(BASE_URL . '/org/documents/edit/' . $original_doc_id);
            return;
        }

        // 4. File Upload Execution (Copied from documents_store)
        $allowed_mimes = [
            'application/pdf', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword', 
            'application/vnd.ms-excel',
            'image/jpeg', 
            'image/pjpeg', 
            'image/png', 
        ];

        $this->upload->file = $uploaded_file; 
        $this->upload->set_dir($upload_dir);
        
        $this->upload->allowed_extensions(['pdf', 'docx', 'xlsx', 'jpg', 'png']);
        $this->upload->allowed_mimes($allowed_mimes); 
        
        $this->upload->max_size(25);
        $this->upload->encrypt_name();

        if (!$this->upload->do_upload(FALSE)) {
            $errors = implode(' ', $this->upload->get_errors());
            set_flash_alert('danger', 'File upload failed: ' . $errors);
            redirect(BASE_URL . '/org/documents/edit/' . $original_doc_id);
            return;
        }
        
        $uploaded_file_name = $this->upload->get_filename();
        $new_doc_title = $this->io->post('title');

        // 5. Database Insertion (New Record)
        $new_data = [
            'title'         => $new_doc_title,
            'type'          => ucfirst($this->io->post('type')),
            'status'        => 'Pending Review', // New document status
            'description'   => $this->io->post('description'),
            'tags'          => array_key_exists('tags', $_POST) ? $this->io->post('tags') : '', // Safely retrieve tags
            'reviewer_id'   => $this->io->post('reviewer') ?: NULL, 
            'file_name'     => $uploaded_file_name, 
            'user_id'       => $user_id,
        ];

        $new_doc_id = $this->OrgModel->insertDocument($new_data);

        if (!$new_doc_id) {
            // Upload succeeded, but DB insertion failed.
            set_flash_alert('danger', 'New document uploaded but failed to save record in database. Please contact IT.');
            redirect(BASE_URL . '/org/documents/edit/' . $original_doc_id);
            return;
        }

        // 6. Success
        set_flash_alert('success', 'Document "' . htmlspecialchars($new_data['title']) . '" successfully uploaded and submitted for review.');
        redirect(BASE_URL . '/org/documents/pending');
        return;
    }

    public function documents_pending() { 
        // 1. Get filter input from URL
        $q = $this->io->get('q'); 
        $type = $this->io->get('type'); 
        
        // 2. Pass filters to the model
        $docs = $this->OrgModel->getPendingDocuments($q, $type); 
        
        // 3. Pass results AND filters back to the view
        // The view uses $q and $type to retain values in the form fields.
        $this->call->view('org/documents/pending', [
            'docs' => $docs,
            'q' => $q, 
            'type' => $type
        ]); 
    }

    public function documents_approved() { 
        // 1. Get filter input from URL
        $q = $this->io->get('q'); 
        $type = $this->io->get('type'); 
        
        // 2. Call model with filters
        $approved_docs = $this->OrgModel->getApprovedDocuments($q, $type); 
        
        // 3. Pass results AND filters back to the view
        $this->call->view('org/documents/approved', [
            'approved_docs' => $approved_docs,
            'q' => $q, 
            'type' => $type
        ]); 
    }

    public function documents_review_test() {
        // Mock data to ensure the review_detail.php view renders the UI without crashing
        $mock_doc = [
            'id' => 999, 
            'title' => 'MOCK Document for UI Test',
            'type' => 'report',
            'file_name' => 'd1f8fab2bb3799910e8f2da081c7a72fe90f9597.pdf', 
            'status' => 'Pending Review',
            'submitter_fname' => 'Test',
            'submitter_lname' => 'User'
        ];
        
        $this->call->view('org/documents/review_detail', ['doc' => $mock_doc]);
    }
    
    public function documents_rejected(){ 
        $docs = $this->OrgModel->getRejectedDocuments(); 
        // FIX: Fetch reviewers for the resubmit modal on the rejected page
        $reviewers = $this->OrgModel->getPotentialReviewers();
        
        $this->call->view('org/documents/rejected', [
            'docs' => $docs, 
            'reviewers' => $reviewers
        ]); 
    }
    
    public function documents_archived(){ 
    // 1. Retrieve the query parameter
    $q = $this->io->get('q'); 
    
    // 2. Pass the query to the model
    $docs = $this->OrgModel->getArchivedDocuments($q); 
    
    // 3. Pass results AND query back to the view
    $this->call->view('org/documents/archived', [
        'docs' => $docs,
        'q' => $q
    ]);
}

    // Review & Workflow
   public function review_queue() { 
        $q = $this->io->get('q'); 
        $sort = $this->io->get('sort') ?: 'oldest'; 
        
        $reviews = $this->OrgModel->getPendingReviews($q, $sort); 
        
        $this->call->view('org/review/queue', [
            'reviews' => $reviews,
            'q' => $q,
            'sort' => $sort
        ]); 
    }

    public function review_history() { 
        $q = $this->io->get('q'); 
        $status = $this->io->get('status'); 
        
        $reviews = $this->OrgModel->getReviewHistory($q, $status); 
        
        $this->call->view('org/review/history', [
            'reviews' => $reviews,
            'q' => $q, 
            'status' => $status
        ]); 
    }

    public function review_comments($doc_id = null){ 
        if (empty($doc_id)) {
            set_flash_alert('warning', 'Please select a document to view comments.');
            redirect(BASE_URL . '/org/review/queue');
            return;
        }

        $doc = $this->OrgModel->getDocumentById((int)$doc_id);
        
        if (empty($doc)) {
            set_flash_alert('danger', 'Document not found.');
            redirect(BASE_URL . '/org/review/queue');
            return;
        }

        $comments = $this->OrgModel->getReviewComments((int)$doc_id);
        
        $this->call->view('org/review/comments', [
            'comments' => $comments,
            'doc' => $doc 
        ]); 
    }

    public function review_add_comment() {
        $doc_id = $this->io->post('document_id');
        $comment_text = trim($this->io->post('comment_text'));
        $user_id = get_user_id(); 

        $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

        if (!$is_authenticated || ((int)$doc_id) <= 0 || empty($comment_text)) {
            set_flash_alert('danger', 'Invalid request or missing data.');
            redirect(BASE_URL . '/org/review/queue');
            return;
        }
        
        $data = [
            'document_id' => (int)$doc_id,
            'user_id'     => $user_id, 
            'comment'     => $comment_text,
            'created_at'  => date('Y-m-d H:i:s')
        ];

        $new_comment_id = $this->OrgModel->insertComment($data);

        if ($new_comment_id) {
            set_flash_alert('success', 'Comment added successfully.');
        } else {
            set_flash_alert('danger', 'Failed to add comment to the database.');
        }
        redirect(BASE_URL . '/org/review/comments/' . $doc_id);
    }
    
    // ----------------------------------------------------------------------
    // ORGANIZATION: MEMBERS
    // ----------------------------------------------------------------------

    public function members_list() { 
        $q = $this->io->get('q');
        
        $members = $this->OrgModel->getMembers($q); 
        
        $this->call->view('org/members/list', [
            'members' => $members,
            'q' => $q
        ]); 
    }
    
    public function members_add() { 
        $departments = $this->OrgModel->getDepartments();
        $roles = $this->OrgModel->getRoles();
        
        $this->call->view('org/members/add', [
            'departments' => $departments,
            'roles' => $roles
        ]); 
    }

    public function members_store() {
        $this->call->library('Form_validation');
        $this->call->library('lauth'); 

        $this->form_validation->name('fname|First Name')->required()->max_length(50);
        $this->form_validation->name('lname|Last Name')->required()->max_length(50);
        $this->form_validation->name('email|Email Address')->required()->valid_email()->is_unique('users.email'); 
        $this->form_validation->name('password|Password')->required()->min_length(8);
        $this->form_validation->name('dept_id|Department')->required()->is_natural_no_zero();
        $this->form_validation->name('role_id|Role')->required()->is_natural_no_zero();
        
        if (!$this->form_validation->run()) {
            set_flash_alert('danger', $this->form_validation->errors());
            redirect(BASE_URL . '/org/members/add');
            return;
        }

        $password = $this->io->post('password');
        
        $data = [
            'fname'       => $this->io->post('fname'),
            'lname'       => $this->io->post('lname'),
            'email'       => $this->io->post('email'),
            'password'    => $this->lauth->hash_password($password), 
            'dept_id'     => (int)$this->io->post('dept_id'),
            'role_id'     => (int)$this->io->post('role_id'),
            'is_verified' => 1, 
            'created_at'  => date('Y-m-d H:i:s')
        ];

        $new_user_id = $this->OrgModel->insertMember($data);

        if ($new_user_id) {
            set_flash_alert('success', 'New member "' . htmlspecialchars($data['fname'] . ' ' . $data['lname']) . '" added successfully.');
            redirect(BASE_URL . '/org/members/list');
        } else {
            set_flash_alert('danger', 'Failed to add member to the database.');
            redirect(BASE_URL . '/org/members/add');
        }
    }
    
    // ----------------------------------------------------------------------
    // ORGANIZATION: DEPARTMENTS & ROLES (Minimal Implementation)
    // ----------------------------------------------------------------------
    
    public function departments() { 
    // CHANGE: Call the new method to get aggregated stats
    $depts = $this->OrgModel->getDepartmentsWithStats(); 
    // NEW: Fetch members without department for the creation form
    $potential_members = $this->OrgModel->getPotentialDepartmentMembers();
    $this->call->view('org/departments', compact('depts', 'potential_members')); 
}

public function departments_store() {
    $this->call->library('Form_validation');
    
    // 1. Set Validation Rules: Name is required, max length 100, and must be unique.
    // (Previous fix for ArgumentCountError)
    $this->form_validation->name('name|Department Name')->required()->max_length(100)->is_unique('departments', 'name', $this->io->post('name')); 
    
    if (!$this->form_validation->run()) {
        set_flash_alert('danger', $this->form_validation->errors());
        redirect(BASE_URL . '/org/departments');
        return;
    }
    
    // FIX APPLIED HERE: Safely retrieve member IDs to prevent Undefined array key warning
    $member_ids = isset($_POST['member_ids']) ? $this->io->post('member_ids') : [];
    $member_ids = is_array($member_ids) ? $member_ids : [$member_ids];
    $member_ids = array_filter($member_ids, 'is_numeric'); // Clean up non-numeric IDs

    // 2. Prepare Data
    $data = [
        'name' => $this->io->post('name'),
    ];

    // 3. Insert Department
    $new_dept_id = $this->OrgModel->insertDepartment($data);

    if ($new_dept_id) {
        // 4. Assign members to the new department
        if (!empty($member_ids)) {
            $this->OrgModel->assignMembersToDepartment($new_dept_id, $member_ids);
        }
        
        $member_count_message = !empty($member_ids) ? ' and ' . count($member_ids) . ' members assigned.' : '.';

        set_flash_alert('success', 'Department "' . htmlspecialchars($data['name']) . '" added successfully' . $member_count_message);
        redirect(BASE_URL . '/org/departments');
    } else {
        set_flash_alert('danger', 'Failed to add department to the database.');
        redirect(BASE_URL . '/org/departments');
    }
}
    
    public function roles() { 
        $roles = $this->OrgModel->getRoles(); 
        $this->call->view('org/roles', compact('roles')); 
    }

    // ----------------------------------------------------------------------
    // REPORTS & SYSTEM (Minimal View Loading)
    // ----------------------------------------------------------------------

    public function reports_overview() { $this->call->view('org/reports/overview'); }
    public function reports_documents() { $this->call->view('org/reports/documents'); }
    public function reports_reviewers() { $this->call->view('org/reports/reviewers'); }
    public function reports_storage() { $this->call->view('org/reports/storage'); }

    public function settings() { $this->call->view('org/settings'); }
    public function profile() { $this->call->view('org/profile'); }
}