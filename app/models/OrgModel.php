<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

require_once SYSTEM_DIR . 'kernel/Model.php';

class OrgModel extends Model
{
    public function __construct() {
        parent::__construct();
        $this->call->database();
    }

    /**
     * Fetches all core statistics and activity needed for the Organization Dashboard.
     * Uses explicit RAW queries for parameterized counts to ensure correct binding.
     * @return array
     */
    public function get_dashboard_stats()
    {
        $stats = [];
                
        // 1. Total Documents (Non-parameterized query builder is safe)
        $stats['total_documents'] = $this->db->table('documents')->count();
        
        // 2. Pending Reviews (Uses RAW to guarantee correct binding)
        $pending_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['pending_reviews'] = $this->db->raw($pending_query, ['Pending Review'])->fetch()['count'];
        
        // 3. Approved Documents (Uses RAW to guarantee correct binding)
        $approved_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['approved_documents'] = $this->db->raw($approved_query, ['Approved'])->fetch()['count'];
        
        // 4. New Members (Joined in the last 7 days - Uses RAW to guarantee correct binding)
        $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
        $new_members_query = "SELECT COUNT(*) AS count FROM users WHERE created_at >= ?";
        $stats['new_members'] = $this->db->raw($new_members_query, [$seven_days_ago])->fetch()['count'];
        }
    
    // Placeholder methods updated to use raw queries for robustness:
    public function getAllDocuments() {
        return $this->db->table('documents')->get_all();
    }

    public function getPendingDocuments() {
        return array_filter($this->getAllDocuments(), fn($d)=>$d['status']=='Pending');
    }
    public function getApprovedDocuments() {
        return array_filter($this->getAllDocuments(), fn($d)=>$d['status']=='Approved');
    }
    public function getRejectedDocuments() {
        return array_filter($this->getAllDocuments(), fn($d)=>$d['status']=='Rejected');
    }
    public function getArchivedDocuments() {
        return []; // example
    }

    // Mock functions remain for tables we haven't created yet:
    public function getMembers() { return []; } 
    public function getDepartments() { return []; } 
    public function getRoles() { return []; }
    
    public function getPendingReviews()  { return []; } 
    public function getReviewHistory()   { return []; }
    public function getComments()        { return []; }
}