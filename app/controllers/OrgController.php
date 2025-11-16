<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

require_once 'app/models/OrgModel.php';

class OrgController
{
    private $model;

    public function __construct() {
        $this->model = new OrgModel();
    }

    /**
     * Retrieves dashboard statistics and activity from the model 
     * and passes them to the dashboard view.
     */
    public function dashboard()
    {
        // 1. Fetch all dashboard data
        $dashboard_data = $this->model->get_dashboard_stats();
    
        
        // 3. Load the view (assuming variables defined locally are accessible)
        require 'app/views/org/dashboard.php'; 
    }

    public function sidebar()         { require 'app/views/org/sidebar.php'; }

    // Documents
    public function documents_all()     { $docs = $this->model->getAllDocuments(); require 'app/views/org/documents/all.php'; }
    public function documents_upload()  { require 'app/views/org/documents/upload.php'; }
    public function documents_pending() { $docs = $this->model->getPendingDocuments(); require 'app/views/org/documents/pending.php'; }
    public function documents_approved(){ $docs = $this->model->getApprovedDocuments(); require 'app/views/org/documents/approved.php'; }
    public function documents_rejected(){ $docs = $this->model->getRejectedDocuments(); require 'app/views/org/documents/rejected.php'; }
    public function documents_archived(){ $docs = $this->model->getArchivedDocuments(); require 'app/views/org/documents/archived.php'; }

    // Review & Workflow
    public function review_queue()   { $reviews = $this->model->getPendingReviews(); require 'app/views/org/review/queue.php'; }
    public function review_history() { $reviews = $this->model->getReviewHistory(); require 'app/views/org/review/history.php'; }
    public function review_comments(){ $comments = $this->model->getComments(); require 'app/views/org/review/comments.php'; }

    // Organization
    public function members_list() { $members = $this->model->getMembers(); require 'app/views/org/members/list.php'; }
    public function members_add()  { require 'app/views/org/members/add.php'; }
    public function departments()  { $depts = $this->model->getDepartments(); require 'app/views/org/departments.php'; }
    public function roles()        { $roles = $this->model->getRoles(); require 'app/views/org/roles.php'; }

    // Reports
    public function reports_overview()  { require 'app/views/org/reports/overview.php'; }
    public function reports_documents() { require 'app/views/org/reports/documents.php'; }
    public function reports_reviewers() { require 'app/views/org/reports/reviewers.php'; }
    public function reports_storage()   { require 'app/views/org/reports/storage.php'; }

    // Settings/Profile
    public function settings() { require 'app/views/org/settings.php'; }
    public function profile()  { require 'app/views/org/profile.php'; }
}