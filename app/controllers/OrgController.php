<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// The class must extend the base Controller class
class OrgController extends Controller
{
    // The model will be loaded by the parent constructor into the framework's registry
    public function __construct() {
        parent::__construct();
        // Load the model via the framework's invoker for correct instantiation
        $this->call->model('OrgModel');
    }

    /**
     * Retrieves dashboard statistics and activity from the model 
     * and passes them to the dashboard view.
     */
    public function dashboard()
    {
        // 1. Fetch all dashboard data from the loaded model instance
        $stats = $this->OrgModel->get_dashboard_stats();
        
        // Mock activity data (as actual table isn't built yet)
        $recent_activity = [];
        
        // 2. Load the view using the standard framework method
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
        // Load helper for user ID retrieval and get reviewer list
        $this->call->helper('common');
        $reviewers = $this->OrgModel->getPotentialReviewers();
        
        // Fetch recent uploads for the view sidebar (if needed)
        $user_id = get_user_id();
        $recent_uploads = $this->OrgModel->getRecentUserUploads($user_id);

        $this->call->view('org/documents/upload', [
            'reviewers' => $reviewers,
            'recent_uploads' => $recent_uploads
        ]);
    }
    
    public function documents_store() {
        // FIX: Load libraries/helpers individually for robust dependency mapping
        $this->call->library('Form_validation'); 
        $this->call->library('Upload'); 
        // Load directory_helper for is_dir_usable and common_helper for flash messages
        $this->call->helper(['common', 'directory']); 
        
        $file_input_name = 'document_file';
        // Use ROOT_DIR to create an absolute, reliable file system path for file operations
        $upload_dir = ROOT_DIR . 'public/uploads/documents/';
        $user_id = get_user_id(); // Assuming this helper returns the logged-in user's ID

        // 0. Preliminary Check: Ensure the upload directory exists and is writable
        if (!is_dir_usable($upload_dir)) {
            set_flash_alert('danger', 'System error: Upload directory not found or is not writable. Check folder permissions (e.g., chmod 775) on: ' . $upload_dir);
            redirect(BASE_URL . '/org/documents/upload');
            return;
        }

        // 1. Validation (uses $this->form_validation after explicit load)
        $this->form_validation->name('title|Document Title')->required()->max_length(255);
        $this->form_validation->name('type|Document Type')->required();
        
        if (empty($_FILES[$file_input_name]['name'])) {
            $this->form_validation->set_error_message('', '%s is required', 'Document File');
        }

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->errors();
            set_flash_alert('danger', $errors);
            redirect(BASE_URL . '/org/documents/upload');
            return;
        }

        // 2. File Upload Logic (uses $this->upload after explicit load)
        $this->upload->set_dir($upload_dir);
        $this->upload->allowed_extensions(['pdf', 'docx', 'xlsx', 'jpg', 'png']);
        $this->upload->max_size(25); // 25 MB (As per guidelines in view)
        $this->upload->encrypt_name(); 

        if (!$this->upload->do_upload(FALSE)) {
            $errors = implode(' ', $this->upload->get_errors());
            set_flash_alert('danger', 'File upload failed: ' . $errors);
            redirect(BASE_URL . '/org/documents/upload');
            return;
        }
        
        $uploaded_file_name = $this->upload->get_filename();

        // 3. Database Insertion
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
            // Note: In a real app, delete file if DB insertion fails
            set_flash_alert('danger', 'Document uploaded but failed to save record in database.');
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