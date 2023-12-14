<?php
if (isset($_GET['file'])) {
    $file = urldecode($_GET['file']);

    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain; charset=US-ASCII'); // Set Content-Type to text/plain and specify ASCII charset
        header('Content-Disposition: attachment; filename=' . basename($_GET['filename']));
        header('Content-Transfer-Encoding: UTF-8');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        ob_clean();
        flush();
        readfile($file);

        // Clean up: delete the temporary file
        unlink($file);

        exit;
    }
}
