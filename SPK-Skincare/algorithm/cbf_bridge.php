<?php
require_once '../config/database.php';

class ManajerRekomendasi {
    private $db;
    private $python_path;
    
    public function __construct($db) {
        $this->db = $db;

        // === PATH PYTHON EXPLICIT (TANPA REALPATH) ===
        $this->python_path = "C:\\laragon\\www\\SPK-Skincare\\venv\\Scripts\\python.exe";

        if (!file_exists($this->python_path)) {
            die("<pre><b>ERROR:</b> Python tidak ditemukan di: {$this->python_path}</pre>");
        }
    }
    
    public function rekomendasi($user_id, $profile_id) {

        $input_data = [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'skincare_recommendation',
            'user_id' => $user_id,
            'profile_id' => $profile_id
        ];

        $result = $this->runPythonScript($input_data);

        if ($result && isset($result['success']) && $result['success']) {
            return $result['recommendation_id'];
        }

        return false;
    }
    
    private function runPythonScript($input_data) {

        // === PATH SCRIPT PYTHON TANPA REALPATH ===
        $script_path = __DIR__ . '\\cbf.py';

        if (!file_exists($script_path)) {
            die("<pre><b>ERROR:</b> cbf.py tidak ditemukan di: {$script_path}</pre>");
        }

        // === COMMAND PYTHON FIX WINDOWS ===
        $cmd = "\"{$this->python_path}\" \"{$script_path}\"";

        $descriptorspec = [
            0 => ["pipe", "r"],   // STDIN
            1 => ["pipe", "w"],   // STDOUT
            2 => ["pipe", "w"]    // STDERR
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            die("<pre><b>ERROR:</b> Tidak dapat menjalankan Python.</pre>");
        }

        // Kirim JSON ke python
        fwrite($pipes[0], json_encode($input_data));
        fclose($pipes[0]);

        // Ambil output
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        // Ambil error Python
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exit_code = proc_close($process);

        // Tampilkan error Python
        if (!empty($error)) {
            echo "<pre style='color:red'><b>PYTHON ERROR:</b>\n$error</pre>";
        }

        // Tampilkan output Python
        echo "<pre style='color:blue'><b>PYTHON OUTPUT:</b>\n$output</pre>";

        if ($exit_code !== 0) {
            echo "<pre><b>PYTHON EXIT CODE:</b> {$exit_code}</pre>";
        }

        // Decode JSON
        return json_decode($output, true);
    }
}
?>
