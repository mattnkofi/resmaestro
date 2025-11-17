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
        return $this->db->table('documents')->select('title, type, status')->get_all();
    }
    
    /**
     * Fetches documents with the 'Pending Review' status, joining with users for submitter name.
     * @return array
     */
    public function getPendingDocuments() {
        return $this->db
            ->select('d.title, d.status, d.created_at, u.fname, u.lname')
            ->table('documents d')
            ->left_join('users u', 'd.user_id = u.id')
            ->where('d.status', 'Pending Review')
            ->order_by('d.created_at', 'ASC')
            ->get_all();
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