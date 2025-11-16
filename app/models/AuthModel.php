<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthModel extends Model {

    protected $table = 'users'; 

    public function __construct()
    {
        parent::__construct();
        // The call to database is usually handled by the parent Model class in this framework,
        // but keeping it here for explicit loading if needed.
        // $this->call->database(); 
    }

    /**
     * Checks if a user exists based on given conditions (e.g., email or username).
     * @param array $conditions
     * @param bool $with_deleted If set to true, includes soft-deleted records.
     * @return bool
     */
    public function exists($conditions = [], $with_deleted = false)
    {
        // Use where_group to handle multiple conditions gracefully if needed,
        // but a simple where() works for single conditions.
        $result = $this->db->table($this->table)->where($conditions)->get();
        // Since get() usually returns a single row array, !empty($result) is correct.
        return !empty($result); 
    }

    /**
     * Finds a user by email verification token.
     * NOTE: Using 'email_verification_token' as per the Controller logic.
     /**
     * Finds a user by email verification token.
     * @param string $token
     * @return object|false
     */
        public function find_by_token($token) {
            $result = $this->db->table($this->table)
                            ->where('email_verification_token', $token)
                            ->limit(1)
                            ->get();

        // The framework's get() returns a single row array, convert to object or return false
        return (is_array($result) && !empty($result)) ? (object)$result : false; 
    }

    /**
     * Inserts a new user into the database.
     * Automatically hashes the password.
     * @param array $data Must contain a 'password' field.
     * @return int|bool The new user ID or false on failure
     */
    public function insert_user($data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Insert the user
        $success = $this->db->table($this->table)->insert($data); 
        
        // Return the ID of the newly inserted user only on success
        return $success ? $this->db->last_id() : false;
    }

    /**
     * Updates the user's status to verified and clears the token.
     * NOTE: Now accepting $user_id as per Controller refactor.
     /**
     * Updates the user's status to verified and clears the token.
     * @param int $user_id
     * @return bool
     */
    public function verify_email($user_id) {
        return $this->db->table($this->table)
                        ->where('id', $user_id)
                        ->update([
                            'email_verified' => 1, 
                            'email_verification_token' => NULL
                        ]);
    }
    
    /**
     * Fetches a user by username or email for the login process.
     * @param string $identifier The username or email provided by the user.
     * @return object|false The user object if found, otherwise false.
     */
    public function get_user_by_username_or_email($identifier)
    {
        // Use grouped() for a single query (more efficient)
        $result = $this->db->table($this->table)
                         ->grouped(function($q) use ($identifier) {
                             $q->where('email', $identifier)
                               ->or_where('username', $identifier);
                         })
                         ->limit(1)
                         ->get();
        
        // If a single array is returned for a single row:
        return (is_array($result) && !empty($result)) ? (object)$result : false;
        
        // If the framework returns an array of rows:
        // return (is_array($result) && isset($result[0])) ? (object)$result[0] : false;
    }

    // --- Added from the first model for completeness ---

    /**
     * Find a user by their email
     *
     * @param string $email
     * @return object|null
     */
    public function find_by_email($email)
    {
        $result = $this->db->table($this->table)->where('email', $email)->limit(1)->get();
        return (is_array($result) && !empty($result)) ? (object)$result : null;
    }

    /**
     * Check if user is an admin
     *
     * @param int $user_id
     * @return bool
     */
    public function is_admin($user_id)
    {
        $user = $this->db->table($this->table)->where('id', $user_id)->get();
        // Assuming your controller expects an array result or an object from $this->db->get()
        $role = is_array($user) && isset($user['role']) ? $user['role'] : (is_object($user) && isset($user->role) ? $user->role : null);
        return ($user && strtolower($role) === 'admin');
    }
    

    /**
     * Get paginated user records with search
     *
     * @param string $query Search term
     * @param int $limit Records per page
     * @param int $page Current page number
     * @return array Associative array with 'records' and 'total_rows'
     */
    public function page($query = '', $limit = 10, $page = 1)
    {
        $offset = ($page > 0) ? ($page - 1) * $limit : 0;
        $search_term = "%{$query}%"; 

        // --- Get Total Count ---
        $this->db->table($this->table);
        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('email', $search_term)
                  ->or_like('first_name', $search_term)
                  ->or_like('last_name', $search_term)
                  ->or_like('role', $search_term);
            });
        }
        $total = $this->db->count();

        // --- Get Paginated Records ---
        $this->db->table($this->table); // Reset query
        if (!empty($query)) {
            $this->db->grouped(function($q) use ($search_term) {
                $q->like('email', $search_term)
                  ->or_like('first_name', $search_term)
                  ->or_like('last_name', $search_term)
                  ->or_like('role', $search_term);
            });
        }

        // Apply limit and offset
        $records = $this->db->limit($offset, $limit)->get_all();

        return [
            'records' => $records,
            'total_rows' => $total
        ];
    }
}