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
        $docs = $this->OrgModel->getAllDocuments(); 
        $this->call->view('org/documents/all', compact('docs')); 
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
            'tags'          => $this->io->post('tags'),
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
        $doc_title = $this->io->post('document_title') ?? 'Document'; // Optional title for messages

        // FIX: Use direct session check to reliably check if the user is logged in
        $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

        if (!$is_authenticated || ((int)$doc_id) <= 0 || empty($new_status)) {
            set_flash_alert('danger', 'Invalid action or missing document data. Please log in.');
            redirect(BASE_URL . '/org/documents/pending');
            return; // CRITICAL: Terminate script
        }

        $data_to_update = [
            'status' => $new_status,
            'reviewer_id' => get_user_id(), // Set reviewer for any status change
            'approved_at' => NULL,
            'rejected_at' => NULL,
            'deleted_at' => NULL
        ];
        
        // FIX: Handle specific timestamps and clear others for clarity
        if ($new_status === 'Approved') {
            $data_to_update['approved_at'] = date('Y-m-d H:i:s');
        } elseif ($new_status === 'Rejected') {
            $data_to_update['rejected_at'] = date('Y-m-d H:i:s');
        } elseif ($new_status === 'Archived') {
            $data_to_update['deleted_at'] = date('Y-m-d H:i:s');
        }
        
        // 2. Call Model to update. Returns row count (0 or 1+) or FALSE on error.
        $success_indicator = $this->OrgModel->updateDocument((int)$doc_id, $data_to_update);
        
        // 3. Handle response and redirect
        // FIX: Check for !== FALSE (accept 0 rows changed) and ensure termination
        if ($success_indicator !== FALSE) {
            $message = "Status for '{$doc_title}' successfully changed to {$new_status}.";
            set_flash_alert('success', $message);
            
            // Redirect to the list corresponding to the new status 
            $redirect_segment = strtolower(str_replace(' ', '', $new_status));
            redirect(BASE_URL . '/org/documents/' . $redirect_segment);
            return; // CRITICAL: Terminate script
        } else {
            set_flash_alert('danger', 'Failed to update document status in the database.');
            redirect(BASE_URL . '/org/documents/pending'); 
            return; // CRITICAL: Terminate script
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
        $doc_id = $this->io->post('document_id');
        
        // FIX: Use robust session check
        $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

        if (!$is_authenticated || ((int)$doc_id) <= 0) {
            set_flash_alert('danger', 'Invalid action or missing document data. Please log in.');
            redirect(BASE_URL . '/org/documents/rejected');
            return;
        }

        // Load validation library 
        $this->call->library('Form_validation'); 
        
        $title = $this->io->post('title');
        
        // Simple validation check (should be extended)
        if (empty($title)) {
             set_flash_alert('danger', 'Document Title is required.');
             redirect(BASE_URL . '/org/documents/edit/' . $doc_id);
             return;
        }
        
        // Data for update/resubmission
        $data = [
            'title'         => $title,
            'type'          => $this->io->post('type'),
            'status'        => 'Pending Review', // CRITICAL: Reset status
            'description'   => $this->io->post('description'),
            'tags'          => $this->io->post('tags'),
            'reviewer_id'   => $this->io->post('reviewer') ?: NULL, 
            'updated_at'    => date('Y-m-d H:i:s'),
            'rejected_at'   => NULL, // Clear rejection timestamp
            'approved_at'   => NULL, // Clear approval timestamp
            'deleted_at'    => NULL  // Clear archived status
        ];
        
        // NOTE: File upload/update logic is complex and omitted here, assuming the existing file is kept.

        $success_indicator = $this->OrgModel->updateDocument((int)$doc_id, $data);

        if ($success_indicator !== FALSE) {
            set_flash_alert('success', 'Document "' . htmlspecialchars($title) . '" successfully resubmitted for review.');
            redirect(BASE_URL . '/org/documents/pending');
            return;
        } else {
            set_flash_alert('danger', 'Resubmission failed to update record in database. Please try again.');
            redirect(BASE_URL . '/org/documents/edit/' . $doc_id);
            return;
        }
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
        $docs = $this->OrgModel->getArchivedDocuments(); 
        $this->call->view('org/documents/archived', compact('docs')); 
    }

    // Review & Workflow
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