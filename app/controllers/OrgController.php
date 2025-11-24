<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// The class must extend the base Controller class
class OrgController extends Controller
{
	public function __construct() {
		parent::__construct();
		$this->call->model('OrgModel');
		
		// Load the email helper here as well
		$this->call->helper(['common', 'auth', 'session', 'email']); 
	}

	public function dashboard()
	{
		$stats = $this->OrgModel->get_dashboard_stats();
		$recent_activity = [];
		
		// Removed notification logic
		
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
		$q = $this->io->get('q');
		$status = $this->io->get('status');
		
		$docs = $this->OrgModel->getAllDocuments($q, $status); 
		
		// Removed notification logic

		$this->call->view('org/documents/all', [
			'docs' => $docs,
			'q' => $q,
			'status' => $status
		]); 
	}

	// REMOVED: public function fetch_archived_documents_json()

	/**
	 * Deletes a document permanently (called from rejected documents view).
	 */
	public function documents_delete() {
		$doc_id = (int)$this->io->post('document_id');
		$doc_title = $this->io->post('document_title') ?? 'Document';

		$is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

		if (!$is_authenticated || $doc_id <= 0) {
			set_flash_alert('danger', 'Invalid request or session expired.');
			redirect(BASE_URL . '/org/documents/rejected'); 
			return;
		}

		$success = $this->OrgModel->deleteDocumentPermanently($doc_id);

		if ($success) {
			set_flash_alert('success', "Document '{$doc_title}' permanently deleted.");
		} else {
			set_flash_alert('danger', "Failed to delete document '{$doc_title}'.");
		}
		
		// Redirect back to the Rejected Documents page
		redirect(BASE_URL . '/org/documents/rejected'); 
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
			'title' 		=> $this->io->post('title'),
			'type' 			=> $this->io->post('type'),
			'status' 		=> 'Pending Review', 
			'description' 	=> $this->io->post('description'),
			'tags' 			=> $this->io->post('tags') ?: '',
			'reviewer_id' 	=> $this->io->post('reviewer') ?: NULL, 
			'file_name' 	=> $uploaded_file_name, 
			'user_id' 		=> get_user_id(),
		];

		$new_doc_id = $this->OrgModel->insertDocument($data);

