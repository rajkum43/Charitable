<?php
// Membership Eligibility Validator
// यह file सदस्य की eligibility check करती है

class MembershipValidator {
    
    /**
     * Check if member is eligible for a specific scheme
     * @param string $created_at - Member creation date (database timestamp)
     * @param string $scheme - Scheme name (e.g., 'beti_vivah_aavedan', 'death_aavedan')
     * @return array - ['eligible' => bool, 'message' => string, 'days_remaining' => int]
     */
    public static function checkEligibility($created_at, $scheme = 'beti_vivah_aavedan') {
        try {
            // Load configuration
            $config = require __DIR__ . '/../config/membership_requirements.php';
            
            // Get required days for this scheme
            $required_days = $config[$scheme] ?? 365;
            
            // Calculate days since registration
            $registration_date = new DateTime($created_at);
            $today = new DateTime();
            $interval = $today->diff($registration_date);
            $days_passed = $interval->days;
            $days_remaining = $required_days - $days_passed;
            
            if ($days_passed >= $required_days) {
                return [
                    'eligible' => true,
                    'message' => 'सदस्य eligible है',
                    'days_passed' => $days_passed,
                    'days_remaining' => 0,
                    'requirement_days' => $required_days
                ];
            } else {
                return [
                    'eligible' => false,
                    'message' => "आवेदन के लिए सदस्य को कम से कम $required_days दिन से सदस्य होना चाहिए। आप $days_remaining दिन बाद आवेदन कर सकते हैं",
                    'days_passed' => $days_passed,
                    'days_remaining' => $days_remaining,
                    'requirement_days' => $required_days
                ];
            }
        } catch (Exception $e) {
            return [
                'eligible' => false,
                'message' => 'Date validation में त्रुटि: ' . $e->getMessage(),
                'days_passed' => 0,
                'days_remaining' => 365,
                'requirement_days' => 365
            ];
        }
    }
    
    /**
     * Get all membership requirements
     * @return array
     */
    public static function getAllRequirements() {
        return require __DIR__ . '/../config/membership_requirements.php';
    }
    
    /**
     * Update requirement (Admin function)
     * @param string $scheme
     * @param int $days
     * @return bool
     */
    public static function updateRequirement($scheme, $days) {
        if ($days < 0 || $days > 3650) {
            return false; // Invalid range
        }
        
        $config = require __DIR__ . '/../config/membership_requirements.php';
        $config[$scheme] = (int) $days;
        
        $config_content = "<?php\n// Membership Duration Configuration\n// यह file membership requirements को define करती है\n\nreturn " . var_export($config, true) . ";\n?>";
        
        $file_path = __DIR__ . '/../config/membership_requirements.php';
        return file_put_contents($file_path, $config_content) !== false;
    }
}
?>
