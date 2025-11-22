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
        
        // 2. Pending Reviews (Use raw SQL to control binding)
        $pending_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['pending_reviews']    = $this->db->raw($pending_query, ['Pending Review'])->fetch()['count'];
        
        // 3. Approved Documents (Use raw SQL to control binding)
        $approved_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['approved_documents'] = $this->db->raw($approved_query, ['Approved'])->fetch()['count'];
        
        // 4. New Members (Using raw SQL is safest for date comparisons)
        $new_members_query = "SELECT COUNT(*) AS count FROM users WHERE created_at >= ?";
        $stats['new_members']        = $this->db->raw($new_members_query, [$seven_days_ago])->fetch()['count'];
        
        return $stats;
    }

    public function getAllDocuments($query = '', $status = '') {
        $search_term = "%{$query}%";
        
        $this->db->select('d.id, d.title, d.type, d.status, d.file_name')
                 ->table('documents d');
    
        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('d.title', $search_term)
                  ->or_like('d.description', $search_term); 
            });
        }
    
        if (!empty($status)) {
            $this->db->where('d.status', $status);
        }
        
        return $this->db->order_by('d.created_at', 'DESC')->get_all();
    }
        
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

    public function getApprovedDocuments($query = '', $type = '') {
    $search_term = "%{$query}%"; // Prepare the search term
    
    $this->db
        ->select('d.id, d.title, d.file_name, d.status, d.created_at, d.type, u.fname AS approver_fname, u.lname AS approver_lname')
        ->table('documents d')
        ->left_join('users u', 'd.reviewer_id = u.id') 
        ->where('d.status', 'Approved');

    // 1. Search Query Filter (Title or Approver Name)
    if (!empty($query)) {
        $this->db->grouped(function($q) use ($search_term) {
            $q->like('d.title', $search_term) // Search by title
              ->or_like('u.fname', $search_term) // Search by approver first name
              ->or_like('u.lname', $search_term); // Search by approver last name
        });
    }

    // 2. Type Filter
    if (!empty($type)) {
        $this->db->where('d.type', $type);
    }
    
    return $this->db->order_by('d.created_at', 'DESC')->get_all();
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
    
    public function getArchivedDocuments($query = '') {
        $search_term = "%{$query}%";
        
        $this->db
            ->select('d.id, d.title, d.type, d.status, d.file_name, d.deleted_at, u.fname, u.lname')
            ->table('documents d')
            ->left_join('users u', 'd.user_id = u.id')
            ->where('d.status', 'Archived');
    
        if (!empty($query)) {
            $this->db->grouped(function($q) use ($query) {
                $q->like('d.title', "%{$query}%")
                  ->or_like('d.description', "%{$query}%"); 
            });
        }
        return $this->db->order_by('d.deleted_at', 'DESC')->get_all();
    }

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

    /**
     * Fetches members who are not currently assigned to a department.
     * @return array
     */
    public function getPotentialDepartmentMembers() {
        return $this->db->table('users')
                        ->select('id, fname, lname')
                        ->where('dept_id', NULL)
                        ->order_by('lname', 'ASC')
                        ->get_all();
    }

    public function getPotentialReviewers() {
        return $this->db->table('users')
                        ->select('id, fname, lname, email') 
                        ->order_by('lname', 'ASC')
                        ->get_all();
    }
    
    public function insertDocument(array $data) {
        $this->db->table('documents')->insert($data);
        return $this->db->last_id();
    }
    
    public function updateDocument(int $doc_id, array $data) {
        return $this->db->table('documents')
                        ->where('id', $doc_id)
                        ->update($data);
    }

    public function getRecentUserUploads(int $user_id, int $limit = 10) {
        return $this->db
            ->select('id, title, status, created_at, file_name')
            ->table('documents')
            ->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get_all();
    }
    
    // ----------------------------------------------------------------------
    //  ORGANIZATION IMPLEMENTATION (FIXED FOR MISSING COLUMNS/TABLES)
    // ----------------------------------------------------------------------

    /**
     * Fetches all members.
     * @param string $query Search term for name or email.
     * @return array
     */
    public function getMembers($query = '') {
    $search_term = "%{$query}%";

    $this->db
        ->select('u.id, u.fname, u.lname, u.email, d.name AS dept_name, r.name AS role_name, u.created_at')
        ->table('users u')
        ->left_join('departments d', 'u.dept_id = d.id')
        ->left_join('roles r', 'u.role_id = r.id');
    
    if (!empty($query)) {
        $this->db->grouped(function($q) use ($search_term) {
            $q->like('u.fname', $search_term)
              ->or_like('u.lname', $search_term)
              ->or_like('u.email', $search_term);
        });
    }
    
    return $this->db->order_by('u.lname', 'ASC')->get_all();
}

    /**
     * Assigns a list of members to a newly created department.
     * @param int $dept_id The ID of the department to assign to.
     * @param array $member_ids Array of user IDs to assign.
     * @return bool
     */
    public function assignMembersToDepartment(int $dept_id, array $member_ids) {
        if (empty($member_ids)) {
            return true; // Nothing to do
        }
        
        return $this->db->table('users')
                        ->where_in('id', $member_ids)
                        ->update(['dept_id' => $dept_id]);
    }

    public function getDepartmentsWithStats() {
    $query = "
        SELECT
            d.id,
            d.name,
            (SELECT COUNT(id) FROM users WHERE users.dept_id = d.id) AS members_count,
            (SELECT COUNT(id) FROM documents WHERE documents.dept_id = d.id) AS documents_count,
            (SELECT COUNT(id) FROM documents WHERE documents.dept_id = d.id AND documents.status = 'Pending Review') AS pending_count
        FROM departments d
        ORDER BY d.name ASC
    ";
    
    try {
        return $this->db->raw($query)->get_all();
    } catch (\Exception $e) {
        // CRITICAL FIX: If the complex stats query fails (e.g., missing columns), 
        // fall back to fetching the simple department list and manually set stats to 0.
        $simple_depts = $this->getDepartmentOptions();
        return array_map(function($d) {
            return [
                'id' => $d['id'], 
                'name' => $d['name'], 
                'members_count' => 0, 
                'documents_count' => 0, 
                'pending_count' => 0
            ];
        }, $simple_depts);
    }
}

    public function getDepartments() { 
    // FIX: Only return the result of getDepartmentOptions() to prevent unreachable code
    return $this->getDepartmentOptions();
}

    public function getDepartmentOptions() { 
    try {
        return $this->db->table('departments')
                        ->select('id, name')
                        ->order_by('name', 'ASC')
                        ->get_all();
    } catch (\Exception $e) {
        return [];
    }
}

    /**
     * Inserts a new department into the database.
     * @return int|bool The new department ID or false on failure.
     */
    public function insertDepartment(array $data) {
        $this->db->table('departments')->insert($data);
        return $this->db->last_id();
    }

    /**
     * Fetches all roles for dropdowns.
     * @return array
     */
    public function getRoles() { 
        try {
            return $this->db->table('roles')
                            ->select('id, name')
                            ->order_by('name', 'ASC')
                            ->get_all();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function insertMember(array $data) {
        $this->db->table('users')->insert($data);
        return $this->db->last_id();
    }
    // ----------------------------------------------------------------------
    //  REVIEW & WORKFLOW IMPLEMENTATION
    // ----------------------------------------------------------------------

    public function getPendingReviews($query = '', $sort = 'oldest') {
        $search_term = "%{$query}%";
        $this->db
            ->select('d.id, d.title, d.status, d.created_at, d.type, d.file_name, u.fname AS submitter_fname, u.lname AS submitter_lname')
            ->table('documents d')
            ->left_join('users u', 'd.user_id = u.id')
            ->where('d.status', 'Pending Review'); 
        // ... (rest of logic omitted for brevity, but remains implemented as before)
        $order_by = ($sort === 'oldest') ? 'd.created_at ASC' : 'd.created_at DESC';
        return $this->db->order_by($order_by)->get_all();
    }

    public function getReviewHistory($query = '', $status = '') {
        $search_term = "%{$query}%";
        $this->db
            ->select('d.id, d.title, d.status, d.created_at, d.approved_at, d.rejected_at, u.fname AS reviewer_fname, u.lname AS reviewer_lname')
            ->table('documents d')
            ->left_join('users u', 'd.reviewer_id = u.id'); 
    
        if (!empty($status) && in_array($status, ['Approved', 'Rejected'])) {
            $this->db->where('d.status', $status);
        } else {
            $this->db->grouped(function($q) {
                $q->where('d.status', 'Approved')
                  ->or_where('d.status', 'Rejected');
            });
        }
        return $this->db->order_by('d.approved_at DESC, d.rejected_at DESC, d.created_at DESC')->get_all();
    }

    public function getReviewComments(int $doc_id) {
    return $this->db
        ->select('c.id, c.comment, c.created_at, u.fname, u.lname')
        ->table('comments c') 
        ->left_join('users u', 'c.user_id = u.id')
        ->where('c.document_id', $doc_id)
        ->order_by('c.created_at', 'ASC')
        ->get_all();
    }

    /**
     * CRITICAL METHOD: Fetches documents that are Approved, Rejected, OR have entries in the 'comments' table.
     */
    public function getDocumentsWithComments($query = '', $status = '') {
        $search_term = "%{$query}%";
        
        $this->db
            ->select('d.id, d.title, d.status, d.created_at, d.approved_at, d.rejected_at, d.reviewer_id, u.fname AS reviewer_fname, u.lname AS reviewer_lname')
            ->table('documents d')
            ->left_join('users u', 'd.reviewer_id = u.id');
            
        // --- START CONDITIONAL FILTERING ---
        if (!empty($status) && in_array($status, ['Approved', 'Rejected', 'Pending Review'])) {
            // Apply status filter based on selection
            $this->db->grouped(function($q) use ($status) {
                $q->where('d.status', $status);
                // CRITICAL FIX: If Pending Review is filtered, it MUST have a comment.
                if ($status === 'Pending Review') {
                    $q->where('EXISTS (SELECT 1 FROM comments c WHERE c.document_id = d.id)', null, false);
                }
            });
        } else {
            // DEFAULT: Show all Approved, Rejected, and any document with comments.
            $this->db->grouped(function($q) {
                $q->where('d.status', 'Approved')
                  ->or_where('d.status', 'Rejected')
                  // I-OR sa mga documents na may comments (Using OR EXISTS)
                  ->or_where('EXISTS (SELECT 1 FROM comments c WHERE c.document_id = d.id)', null, false);
            });
        }
        // --- END CONDITIONAL FILTERING ---
        
        // Apply search query filter (Title or Reviewer Name)
        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('d.title', $search_term) 
                  ->or_like('u.fname', $search_term)
                  ->or_like('u.lname', $search_term);
            });
        }

        return $this->db->order_by('d.approved_at DESC, d.rejected_at DESC, d.created_at DESC')->get_all();
    }

    public function insertComment(array $data) {
        $this->db->table('comments')->insert($data);
        return $this->db->last_id();
    }
}