		if ($new_doc_id) {
			set_flash_alert('success', 'Document "' . htmlspecialchars($data['title']) . '" uploaded successfully and submitted for review.');
			redirect(BASE_URL . '/org/documents/all'); 
			return;
		} else {
			set_flash_alert('success', 'Document "' . htmlspecialchars($new_data['title']) . '" successfully uploaded and submitted for review.');
			redirect(BASE_URL . '/org/documents/all');
			return;
		}
	}

	public function update_document_status() {
	// 1. Get POST data
	$doc_id = (int)$this->io->post('document_id');
	$new_status = $this->io->post('new_status');
	$doc_title = $this->io->post('document_title') ?? 'Document';
    $review_comment = $this->io->post('review_comment');
	
	$user_id = (int)get_user_id();
	$reviewer_id_to_send = ($user_id > 0) ? $user_id : NULL;

	$is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

	if (!$is_authenticated || ((int)$doc_id) <= 0 || empty($new_status)) {
		set_flash_alert('danger', 'Invalid session or missing data.');
		redirect(BASE_URL . '/org/documents/all'); 
		return;
	}
	
	// --- IMPORTANT: Only allow Approved and Rejected status updates ---
	if (!in_array($new_status, ['Approved', 'Rejected'])) {
		set_flash_alert('danger', "Invalid status: {$new_status}. Only 'Approved' or 'Rejected' are allowed.");
		redirect(BASE_URL . '/org/documents/all'); 
		return;
	}

	$data_to_update = [
		'status' => $new_status,
        'review_comment' => $review_comment, 
		'approved_at' => NULL,
		'rejected_at' => NULL,
		'deleted_at' => NULL, // Ensure soft delete column is null
		'reviewer_id' => $reviewer_id_to_send, 
	];
	
	$current_datetime = date('Y-m-d H:i:s');
	
	if ($new_status === 'Approved') {
		$data_to_update['approved_at'] = $current_datetime;
	} elseif ($new_status === 'Rejected') {
		$data_to_update['rejected_at'] = $current_datetime;
	} 
	
	$success_indicator = $this->OrgModel->updateDocument((int)$doc_id, $data_to_update);
	
	// 3. Handle response, notification, and redirect
	if ($success_indicator !== FALSE) {
		
		// FETCH DOCUMENT DETAILS TO GET SUBMITTER ID/EMAIL
		$doc = $this->OrgModel->getDocumentById($doc_id); 
		$submitter_id = $doc['user_id'] ?? null;
		$submitter_email = $doc['email'] ?? null;
		$submitter_fname = $doc['submitter_fname'] ?? 'User';
		
		if ($submitter_id) {
			
			// SEND EMAIL NOTIFICATION (New Interactive UI)
			if ($submitter_email) {
				
				$is_approved = ($new_status === 'Approved');
				$icon_html = $is_approved 
					? '<div style="color: #10b981; font-size: 48px; line-height: 1; margin-bottom: 15px;">&#10003;</div>' // Green Check
					: '<div style="color: #ef4444; font-size: 48px; line-height: 1; margin-bottom: 15px;">&#10006;</div>'; // Red X
				$status_color = $is_approved ? '#10b981' : '#ef4444';
				
				$email_subject = "Maestro Update: Your Document '{$doc_title}' is {$new_status}";
				$email_body = "
					<div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
						<div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e0e0e0; box-shadow: 0 4px 8px rgba(0,0,0,0.05); padding: 30px; text-align: center;'>
							
							{$icon_html}
							
							<h1 style='color: {$status_color}; font-size: 24px; margin: 0 0 10px 0;'>Document Review Complete</h1>
							
							<p style='color: #333; font-size: 16px; margin: 0 0 20px 0;'>
								Dear ".htmlspecialchars($submitter_fname).", the document <strong>".htmlspecialchars($doc_title)."</strong> has been processed.
							</p>
							
							<div style='background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
								<p style='font-size: 14px; color: #555; margin: 0 0 5px 0;'><strong>Final Status:</strong> <span style='color: {$status_color};'>".htmlspecialchars($new_status)."</span></p>
								<p style='font-size: 14px; color: #555; margin: 0;'><strong>Reviewer Comment:</strong> <em>".htmlspecialchars($review_comment)."</em></p>
							</div>
							
							<p style='font-size: 12px; color: #999; margin-top: 30px;'>
								This is an automated notification.
							</p>
						</div>
					</div>
				";
				sendEmail($submitter_email, $email_subject, $email_body); 
			}
		}
		
		$message = "Status for '{$doc_title}' successfully changed to {$new_status}.";
		set_flash_alert('success', $message);
		
		$redirect_segment = strtolower(str_replace(' ', '', $new_status));

		header('Location: ' . BASE_URL . '/org/documents/' . $redirect_segment);
		exit(); 
	} else {
		set_flash_alert('danger', 'Failed to update document status in the database. Please check DB logs.');
		redirect(BASE_URL . '/org/documents/all'); 
		return; 
	}
}

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
		$original_doc_id = (int)$this->io->post('document_id'); // Ensure it's an integer
		$user_id = get_user_id(); 
		
		// 1. Initial Checks (Authentication and Doc ID)
		$is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

		if (!$is_authenticated || $original_doc_id <= 0) {
			set_flash_alert('danger', 'Invalid document ID or please log in.');
			redirect(BASE_URL . '/login');
			return;
		}

		// 2. Setup and Dependencies
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
		// NOTE: The previous view allowed optional upload, but resubmitting a rejected doc without changing the file is odd. 
		// We'll enforce validation here to catch the error.
		if (empty($uploaded_file) || $uploaded_file['error'] === UPLOAD_ERR_NO_FILE) {
			 $this->form_validation->set_error_message('', '%s is required for resubmission.', 'Document File');
		} elseif (isset($uploaded_file['error']) && $uploaded_file['error'] !== UPLOAD_ERR_OK) {
			 $this->form_validation->set_error_message('', 'File upload failed with error code: ' . $uploaded_file['error']);
		}
		
		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->errors();
			set_flash_alert('danger', $errors);
			// This redirect path assumes documents_edit exists, but for the rejected page flow, 
			// a general redirect to rejected page or documents/all is usually safer if edit view isn't available.
			redirect(BASE_URL . '/org/documents/rejected'); 
			return;
		}

		// 4. File Upload Execution
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

		// 5. Database UPDATE (FIXED LOGIC)
		
		// Delete the old file before updating the record
		$old_doc = $this->OrgModel->getDocumentById($original_doc_id);
		if (!empty($old_doc['file_name'])) {
			$old_file_path = ROOT_DIR . 'public/uploads/documents/' . $old_doc['file_name'];
			if (file_exists($old_file_path)) {
				@unlink($old_file_path);
			}
		}
		
		// Build the update data
		$new_data = [
			'title' 		=> $new_doc_title,
			'type' 			=> ucfirst($this->io->post('type')),
			'status' 		=> 'Pending Review', // CRITICAL: Set status to PENDING REVIEW
			'description' 	=> $this->io->post('description'),
			'tags' 			=> array_key_exists('tags', $_POST) ? $this->io->post('tags') : '',
			'reviewer_id' 	=> $this->io->post('reviewer') ?: NULL, 
			'file_name' 	=> $uploaded_file_name, // Update to the new file name
			'updated_at' 	=> date('Y-m-d H:i:s'),
			'rejected_at' 	=> NULL, // Clear rejection stamp
			'approved_at' 	=> NULL,  // Clear approval stamp
            'deleted_at' 	=> NULL,   // Clear any soft delete stamp
		];

		$success = $this->OrgModel->updateDocument($original_doc_id, $new_data); // CRITICAL: Update existing ID

		if (!$success) {
			set_flash_alert('danger', 'Failed to update document record in database. Please contact IT.');
			redirect(BASE_URL . '/org/documents/rejected'); 
			return;
		}

		// 6. Success
		set_flash_alert('success', 'Document "' . htmlspecialchars($new_data['title']) . '" successfully resubmitted and placed in Pending Review.');
		// FINAL REDIRECT FIX: Redirect to the main documents all page
		redirect(BASE_URL . '/org/documents/all'); 
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
	
	public function documents_rejected() { 
        $q = $this->io->get('q');
        $type = $this->io->get('type');
        
		$docs = $this->OrgModel->getRejectedDocuments($q, $type);
		
		// FIX: Fetch reviewers for the resubmit modal on the rejected page
		$reviewers = $this->OrgModel->getPotentialReviewers();
		
		$this->call->view('org/documents/rejected', [
			'docs' => $docs, 
			'reviewers' => $reviewers,
            'q' => $q,
            'type' => $type
		]); 
	}
    
    // ----------------------------------------------------------------------
    // ORGANIZATION: MEMBERS (UPDATED)
    // ----------------------------------------------------------------------

    public function members_list() { 
        $q = $this->io->get('q');
        $selected_role = $this->io->get('role'); 
        
        $members = $this->OrgModel->getMembers($q, $selected_role); 
        $departments = $this->OrgModel->getDepartments(); 
        $roles = $this->OrgModel->getRoles(); 
        
        $this->call->view('org/members/list', [
            'members' => $members,
            'departments' => $departments, 
            'roles' => $roles, 
            'q' => $q,
            'selected_role' => $selected_role 
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
        
        $this->form_validation->name('email|Email Address')->required()->valid_email();
        $this->form_validation->name('dept_id|Department')->required()->greater_than('0');
        $this->form_validation->name('role_id|Role')->required()->greater_than('0');
        
        if (!$this->form_validation->run()) {
            set_flash_alert('danger', $this->form_validation->errors());
            redirect(BASE_URL . '/org/members/add');
            return;
        }

        $email = $this->io->post('email');
        $dept_id = (int)$this->io->post('dept_id');
        $role_id = (int)$this->io->post('role_id');

        $existing_user = $this->OrgModel->getMemberByEmail($email);
        
        if (empty($existing_user)) {
             set_flash_alert('danger', 'Member not found. Please ensure the user has signed up before attempting to add them to the organization.');
            redirect(BASE_URL . '/org/members/add');
            return;
        }
        if ($this->OrgModel->isRoleUniqueInDepartment($role_id, $dept_id, $existing_user['id'])) {
            $roles = $this->OrgModel->getRoles();
            $role_name = array_filter($roles, fn($r) => (int)($r['id'] ?? 0) === $role_id);
            $role_name = reset($role_name)['name'] ?? 'The specified role';
            
             set_flash_alert('danger', "The role '{$role_name}' is a unique position and is already assigned in this department.");
            redirect(BASE_URL . '/org/members/add');
            return;
        }
        
        $member_id = (int)$existing_user['id'];
        $full_name = trim($existing_user['fname'] . ' ' . $existing_user['lname']);

        $data = [
            'dept_id'    => $dept_id,
            'role_id'    => $role_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $success = $this->OrgModel->updateMember($member_id, $data);

        if ($success) {
            set_flash_alert('success', 'Existing user "' . htmlspecialchars($full_name) . '" added to organization successfully.');
            redirect(BASE_URL . '/org/members/list');
        } else {
            set_flash_alert('danger', 'Failed to update member\'s organization details in the database.');
            redirect(BASE_URL . '/org/members/add');
        }
    }
    
    public function members_update() {
    $this->call->library('Form_validation');
    
    $member_id = (int)$this->io->post('member_id');
    $current_user_id = (int)get_user_id();
    
    $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    
    if (!$is_authenticated || $member_id <= 0) {
        set_flash_alert('danger', 'Invalid request or session expired.');
        redirect(BASE_URL . '/org/members/list');
        return;
    }

    // --- NEW LOGIC START: Determine permissions ---
    $current_user_role = $_SESSION['user_role'] ?? '';
    $admin_roles = ['Administrator', 'President', 'Adviser'];
    $can_manage_org = in_array($current_user_role, $admin_roles);
    $is_self_edit = ($member_id === $current_user_id);
    
    // Non-admin trying to edit someone else's credentials via POST is blocked here (already covered by frontend hide/unauthorized modal, but good for security)
    if (!$is_self_edit && !$can_manage_org) {
        set_flash_alert('danger', 'You do not have permission to edit this member\'s details.');
        redirect(BASE_URL . '/org/members/list');
        return;
    }
    // --- NEW LOGIC END ---

    $dept_id = (int)$this->io->post('dept_id');
    $role_id = (int)$this->io->post('role_id');
    
    // 1. VALIDATION
    $this->form_validation->name('fname|First Name')->required()->max_length(50);
    $this->form_validation->name('lname|Last Name')->required()->max_length(50);
    $this->form_validation->name('email|Email Address')->required()->valid_email();
    
    // Only validate Group/Dept fields if the current user is an Admin (can_manage_org)
    if ($can_manage_org) {
        $this->form_validation->name('dept_id|Department')->required()->greater_than('0');
        $this->form_validation->name('role_id|Role')->required()->greater_than('0');
    }
    
    $new_password = $this->io->post('new_password');
    $confirm_password = $this->io->post('confirm_password');
    
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            set_flash_alert('danger', 'Password must be at least 8 characters long.');
            redirect(BASE_URL . '/org/members/list');
            return;
        }
        if ($new_password !== $confirm_password) {
            set_flash_alert('danger', 'New password and confirmation do not match.');
            redirect(BASE_URL . '/org/members/list');
            return;
        }
    }
    
    if (!$this->form_validation->run()) {
        set_flash_alert('danger', $this->form_validation->errors());
        redirect(BASE_URL . '/org/members/list');
        return;
    }
    
    $email = $this->io->post('email');
    $existing_user = $this->OrgModel->getMemberByEmail($email); 

    if ($existing_user && (int)$existing_user['id'] !== $member_id) {
        set_flash_alert('danger', 'This email address is already in use by another member.');
        redirect(BASE_URL . '/org/members/list');
        return;
    }
    
    // 2. DATA PREPARATION
    $data = [
        'fname'     => $this->io->post('fname'),
        'lname'     => $this->io->post('lname'),
        'email'     => $email,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // --- CRITICAL PERMISSION CHECK FOR GROUP FIELDS ---
    if ($can_manage_org) {
        // Admins are allowed to update group fields using POST data
        $data['dept_id'] = $dept_id;
        $data['role_id'] = $role_id;
        
        // Uniqueness check for unique roles only applies when admin changes groups
        if ($this->OrgModel->isRoleUniqueInDepartment($role_id, $dept_id, $member_id)) {
            $roles = $this->OrgModel->getRoles();
            $role_name = array_filter($roles, fn($r) => (int)($r['id'] ?? 0) === $role_id);
            $role_name = reset($role_name)['name'] ?? 'The specified role';

            set_flash_alert('danger', "The role '{$role_name}' is a unique position and is already assigned in this department.");
            redirect(BASE_URL . '/org/members/list');
            return;
        }
    } else {
        // Non-admins update ONLY their personal fields.
        // We ensure the previous dept_id and role_id (which were sent via hidden fields in the view)
        // are copied back into $data to prevent unintended changes to these columns.
        $member_before_update = $this->OrgModel->getMemberById($member_id);
        $data['dept_id'] = $member_before_update['dept_id'] ?? null;
        $data['role_id'] = $member_before_update['role_id'] ?? null;
    }
    // --- END CRITICAL PERMISSION CHECK ---
    
    if (!empty($new_password)) {
        $this->call->library('lauth');
        $data['password'] = $this->lauth->passwordhash($new_password);
    }
    
    $success = $this->OrgModel->updateMember($member_id, $data);
    
    if ($success !== FALSE) {
        
        $new_role_name = $_SESSION['user_role'] ?? 'General Member'; 
        
        if ($is_self_edit) {
            
            // Re-fetch role name if the update was successful (and group was changed by admin) or if name was changed
            if ($can_manage_org || ($success > 0 && ($data['fname'] ?? null) || ($data['lname'] ?? null))) {
                $updated_member = $this->OrgModel->getMemberById($member_id);
                $new_role_id = $updated_member['role_id'] ?? null;
                $roles_list = $this->OrgModel->getRoles();
                
                foreach ($roles_list as $role) {
                    if ((int)($role['id'] ?? 0) === (int)($new_role_id)) {
                        $new_role_name = $role['name'];
                        break;
                    }
                }
            }
            
            if (!isset($_SESSION)) {
                session_start();
            }
            $_SESSION['user_role'] = $new_role_name;
            $_SESSION['user_name'] = ($data['fname'] ?? '') . ' ' . ($data['lname'] ?? ''); 
        }

        $full_name = $data['fname'] . ' ' . $data['lname'];
        
        $message = ($success > 0) ? 
            'Member "' . htmlspecialchars($full_name) . '" updated successfully.' :
            'Member "' . htmlspecialchars($full_name) . '" details verified (no new changes were saved).';

        set_flash_alert('success', $message);
    } else {
        set_flash_alert('danger', 'Failed to update member. A critical database error occurred.');
    }
    
    redirect(BASE_URL . '/org/members/list');
}

public function members_delete() {
    $member_id = (int)$this->io->post('member_id');
    
    $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    
    if (!$is_authenticated || $member_id <= 0) {
        set_flash_alert('danger', 'Invalid request or session expired.');
        redirect(BASE_URL . '/org/members/list');
        return;
    }
    
    $current_user_id = (int)get_user_id();
    if ($member_id === $current_user_id) {
        set_flash_alert('danger', 'You cannot delete your own account.');
        redirect(BASE_URL . '/org/members/list');
        return;
    }
    
    $member = $this->OrgModel->getMemberById($member_id);
    
    if (!$member) {
        set_flash_alert('danger', 'Member not found.');
        redirect(BASE_URL . '/org/members/list');
        return;
    }
    
    $success = $this->OrgModel->deleteMember($member_id);
    
    if ($success) {
        $full_name = ($member['fname'] ?? '') . ' ' . ($member['lname'] ?? '');
        set_flash_alert('success', 'Member "' . htmlspecialchars(trim($full_name)) . '" has been deleted.');
    } else {
        set_flash_alert('danger', 'Failed to delete member. Please try again.');
    }
    
    redirect(BASE_URL . '/org/members/list');
}

    // ----------------------------------------------------------------------
    // ORGANIZATION: DEPARTMENTS & ROLES (Minimal Implementation)
    // ----------------------------------------------------------------------
    
    public function departments() { 
        $depts = $this->OrgModel->getDepartmentsWithStats(); 
        $potential_members = $this->OrgModel->getPotentialDepartmentMembers();
        foreach ($depts as &$dept) { 
            $dept_id = $dept['id'] ?? 0;
            $dept['assigned_members'] = $this->OrgModel->getMembersByDepartment((int)$dept_id);
        }
        unset($dept);

        $this->call->view('org/departments', compact('depts', 'potential_members')); 
    }

    public function fetch_dept_members($dept_id) 
    {
        $members = $this->OrgModel->getMembersByDepartment((int)$dept_id);
        header('Content-Type: application/json');
        echo json_encode($members);
        exit;
    }

    public function departments_store() {
        $this->call->library('Form_validation');
        
        $this->form_validation->name('name|Department Name')->required()->max_length(100)->is_unique('departments', 'name', $this->io->post('name')); 
        
        if (!$this->form_validation->run()) {
            set_flash_alert('danger', $this->form_validation->errors());
            redirect(BASE_URL . '/org/departments');
            return;
        }
        
        $member_ids = isset($_POST['member_ids']) ? $this->io->post('member_ids') : [];
        $member_ids = is_array($member_ids) ? $member_ids : [$member_ids];
        $member_ids = array_filter($member_ids, 'is_numeric'); 

        $data = [
            'name' => $this->io->post('name'),
        ];

        $new_dept_id = $this->OrgModel->insertDepartment($data);

        if ($new_dept_id) {
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
    
    public function departments_update() {
        $this->call->library('Form_validation');
        
        $dept_id = (int)$this->io->post('dept_id');
        $new_name = $this->io->post('name');
        
        if ($dept_id <= 0) {
            set_flash_alert('danger', 'Invalid department ID.');
            redirect(BASE_URL . '/org/departments');
            return;
        }

        $this->form_validation->name('name|Department Name')->required()->max_length(100); 

        if (!$this->form_validation->run()) {
            set_flash_alert('danger', $this->form_validation->errors());
            redirect(BASE_URL . '/org/departments');
            return;
        }

        if ($this->OrgModel->isDepartmentNameDuplicate($new_name, $dept_id)) {
            set_flash_alert('danger', 'The Department Name is already in use by another department.');
            redirect(BASE_URL . '/org/departments');
            return;
        }
        
        $data = ['name' => $new_name];

        $success = $this->OrgModel->updateDepartment($dept_id, $data);

        if ($success) {
            set_flash_alert('success', 'Department "' . htmlspecialchars($new_name) . '" updated successfully.');
            redirect(BASE_URL . '/org/departments');
        } else {
            set_flash_alert('danger', 'Failed to update department. Please try again or check if the name already exists.');
            redirect(BASE_URL . '/org/departments');
        }
    }

    public function departments_delete() {
        $dept_id = (int)$this->io->post('dept_id');
        $submitted_code = $this->io->post('verification_code'); 
        $session_code = $_SESSION['dept_delete_code'] ?? null;
        
        $is_authenticated = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

        if (!$is_authenticated || $dept_id <= 0) {
            set_flash_alert('danger', 'Invalid request or session expired.');
            redirect(BASE_URL . '/org/departments');
            return;
        }

        if (empty($submitted_code) || $submitted_code !== $session_code) {
            
            $new_code = (string)random_int(1000, 9999); 
            $_SESSION['dept_delete_code'] = $new_code;
            
            $department = $this->OrgModel->getDepartmentById($dept_id);
            $dept_name = htmlspecialchars($department['name'] ?? 'Department');
            
            set_flash_alert('warning', 
                "**Verification Required:** To confirm deletion of **{$dept_name}**, please re-submit the form and enter the code **{$new_code}** in the confirmation box."
            );
            redirect(BASE_URL . '/org/departments');
            return;
        }

        $department = $this->OrgModel->getDepartmentById($dept_id);
        $dept_name = $department['name'] ?? 'Department';

        $this->OrgModel->unassignMembersFromDepartment($dept_id);

        $success = $this->OrgModel->deleteDepartment($dept_id);
        
        unset($_SESSION['dept_delete_code']);

        if ($success) {
            $full_name = htmlspecialchars(trim($dept_name));
            set_flash_alert('success', "Department **{$full_name}** deleted successfully and all members unassigned.");
        } else {
            set_flash_alert('danger', 'Failed to delete department. Please try again.');
        }
        
        redirect(BASE_URL . '/org/departments');
    }

    
    public function roles() { 
        $roles = $this->OrgModel->getRoles(); 
        $this->call->view('org/roles', compact('roles')); 
    }

    public function settings() { $this->call->view('org/settings'); }
    public function profile() {
        $user_id = get_user_id();
        
        if ($user_id <= 0) {
            set_flash_alert('danger', 'Please log in to view your profile.');
            redirect(BASE_URL . '/login');
            return;
        }

        $user_details = $this->OrgModel->getMemberById($user_id);

        if (empty($user_details)) {
             set_flash_alert('danger', 'Could not retrieve user details.');
             redirect(BASE_URL . '/org/dashboard');
             return;
        }

        $this->call->view('org/profile', [
            'user' => $user_details 
        ]); 
    }
    
     public function documents_department_review() {
        // 1. Get user details to find their department ID
        $user_id = get_user_id();
        $user = $this->OrgModel->getMemberById($user_id);
        $dept_id = $user['dept_id'] ?? 0;
        
        // Initialize variables for the view
        $is_member_assigned = ($dept_id !== 0 && $dept_id !== NULL); // <-- Flag: True if assigned
        $dept_docs = [];
        $dept_name = 'Unassigned';
        $q = $this->io->get('q'); 
        
        // 2. Check if user is assigned to a department
        if ($is_member_assigned) {
            // 3. Fetch documents for that department
            $dept_docs = $this->OrgModel->getDocumentsByDepartment((int)$dept_id, $q);
            
            // 4. Fetch department name for the view title
            $department = $this->OrgModel->getDepartmentById((int)$dept_id);
            $dept_name = $department['name'] ?? 'Your Department';
        }
        
        // 5. Load the new view (no redirect here)
        $this->call->view('org/documents/department_review', [
            'docs' => $dept_docs,
            'dept_name' => $dept_name,
            'q' => $q,
            'is_member_assigned' => $is_member_assigned // <-- PASS THE FLAG
        ]); 
    }

    public function profile_update() {
        $user_id = (int)get_user_id();
        
        if ($user_id <= 0) {
            set_flash_alert('danger', 'Session expired. Please log in again.');
            redirect(BASE_URL . '/login');
            return;
        }

        $this->call->library('Form_validation');
        $this->call->library('lauth'); 
        
        $this->form_validation->name('fname|First Name')->required()->max_length(50);
        $this->form_validation->name('lname|Last Name')->required()->max_length(50);
        
        $new_password = $this->io->post('new_password');
        $confirm_password = $this->io->post('confirm_password');
        
        if (!empty($new_password)) {
            // Re-validate password fields using Form_validation functions if needed, 
            // but simple checks suffice here.
            if (strlen($new_password) < 8) {
                set_flash_alert('danger', 'New password must be at least 8 characters long.');
                redirect(BASE_URL . '/org/profile');
                return;
            }
            if ($new_password !== $confirm_password) {
                set_flash_alert('danger', 'New password and confirmation do not match.');
                redirect(BASE_URL . '/org/profile');
                return;
            }
        }
        
        if (!$this->form_validation->run()) {
            set_flash_alert('danger', $this->form_validation->errors());
            redirect(BASE_URL . '/org/profile');
            return;
        }
        
        $data = [
            'fname'     => $this->io->post('fname'),
            'lname'     => $this->io->post('lname'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($new_password)) {
            // Hash the new password before storage
            $data['password'] = $this->lauth->passwordhash($new_password);
        }
        
        $success = $this->OrgModel->updateMember($user_id, $data);
        
        if ($success !== FALSE) {
            // Refresh session variables immediately since the name might have changed
            $full_name = ($data['fname'] ?? '') . ' ' . ($data['lname'] ?? '');
            
            $this->session->set_userdata([
                'user_name' => $full_name,
            ]);

            $message = ($success > 0) ? 
                'Profile successfully updated.' :
                'Profile saved (no changes detected).';

            set_flash_alert('success', $message);
        } else {
            set_flash_alert('danger', 'Failed to update profile. A critical database error occurred.');
        }
        
        redirect(BASE_URL . '/org/profile');
    }
}