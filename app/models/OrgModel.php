<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class OrgModel
{
    public function getAllDocuments() {
        // example static data
        return [
            ['title'=>'Project Proposal 2025', 'type'=>'PDF', 'status'=>'Approved'],
            ['title'=>'Budget Q3', 'type'=>'XLSX', 'status'=>'Pending'],
        ];
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

    public function getMembers() {
        return [
            ['name'=>'John Doe','role'=>'Admin'],
            ['name'=>'Jane Smith','role'=>'Member']
        ];
    }

    public function getDepartments() { return ['HR','IT','Finance']; }
    public function getRoles()       { return ['Admin','Member','Reviewer']; }
    public function getPendingReviews() { return []; }
    public function getReviewHistory()  { return []; }
    public function getComments()       { return []; }
}
