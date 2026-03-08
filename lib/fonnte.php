<?php
/**
 * Fonnte WhatsApp API Wrapper
 * Handles sending WhatsApp messages via Fonnte.com API
 */

if (substr($_SERVER["REQUEST_URI"], -10) == "fonnte.php") {
    header("Location:../");
    exit;
}

class Fonnte {
    private $token;
    private $apiUrl = 'https://api.fonnte.com/send';

    /**
     * Constructor
     * @param string $token Fonnte API token
     */
    public function __construct($token = '') {
        if (empty($token)) {
            require_once(__DIR__ . '/../database/database.php');
            $this->token = Database::getSetting('fonnte_token', '');
        } else {
            $this->token = $token;
        }
    }

    /**
     * Send a WhatsApp message
     * @param string $phone Target phone number
     * @param string $message Message content
     * @return array ['success' => bool, 'response' => string]
     */
    public function sendMessage($phone, $message) {
        if (empty($this->token)) {
            return ['success' => false, 'response' => 'Token Fonnte belum dikonfigurasi.'];
        }
        if (empty($phone)) {
            return ['success' => false, 'response' => 'Nomor telepon tidak boleh kosong.'];
        }

        // Normalize phone number (remove spaces, dashes)
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        // Convert 08xx to 628xx
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $phone,
                'message' => $message,
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token,
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['success' => false, 'response' => 'cURL Error: ' . $error];
        }

        $result = json_decode($response, true);
        $success = ($httpCode == 200 && isset($result['status']) && $result['status'] == true);
        
        return [
            'success' => $success,
            'response' => $response,
            'detail' => $result['detail'] ?? ($result['reason'] ?? 'Unknown response'),
        ];
    }

    /**
     * Format a template with variable substitution
     * @param string $template Template string with {variables}
     * @param array $data Key-value pairs for substitution
     * @return string Formatted message
     */
    public static function formatTemplate($template, $data) {
        $replacements = [
            '{username}'       => $data['username'] ?? '',
            '{password}'       => $data['password'] ?? '',
            '{profile}'        => $data['profile'] ?? '',
            '{price}'          => $data['price'] ?? '',
            '{agent_name}'     => $data['agent_name'] ?? '',
            '{customer_phone}' => $data['customer_phone'] ?? '',
            '{session}'        => $data['session'] ?? '',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Check if token is configured
     * @return bool
     */
    public function isConfigured() {
        return !empty($this->token);
    }
}
