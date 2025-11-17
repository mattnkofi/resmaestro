<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// Ensure the parent class is correctly loaded if needed, though usually autoloaded in full framework setup
require_once SYSTEM_DIR . 'kernel/Model.php';

class OrgModel extends Model
{
    // The base Model constructor handles database initialization
    public function __construct() {
        parent::__construct();
        // Explicitly calling database is redundant if autoloaded, but kept if you prefer explicit access
        // $this->call->database();
    }

    /**
     * Fetches all core statistics and activity needed for the Organization Dashboard.
     * @return array
     */
    public function get_dashboard_stats()
    {
        // This logic is designed to pull real counts from the database tables defined earlier
        $stats = [];
        $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
                
        $stats['total_documents']    = $this->db->table('documents')->count();
        $stats['pending_reviews']    = $this->db->table('documents')->where('status', 'Pending Review')->count();
        $stats['approved_documents'] = $this->db->table('documents')->where('status', 'Approved')->count();
        $stats['new_members']        = $this->db->table('users')->where('created_at', '>=', $seven_days_ago)->count();
        
        return $stats;
    }

    /**
     * Fetches all documents with necessary columns for the All Documents view.
     * @return array
     */
    public function getAllDocuments() {
        // SQL: SELECT title, type, status FROM documents
        return $this->db->table('documents')
                        ->select('title, type, status') 
                        ->get_all(); 
    }

    /**
     * Fetches documents with the 'Pending Review' status.
     * @return array
     */
    public function getPotentialReviewers() {
        // Assuming users can be identified by their full name and email for the dropdown
        return $this->db->table('users')
                        ->select('id, fname, lname, email') 
                        ->order_by('lname', 'ASC')
                        ->get_all();
    }

    public function insertDocument(array $data) {
        $this->db->table('documents')->insert($data);
        return $this->db->last_id();
    }


    public function getPendingDocuments() {
        return $this->db->table('documents')->where('status', 'Pending Review')->get_all();
    }
    
    /**
     * Fetches documents with the 'Approved' status.
     * @return array
     */
    public function getApprovedDocuments() {
        return $this->db->table('documents')->where('status', 'Approved')->get_all();
    }
    
    /**
     * Fetches documents with the 'Rejected' status.
     * @return array
     */
    public function getRejectedDocuments() {
        return $this->db->table('documents')->where('status', 'Rejected')->get_all();
    }
    
    /**
     * Fetches archived documents (assuming 'deleted_at' is NOT NULL).
     * @return array
     */
    public function getArchivedDocuments() {
        // Assuming archived means soft-deleted for now
        return $this->db->table('documents')->where_not_null('deleted_at')->get_all();
    }
    
    // Placeholder methods updated to return empty array or query builder for now
    public function getPendingReviews()  { return $this->db->table('documents')->where('status', 'Pending Review')->get_all(); } 
    public function getReviewHistory()   { return $this->db->table('reviews')->get_all(); } // Needs 'reviews' table
    public function getComments()        { return $this->db->table('comments')->get_all(); } // Needs 'comments' table

    public function getMembers() { return $this->db->table('users')->select('id, fname, lname, email')->get_all(); } 
    public function getDepartments() { return $this->db->table('departments')->get_all(); } // Needs 'departments' table
    public function getRoles() { return $this->db->table('roles')->get_all(); } // Needs 'roles' table
}