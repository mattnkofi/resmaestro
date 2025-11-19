<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// The class must extend the base Controller class
class OrgController extends Controller
{
    public function __construct() {
        parent::__construct();
        $this->call->model('OrgModel');
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

    // ----------------------------------------------------------------------
    //  DOCUMENT LISTING
    // ----------------------------------------------------------------------
    public function documents_all() { 
        $docs = $this->OrgModel->getAllDocuments(); 
        $this->call->view('org/documents/all', compact('docs')); 
    }

    public function documents_pending() { 
        // 1. Get filter input from URL
        $q = $this->io->get('q'); 
        $type = $this->io->get('type'); 
        
        // 2. Pass filters to the model
        $docs = $this->OrgModel->getPendingDocuments($q, $type); 
        
        // 3. Pass results AND filters back to the view
        $this->call->view('org/documents/pending', [
            'docs' => $docs,
            'q' => $q, 
            'type' => $type
        ]); 
    }

    public function documents_approved() { 
        $q = $this->io->get('q'); 
        $type = $this->io->get('type'); 
        $approved_docs = $this->OrgModel->getApprovedDocuments($q, $type); 
        
        $this->call->view('org/documents/approved', [
            'approved_docs' => $approved_docs,
            'q' => $q, 
            'type' => $type
        ]); 
    }
    
    public function documents_rejected(){ 
        $docs = $this->OrgModel->getRejectedDocuments(); 
        $reviewers = $this->OrgModel->getPotentialReviewers();
        
        $this->call->view('org/documents/rejected', [
            'docs' => $docs, 
            'reviewers' => $reviewers
        ]); 
    }
    public function documents_archived(){ 
        $docs = $this->OrgModel->getArchivedDocuments(); 
        $this->call->view('org/documents/archived', compact('docs')); 
    }


    // ----------------------------------------------------------------------
    //  DOCUMENT CRUD
    // ----------------------------------------------------------------------

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
        $this->call->library('Form_validation'); 
        $this->call->library('Upload'); 
        $this->call->helper(['directory']); 
        
        $file_input_name = 'document_file';
        $uploaded_file = $_FILES[$file_input_name] ?? null;
        $upload_dir = ROOT_DIR . 'public/uploads/documents/'; 
        $user_id = get_user_id(); 

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

        $data = [
            'title'         => $this->io->post('title'),
            'type'          => ucfirst($this->io->post('type')),
            'status'        => 'Pending Review', 
            'description'   => $this->io->post('description'),
            'tags'          => $this->io->post('tags') ?: '',
            'reviewer_id'   => $this->io->post('reviewer') ?: NULL, 
            'file_name'     => $uploaded_file_name, 
            'user_id'       => get_user_id(),
            'created_at'    => date('Y-m-d H:i:s'), 
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

    // ... (Validation and Authentication checks remain the same) ...
    $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    $allowed_statuses = ['Approved', 'Rejected', 'Archived', 'Pending Review'];
    
    if (!$is_authenticated || !is_numeric($doc_id) || ((int)$doc_id) <= 0 || !in_array($new_status, $allowed_statuses)) {
        set_flash_alert('danger', 'Invalid action or missing document data.');
        redirect(BASE_URL . '/org/documents/all'); 
        return;
    }
    
    // 2. Prepare Data and Timestamps
    $current_datetime = date('Y-m-d H:i:s');
    
    $data_to_update = [
        'status' => $new_status,
        'reviewer_id' => get_user_id(),
        // CRITICAL FIX 1: REMOVE created_at from the UPDATE payload.
        // Initialize all date fields to NULL (as they allow NULL, except updated_at is handled by DB)
        'approved_at' => NULL, 
        'rejected_at' => NULL,
        'deleted_at' => NULL,
    ];

    switch ($new_status) {
        case 'Approved':
            $data_to_update['approved_at'] = $current_datetime;
            break;
        case 'Rejected':
            $data_to_update['rejected_at'] = $current_datetime;
            break;
        case 'Archived':
            $data_to_update['deleted_at'] = $current_datetime;
            break;
    }
    
    // 3. Call Model to update.
    $success_indicator = $this->OrgModel->updateDocument((int)$doc_id, $data_to_update);
    
    // 4. Handle response and redirect
    if ($success_indicator !== FALSE) {
        
        $redirect_segment = strtolower(str_replace(' ', '', $new_status));
        $redirect_segment = $redirect_segment === 'pendingreview' ? 'pending' : $redirect_segment;
        $destination_url = BASE_URL . '/org/documents/' . $redirect_segment;

        $message = "Status for '{$doc_title}' successfully changed to {$new_status}.";
        set_flash_alert('success', $message);
        
        header('Location: ' . $destination_url);
        exit(); 
    } else {
        set_flash_alert('danger', 'Failed to update document status in the database.');
        redirect(BASE_URL . '/org/documents/pending'); 
        return;
    }
}

    // ----------------------------------------------------------------------
    //  RESUBMISSION LOGIC (Rejected Documents)
    // ----------------------------------------------------------------------

    /**
     * Displays the edit/resubmit form for a specific document.
     * @param int $doc_id
     */
    public function documents_edit($doc_id) {
        // FIX: Use robust session check and input validation
        if (!is_numeric($doc_id) || ((int)$doc_id) <= 0 || !(isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true)) {
            set_flash_alert('danger', 'Invalid document ID or please log in.');
            redirect(BASE_URL . '/org/documents/rejected');
            return;
        }
        
        // Fetch document details including the original submitter 
        $doc = $this->OrgModel->getDocumentById((int)$doc_id); 
        
        if (empty($doc)) {
            set_flash_alert('danger', 'Document not found.');
            redirect(BASE_URL . '/org/documents/rejected');
            return;
        }

        // NOTE: The model uses raw() and returns an array, so we must check array access.
        $doc_user_id = $doc['user_id'] ?? null;
        
        // Only the original submitter should be able to edit/resubmit
        if ($doc_user_id !== get_user_id()) {
            set_flash_alert('danger', 'You do not have permission to edit this document.');
            redirect(BASE_URL . '/org/dashboard');
            return;
        }
        
        $reviewers = $this->OrgModel->getPotentialReviewers();
        
        $this->call->view('org/documents/edit', [ 
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
        
        // 1. Initial Checks (Authentication and ID validation)
        if (!is_numeric($original_doc_id) || ((int)$original_doc_id) <= 0 || !(isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true)) {
             set_flash_alert('danger', 'Invalid document ID or please log in.');
             redirect(BASE_URL . '/org/documents/rejected');
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
            redirect(BASE_URL . '/org/documents/rejected'); 
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
            redirect(BASE_URL . '/org/documents/rejected'); 
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
            redirect(BASE_URL . '/org/documents/rejected'); 
            return;
        }
        
        $uploaded_file_name = $this->upload->get_filename();
        $new_doc_title = $this->io->post('title');

        // 5. Database Insertion (New Record - Resubmission)
        $new_data = [
            'title'         => $new_doc_title,
            'type'          => ucfirst($this->io->post('type')),
            'status'        => 'Pending Review', // New document status
            'description'   => $this->io->post('description'),
            'tags'          => $this->io->post('tags') ?: '', // Safely retrieve tags
            'reviewer_id'   => $this->io->post('reviewer') ?: NULL, 
            'file_name'     => $uploaded_file_name, 
            'user_id'       => $user_id,
            'created_at'    => date('Y-m-d H:i:s'), // Add explicit timestamp
        ];

        $new_doc_id = $this->OrgModel->insertDocument($new_data);

        if (!$new_doc_id) {
            set_flash_alert('danger', 'New document uploaded but failed to save record in database. Please contact IT.');
            redirect(BASE_URL . '/org/documents/rejected'); 
            return;
        }
        
        // 6. Success
        set_flash_alert('success', 'Document "' . htmlspecialchars($new_data['title']) . '" successfully **resubmitted** for review.');
        redirect(BASE_URL . '/org/documents/pending');
        return;
    }


    // ----------------------------------------------------------------------
    //  Review & Workflow
    // ----------------------------------------------------------------------
    public function review_queue() { 
        $reviews = $this->OrgModel->getPendingReviews(); 
        $this->call->view('org/review/queue', compact('reviews')); 
    }
    public function review_history() { 
        $reviews = $this->OrgModel->getReviewHistory(); 
        $this->call->view('org/review/history', compact('reviews')); 
    }
    public function review_comments(){ 
        $comments = $this->OrgModel->getComments(); 
        $this->call->view('org/review/comments', compact('comments')); 
    }

    // Organization
    public function members_list() { 
        $members = $this->OrgModel->getMembers(); 
        $this->call->view('org/members/list', compact('members')); 
    }
    public function members_add() { 
        $this->call->view('org/members/add'); 
    }
    public function departments() { 
        $depts = $this->OrgModel->getDepartments(); 
        $this->call->view('org/departments', compact('depts')); 
    }
    public function roles() { 
        $roles = $this->OrgModel->getRoles(); 
        $this->call->view('org/roles', compact('roles')); 
    }

    // Reports
    public function reports_overview() { 
        $this->call->view('org/reports/overview'); 
    }
    public function reports_documents() { 
        $this->call->view('org/reports/documents'); 
    }
    public function reports_reviewers() { 
        $this->call->view('org/reports/reviewers'); 
    }
    public function reports_storage() { 
        $this->call->view('org/reports/storage'); 
    }

    // Settings/Profile
    public function settings() { 
        $this->call->view('org/settings'); 
    }
    public function profile() { 
        $this->call->view('org/profile'); 
    }
}