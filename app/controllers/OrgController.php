<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// The class must extend the base Controller class
class OrgController extends Controller
{
    public function __construct() {
        parent::__construct();
        $this->call->model('OrgModel');
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
        $this->call->helper('common');
        
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
        $this->call->helper(['common', 'directory']); 
        
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
            // PDF
            'application/pdf', 
            // DOCX, XLSX (Microsoft Office Open XML formats)
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // Legacy Microsoft Office formats (good for compatibility)
            'application/msword', 
            'application/vnd.ms-excel',
            // Image formats (JPG, PNG)
            'image/jpeg', 
            'image/pjpeg', // Alternate JPEG MIME type
            'image/png', 
        ];

        $this->upload->file = $uploaded_file; 
        $this->upload->set_dir($upload_dir);
        
        // Use both extension and the comprehensive MIME list
        $this->upload->allowed_extensions(['pdf', 'docx', 'xlsx', 'jpg', 'png']);
        $this->upload->allowed_mimes($allowed_mimes); // <-- This prevents the MIME Type error
        
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
            'user_id'       => $user_id,
        ];

        $new_doc_id = $this->OrgModel->insertDocument($data);

        if ($new_doc_id) {
            set_flash_alert('success', 'Document "' . htmlspecialchars($data['title']) . '" uploaded successfully and submitted for review.');
            redirect(BASE_URL . '/org/documents/pending');
        } else {
            set_flash_alert('danger', 'Document uploaded but failed to save record in database. Please contact IT.');
            redirect(BASE_URL . '/org/documents/upload');
        }
    }

    public function documents_pending() { 
        $docs = $this->OrgModel->getPendingDocuments(); 
        $this->call->view('org/documents/pending', compact('docs')); 
    }
    public function documents_approved(){ 
        $docs = $this->OrgModel->getApprovedDocuments(); 
        $this->call->view('org/documents/approved', compact('docs')); 
    }
    public function documents_rejected(){ 
        $docs = $this->OrgModel->getRejectedDocuments(); 
        $this->call->view('org/documents/rejected', compact('docs')); 
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