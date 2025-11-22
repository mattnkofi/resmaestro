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
        $stats['pending_reviews']      = $this->db->raw($pending_query, ['Pending Review'])->fetch()['count'];
        
        $approved_query = "SELECT COUNT(*) AS count FROM documents WHERE status = ?";
        $stats['approved_documents'] = $this->db->raw($approved_query, ['Approved'])->fetch()['count'];
        
        $new_members_query = "SELECT COUNT(*) AS count FROM users WHERE created_at >= ?";
        $stats['new_members']          = $this->db->raw($new_members_query, [$seven_days_ago])->fetch()['count'];
        
        return $stats;
    }

    public function getAllDocuments($query = '', $status = '') {
        $search_term = "%{$query}%";
        
        $this->db->select('d.id, d.title, d.type, d.status, d.file_name')
                 ->table('documents d');
    
        if (!empty($query)) {
            $where = "(d.title LIKE ? OR d.description LIKE ?)";
            $this->db->where($where, [$search_term, $search_term], false); 
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
            $where = "(d.title LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)";
            $this->db->where($where, [$search_term, $search_term, $search_term], false);
        }
    
        if (!empty($type)) {
            $this->db->where('d.type', $type);
        }
        return $this->db->order_by('d.created_at', 'ASC')->get_all();
    }

    public function getApprovedDocuments($query = '', $type = '') {
    $search_term = "%{$query}%"; 
    
    $this->db
        ->select('d.id, d.title, d.file_name, d.status, d.created_at, d.type, u.fname AS approver_fname, u.lname AS approver_lname')
        ->table('documents d')
        ->left_join('users u', 'd.reviewer_id = u.id') 
        ->where('d.status', 'Approved');

    if (!empty($query)) {
        $where = "(d.title LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)";
        $this->db->where($where, [$search_term, $search_term, $search_term], false);
    }

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
            $where = "(d.title LIKE ? OR d.description LIKE ?)";
            $this->db->where($where, [$search_term, $search_term], false); 
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
    
    // ----------------------------------------------------------------------
    //  MEMBER UPDATE METHODS
    // ----------------------------------------------------------------------

    /**
     * Updates a user record in the database.
     */
    public function updateMember(int $member_id, array $data) {
        return $this->db->table('users')
                           ->where('id', $member_id)
                           ->update($data);
    }

    /**
     * Fetches the Role ID by Role Name.
     */
    public function getRoleIdByName(string $role_name) {
        return $this->db->table('roles')
                           ->select('id')
                           ->where('name', $role_name)
                           ->get(); 
    }

    /**
     * Fetches the Department ID by Department Name.
     */
    public function getDepartmentIdByName(string $dept_name) {
        return $this->db->table('departments')
                           ->select('id')
                           ->where('name', $dept_name)
                           ->get(); 
    }
    
    /**
     * Fetches a member record by their email address.
     */
    public function getMemberByEmail(string $email) {
        return $this->db->table('users')
                         ->where('email', $email)
                         ->get(); 
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
    //  ORGANIZATION IMPLEMENTATION
    // ----------------------------------------------------------------------

    public function getMembers($q = null, $role_slug = null) {
        $query = $this->db->table('users')
            ->select('users.id, users.fname, users.lname, users.email, users.dept_id, users.role_id, departments.name as dept_name, roles.name as role_name')
            ->left_join('departments', 'users.dept_id = departments.id')
            ->left_join('roles', 'users.role_id = roles.id');
        
        if (!empty($q)) {
            $search_term = "%{$q}%";
            $where_clause = "(users.fname LIKE ? OR users.lname LIKE ? OR users.email LIKE ?)";
            $query->where($where_clause, [$search_term, $search_term, $search_term], false); 
        }

        if (!empty($role_slug)) {
            $role_name_from_slug = ucwords(str_replace('_', ' ', $role_slug));
            $query->where('roles.name', $role_name_from_slug);
        }
        
        return $query->order_by('users.fname', 'ASC')
            ->get_all();
    }


    public function assignMembersToDepartment(int $dept_id, array $member_ids) {
        if (empty($member_ids)) {
            return true; // Nothing to do
        }
        
        return $this->db->table('users')
                           ->where_in('id', $member_ids)
                           ->update(['dept_id' => $dept_id]);
    }
    
    /**
     * Unassigns all members from a specific department (sets dept_id to NULL).
     */
    public function unassignMembersFromDepartment(int $dept_id) {
        return $this->db->table('users')
                         ->where('dept_id', $dept_id)
                         ->update(['dept_id' => NULL]);
    }


    /**
     * Fetches department statistics (Member count is fixed via robust JOIN).
     */
    public function getDepartmentsWithStats() {
    $query = "
        SELECT
            d.id,
            d.name,
            COUNT(u.id) AS members_count,
            0 AS documents_count,  
            0 AS pending_count     
        FROM departments d
        LEFT JOIN users u ON u.dept_id = d.id
        GROUP BY d.id, d.name
        ORDER BY d.name ASC
    ";
    
    try {
        $stmt = $this->db->raw($query);
        return $stmt->fetchAll(2);
    } catch (\Exception $e) {
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


    public function getMembersByDepartment(int $dept_id) {
        $query = "
            SELECT id, fname, lname, email
            FROM users
            WHERE dept_id = ?
            ORDER BY lname ASC
        ";

        try {
            $stmt = $this->db->raw($query, [(int)$dept_id]);
            return $stmt->fetchAll(2); 
        } catch (\Exception $e) {
            error_log("Database error in getMembersByDepartment: " . $e->getMessage());
            return [];
        }
    }

    public function getMemberById(int $id) {
        return $this->db->table('users')
                         ->where('id', $id)
                         ->get(); // Using get() for a single result
    }

    public function getDepartmentById(int $dept_id) {
        return $this->db->table('departments')
                        ->where('id', $dept_id)
                        ->get(); 
    }

    public function getDepartments() { 
    return $this->getDepartmentOptions();
}

    public function isDepartmentNameDuplicate(string $name, int $current_id) {
    
    $query = "SELECT COUNT(*) AS count 
              FROM departments 
              WHERE name = ? AND id != ?";

    $result = $this->db->raw($query, [$name, $current_id])->fetch();
                      
    return ($result['count'] ?? 0) > 0;
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

    public function insertDepartment(array $data) {
        $this->db->table('departments')->insert($data);
        return $this->db->last_id();
    }

    public function updateDepartment(int $dept_id, array $data) {
        return $this->db->table('departments')
                        ->where('id', $dept_id)
                        ->update($data);
    }
    
    public function deleteDepartment(int $dept_id) {
        return $this->db->table('departments')
                        ->where('id', $dept_id)
                        ->delete();
    }
    
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

    public function deleteMember($id) {
    return $this->db->table('users')
        ->where('id', (int)$id)
        ->delete();
}
    
    public function getPendingReviews($query = '', $sort = 'oldest') {
        $search_term = "%{$query}%";
        $this->db
            ->select('d.id, d.title, d.status, d.created_at, d.type, d.file_name, u.fname AS submitter_fname, u.lname AS submitter_lname')
            ->table('documents d')
            ->left_join('users u', 'd.user_id = u.id')
            ->where('d.status', 'Pending Review'); 
            
        if (!empty($query)) {
            $where = "(d.title LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)";
            $this->db->where($where, [$search_term, $search_term, $search_term], false);
        }
        
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
            $where = "(d.status = 'Approved' OR d.status = 'Rejected')";
            $this->db->where($where, null, false);
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


    public function getDocumentsWithComments($query = '', $status = '') {
        $search_term = "%{$query}%";
        
        $this->db
            ->select('d.id, d.title, d.status, d.created_at, d.approved_at, d.rejected_at, d.reviewer_id, u.fname AS reviewer_fname, u.lname AS reviewer_lname')
            ->table('documents d')
            ->left_join('users u', 'd.reviewer_id = u.id');
            
        if (!empty($status) && in_array($status, ['Approved', 'Rejected', 'Pending Review'])) {
            $where = "d.status = ?";
            $params = [$status];

            if ($status === 'Pending Review') {
                $where .= " AND EXISTS (SELECT 1 FROM comments c WHERE c.document_id = d.id)";
            }
            $this->db->where($where, $params, false);
        } else {
            $where = "(d.status = 'Approved' OR d.status = 'Rejected' OR EXISTS (SELECT 1 FROM comments c WHERE c.document_id = d.id))";
            $this->db->where($where, null, false);
        }

        if (!empty($query)) {
            $where_search = "(d.title LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)";
            $this->db->where($where_search, [$search_term, $search_term, $search_term], false);
        }

        return $this->db->order_by('d.approved_at DESC, d.rejected_at DESC, d.created_at DESC')->get_all();
    }

    public function insertComment(array $data) {
        $this->db->table('comments')->insert($data);
        return $this->db->last_id();
    }
}