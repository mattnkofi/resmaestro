<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Csv_generator {

    public function download(array $data, string $filename = 'export.csv') {
        if (empty($data)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            echo "No data available";
            exit;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        
        $output = fopen('php://output', 'w');

        fputcsv($output, array_keys($data[0]));

        // Write data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}