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
        ->select('d.id, d.title, d.status, d.created_at, u.fname, u.lname')
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

    
    public function getApprovedDocuments() {
        return $this->db->table('documents')->where('status', 'Approved')->get_all();
    }
    
    public function getRejectedDocuments() {
        return $this->db->table('documents')->where('status', 'Rejected')->get_all();
    }
    
    public function getArchivedDocuments() {
        return $this->db->table('documents')->where_not_null('deleted_at')->get_all();
    }

    // --- Status Update Methods ---

    /**
     * Generic method to update a document's attributes by ID.
     * This is used by OrgController::update_document_status().
     * @param int $doc_id The ID of the document to update.
     * @param array $data An associative array of field => value to update.
     * @return bool True on success, false on failure.
     */
    public function updateDocument(int $doc_id, array $data) {
        return $this->db->table('documents')
                        ->where('id', $doc_id)
                        ->update($data);
    }

    /**
     * Fetches a single document by ID, including the submitter's name.
     * This is used by OrgController::documents_review($doc_id).
     * @param int $doc_id The ID of the document.
     * @return array|null The document data or null if not found.
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