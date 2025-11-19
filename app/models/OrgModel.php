<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

require_once SYSTEM_DIR . 'kernel/Model.php';

class OrgModel extends Model
{
    public function __construct() {
        parent::__construct();
        $this->call->database();
    }

    // --- Core Stats ---

    public function get_dashboard_stats()
    {
        $stats = [];
        $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
                
        // 1. Total Documents (No WHERE clause, standard count is stable)
        $stats['total_documents']    = $this->db->table('documents')->count();
        
        // 2. Pending Reviews (FIX: Use raw SQL to control binding)
        $pending_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['pending_reviews']    = $this->db->raw($pending_query, ['Pending Review'])->fetch()['count'];
        
        // 3. Approved Documents (FIX: Use raw SQL to control binding)
        $approved_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['approved_documents'] = $this->db->raw($approved_query, ['Approved'])->fetch()['count'];
        
        // 4. New Members (Fixed previously, using raw SQL is safest for date comparisons)
        $new_members_query = "SELECT COUNT(*) AS count FROM users WHERE created_at >= ?";
        $stats['new_members']        = $this->db->raw($new_members_query, [$seven_days_ago])->fetch()['count'];
        
        return $stats;
    }

    // --- Documents Fetching ---

    public function getAllDocuments() {
        // FIX: Ensure 'd.id' is selected
        return $this->db
            ->select('d.id, d.title, d.type, d.status')
            ->table('documents d')
            ->get_all();
    }
    
    /**
     * Fetches documents with the 'Pending Review' status, joining with users for submitter name.
     * @return array
     */
    public function getPendingDocuments($query = '', $type = '') {
    $search_term = "%{$query}%";
    $this->db
        // Ensure d.id is selected for the review link
        ->select('d.id, d.title, d.status, d.created_at, d.type, d.file_name, u.fname, u.lname')
        ->table('documents d')
        ->left_join('users u', 'd.user_id = u.id')
        ->where('d.status', 'Pending Review'); // Always filter by status

    // 1. Apply Search Query Filter (if present)
    if (!empty($query)) {
        $this->db->grouped(function($q) use ($search_term) {
            // Search by document title, first name, or last name
            $q->like('d.title', $search_term)
              ->or_like('u.fname', $search_term)
              ->or_like('u.lname', $search_term);
        });
    }

    // 2. Apply Document Type Filter (if present)
    if (!empty($type)) {
        $this->db->where('d.type', $type);
    }

    // 3. Apply Ordering and Execute
    return $this->db->order_by('d.created_at', 'ASC')->get_all();
}

    /**
     * Fetches documents with the 'Approved' status, applying search and type filters.
     * @param string $query Search term for title/approver.
     * @param string $type Document type filter.
     * @return array
     */
    public function getApprovedDocuments($query = '', $type = '') {
        $search_term = "%{$query}%";
        $this->db
            // Select necessary fields, including the file name and the approver's name
            ->select('d.id, d.title, d.file_name, d.status, d.created_at, d.type, u.fname AS approver_fname, u.lname AS approver_lname')
            ->table('documents d')
            // Join users on the reviewer ID who approved the document
            ->left_join('users u', 'd.reviewer_id = u.id') 
            ->where('d.status', 'Approved'); // Filter by Approved status

        // 1. Apply Search Query Filter (if present)
        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                // Search by document title, approver first name, or approver last name
                $q->like('d.title', $search_term)
                  ->or_like('u.fname', $search_term)
                  ->or_like('u.lname', $search_term);
            });
        }

        // 2. Apply Document Type Filter (if present)
        if (!empty($type)) {
            $this->db->where('d.type', $type);
        }

        // 3. Apply Ordering and Execute (Use created_at as approved_at doesn't exist)
        return $this->db->order_by('d.created_at', 'DESC')->get_all();
    }
    
    public function getRejectedDocuments() {
        // FIX: Join with users to get reviewer names and select necessary fields (id, title, description, created_at)
        return $this->db
            ->select('d.id, d.title, d.created_at, d.description, d.file_name, d.type, u.fname AS reviewer_fname, u.lname AS reviewer_lname')
            ->table('documents d')
            ->left_join('users u', 'd.reviewer_id = u.id') 
            ->where('d.status', 'Rejected')
            ->order_by('d.created_at', 'DESC')
            ->get_all();
    }
    
    public function getArchivedDocuments() {
        return $this->db->table('documents')->where('status', 'Archived')->get_all();
    }

    // --- Status Update Methods ---

    /**
     * Generic method to update a document's attributes by ID.
     */
    public function updateDocument(int $doc_id, array $data) {
        return $this->db->table('documents')
                        ->where('id', $doc_id)
                        ->update($data);
    }

    /**
     * Fetches a single document by ID, including the submitter's name.
     */
    public function getDocumentById(int $doc_id) {
        return $this->db
            ->select('d.*, u.fname AS submitter_fname, u.lname AS submitter_lname')
            ->table('documents d')
            ->left_join('users u', 'd.user_id = u.id')
            ->where('d.id', $doc_id)
            ->get_row();
    }

    // --- Upload Support ---

    public function getPotentialReviewers() {
        return $this->db->table('users')
                        ->select('id, fname, lname, email') 
                        ->order_by('lname', 'ASC')
                        ->get_all();
    }

    public function getRecentUserUploads($user_id, $limit = 3) {
        return $this->db->table('documents')
                        ->select('title, type, status')
                        ->where('user_id', $user_id)
                        ->order_by('created_at', 'DESC')
                        ->limit($limit)
                        ->get_all();
    }
    
    public function insertDocument(array $data) {
        $this->db->table('documents')->insert($data);
        return $this->db->last_id();
    }

    // --- Placeholders (Return empty data sets to avoid runtime errors in views) ---
    public function getPendingReviews()  { return []; } 
    public function getReviewHistory()   { return []; }
    public function getComments()        { return []; }
    public function getMembers() { return []; } 
    public function getDepartments() { return []; } 
    public function getRoles() { return []; }
}