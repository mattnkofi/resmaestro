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
                
        $stats['total_documents']      = $this->db->table('documents')->count();
        
        $pending_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['pending_reviews']      = $this->db->raw($pending_query, ['Pending Review'])->fetch()['count'] ?? 0;
        
        $approved_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['approved_documents'] = $this->db->raw($approved_query, ['Approved'])->fetch()['count'] ?? 0;
        
        $new_members_query = "SELECT COUNT(*) AS count FROM users WHERE created_at >= ?";
        $stats['new_members']          = $this->db->raw($new_members_query, [$seven_days_ago])->fetch()['count'] ?? 0;
        
        return $stats;
    }

    // --- Documents Fetching ---

    public function getAllDocuments() {
        // FIX: Include user names (fname, lname) and created_at for modal display
        return $this->db
            ->select('d.id, d.title, d.type, d.status, d.file_name, d.created_at, u.fname, u.lname')
            ->table('documents d')
            ->left_join('users u', 'd.user_id = u.id') // Join by user_id for submitter name
            ->order_by('d.created_at', 'DESC')
            ->get_all();
    }
    
    /**
     * Fetches documents with the 'Pending Review' status, joining with users for submitter name.
     */
    public function getPendingDocuments($query = '', $type = '') {
        $search_term = "%{$query}%";
        $this->db
            ->select('d.id, d.title, d.status, d.created_at, d.type, d.file_name, u.fname, u.lname')
            ->table('documents d')
            ->left_join('users u', 'd.user_id = u.id')
            ->where('d.status', 'Pending Review'); 

        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('d.title', $search_term)
                  ->or_like('u.fname', $search_term)
                  ->or_like('u.lname', $search_term);
            });
        }

        if (!empty($type)) {
            $this->db->where('d.type', $type);
        }

        return $this->db->order_by('d.created_at', 'ASC')->get_all();
    }

    /**
     * Fetches documents with the 'Approved' status, applying search and type filters.
     */
    public function getApprovedDocuments($query = '', $type = '') {
        $search_term = "%{$query}%";
        $this->db
            ->select('d.id, d.title, d.file_name, d.status, d.created_at, d.approved_at, d.type, u.fname AS approver_fname, u.lname AS approver_lname')
            ->table('documents d')
            ->left_join('users u', 'd.reviewer_id = u.id') 
            ->where('d.status', 'Approved'); 

        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('d.title', $search_term)
                  ->or_like('u.fname', $search_term)
                  ->or_like('u.lname', $search_term);
            });
        }

        if (!empty($type)) {
            $this->db->where('d.type', $type);
        }

        // FIX: Use approved_at for ordering, falling back to created_at if null
        return $this->db->order_by('COALESCE(d.approved_at, d.created_at)', 'DESC')->get_all();
    }
    
    public function getRejectedDocuments() {
        return $this->db
            ->select('d.id, d.title, d.created_at, d.description, d.file_name, d.type, u.fname AS reviewer_fname, u.lname AS reviewer_lname')
            ->table('documents d')
            ->left_join('users u', 'd.reviewer_id = u.id') 
            ->where('d.status', 'Rejected')
            ->order_by('d.created_at', 'DESC')
            ->get_all();
    }
    
    public function getArchivedDocuments() {
        return $this->db
            ->select('d.id, d.title, d.file_name, d.type, d.deleted_at, u.fname, u.lname')
            ->table('documents d')
            ->left_join('users u', 'd.user_id = u.id') 
            ->where('d.status', 'Archived')
            ->order_by('d.deleted_at', 'DESC')
            ->get_all();
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
        $query = "
            SELECT 
                d.*, 
                u.fname AS submitter_fname, 
                u.lname AS submitter_lname
            FROM documents d
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.id = ?
            LIMIT 1
        ";
        
        $result = $this->db->raw($query, [(int)$doc_id])->fetch();

        return $result ?: null; 
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