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

        // 1. First Query: Get document data including user_id (NO JOIN)
        $this->db->select('d.id, d.title, d.type, d.status, d.file_name, d.description, d.created_at, d.user_id')
                 ->table('documents d');

        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('d.title', $search_term)
                  ->or_like('d.description', $search_term);
            });
        }

        if (!empty($status)) {
            $this->db->where('d.status', $status);
        } else {
            $this->db->where('d.status', '!=', 'Archived');
        }

        $docs = $this->db->order_by('d.created_at', 'DESC')->get_all();

        if (empty($docs)) {
            return [];
        }

        // 2. Collect all unique user IDs for batch lookup
        $user_ids = array_unique(array_column($docs, 'user_id'));

        // 3. Second Query: Fetch all required user names in one batch (NO JOIN)
        $users_data = $this->db->table('users')
                               ->select('id, fname, lname')
                               ->in('id', $user_ids) // FIX: Changed where_in to the correct in() method
                               ->get_all();

        // 4. Map user data for easy lookup [id => user_data]
        $users_lookup = [];
        foreach ($users_data as $user) {
            $users_lookup[$user['id']] = $user;
        }

        // 5. Merge user data into document records
        foreach ($docs as &$doc) {
            $user_id = $doc['user_id'];
            $user = $users_lookup[$user_id] ?? ['fname' => 'Unknown', 'lname' => 'User'];
            $doc['fname'] = $user['fname'];
            $doc['lname'] = $user['lname'];
            unset($doc['user_id']); 
        }
        unset($doc); 

        return $docs;
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
            $q->like('d.title', $search_term)
              ->or_like('u.fname', $search_term)
              ->or_like('u.lname', $search_term); 
        });
    }

    if (!empty($type)) {
        $this->db->where('d.type', $type);
    }
    
    return $this->db->order_by('d.created_at', 'DESC')->get_all();
}
    
    public function getRejectedDocuments($query = '', $type = '') { // <--- MODIFIED
        $search_term = "%{$query}%";
        
        $this->db
            ->select('d.id, d.title, d.created_at, d.description, d.file_name, d.type, u.fname AS reviewer_fname, u.lname AS reviewer_lname')
            ->table('documents d')
            ->left_join('users u', 'd.reviewer_id = u.id') 
            ->where('d.status', 'Rejected');

        // 1. Search Query Filter (Title or Reviewer Name)
        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('d.title', $search_term)
                  ->or_like('d.description', $search_term); 
            });
        }

        // 2. Type Filter
        if (!empty($type)) {
            $this->db->where('d.type', $type);
        }

        return $this->db->order_by('d.created_at', 'DESC')
            ->get_all();
    }
    
    // REMOVED: public function getArchivedDocumentsOnly(...) 

    public function getDocumentById(int $doc_id) {
        $query = "
            SELECT 
                d.*, 
                u.fname AS submitter_fname, 
                u.lname AS submitter_lname,
                u.email AS email
            FROM documents d
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.id = ?
            LIMIT 1
        ";
        $result = $this->db->raw($query, [(int)$doc_id])->fetch();
        return $result ?: null; 
    }

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

    /**
     * Deletes a document record and its physical file permanently.
     */
    public function deleteDocumentPermanently(int $doc_id) {
        $doc = $this->db->table('documents')->select('file_name')->where('id', $doc_id)->get();
        if (!$doc) return false;
        
        try {
            // Execute all three commands in sequence using raw() for immediate effect on the connection
            $this->db->raw('SET FOREIGN_KEY_CHECKS = 0');
            $stmt = $this->db->raw('DELETE FROM documents WHERE id = ?', [(int)$doc_id]);
            $this->db->raw('SET FOREIGN_KEY_CHECKS = 1'); 
            
            $rows_affected = $stmt->rowCount();

            if ($rows_affected > 0) {
                // Delete the physical file
                $file_name = $doc['file_name'];
                $file_path = ROOT_DIR . 'public/uploads/documents/' . $file_name;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
                return true;
            }
            return false;
            
        } catch (\Exception $e) {
            error_log("Permanent document deletion exception: " . $e->getMessage());
            // Attempt to re-enable foreign key checks on failure
            try {
                $this->db->raw('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Exception $e2) {
                // Ignore nested error
            }
            return false;
        }
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
    
    public function isRoleUniqueInDepartment(int $role_id, int $dept_id, int $member_id = 0) {
        $role_info = $this->db->table('roles')->select('name')->where('id', $role_id)->get();
        if (empty($role_info)) {
            return false;
        }
        $role_name = $role_info['name'];
        $unique_roles = ['Adviser', 'President', 'Secretary', 'Treasurer'];

        if (!in_array($role_name, $unique_roles)) {
            return false;
        }
        $query = "
            SELECT COUNT(*) AS count FROM users
            WHERE role_id = ? AND dept_id = ? AND id != ?
        ";

        $result = $this->db->raw($query, [$role_id, $dept_id, $member_id])->fetch();

        return ($result['count'] ?? 0) > 0;
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

    public function getDocumentsByDepartment(int $dept_id, $query = '') {
        $search_term = "%{$query}%";
        
        $this->db->select('d.id, d.title, d.type, d.status, d.file_name, d.review_comment, d.created_at, u.fname, u.lname')
                 ->table('documents d')
                 ->join('users u', 'd.user_id = u.id');

        $this->db->where('u.dept_id', $dept_id);

        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('d.title', $search_term)
                  ->or_like('d.description', $search_term); 
            });
        }
        
        // Filter out 'Archived' records permanently, similar to getAllDocuments
        $this->db->where('d.status', '!=', 'Archived');
        
        return $this->db->order_by('d.created_at', 'DESC')->get_all();
    }
}