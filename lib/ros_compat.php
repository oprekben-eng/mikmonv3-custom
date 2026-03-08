<?php
/**
 * RouterOS Compatibility Helper
 * Ensures Mikhmon works seamlessly on both RouterOS v6 and v7
 * 
 * Key differences handled:
 * - Date format: v6 uses "jan/01/2025", v7 uses "2025-01-01"
 * - Some response fields may be absent in certain versions
 * - Version string parsing
 */

/**
 * Safely get a value from an array with a default fallback.
 * Prevents "undefined index" errors when API response fields differ between v6/v7.
 *
 * @param array  $array   The response array
 * @param string $key     The key to look for
 * @param mixed  $default Default value if key doesn't exist
 * @return mixed
 */
function safeGet($array, $key, $default = '-') {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Extract major version number from RouterOS version string.
 * Examples: "6.49.14 (stable)" => 6, "7.16 (stable)" => 7
 *
 * @param string $versionString Full version string from /system/resource
 * @return int Major version (6 or 7)
 */
function getROSMajorVersion($versionString) {
    if (preg_match('/^(\d+)/', $versionString, $matches)) {
        return (int) $matches[1];
    }
    return 6; // default fallback to v6
}

/**
 * Normalize RouterOS date string to a consistent PHP-parseable format.
 * 
 * v6 format: "jan/01/2025" or "mar/15/2025"
 * v7 format: "2025-01-01" or "2025-03-15"
 * 
 * @param string $dateString Raw date from /system/clock/print
 * @return string Normalized date in "Y-m-d" format, or original if unrecognized
 */
function normalizeROSDate($dateString) {
    $dateString = trim($dateString);
    
    // Already in v7 ISO format (2025-01-01)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
        return $dateString;
    }
    
    // v6 format: "jan/01/2025" or "january/01/2025"
    $monthMap = [
        'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
        'may' => '05', 'jun' => '06', 'jul' => '07', 'aug' => '08',
        'sep' => '09', 'oct' => '10', 'nov' => '11', 'dec' => '12'
    ];
    
    if (preg_match('/^([a-z]+)\/(\d{2})\/(\d{4})$/i', $dateString, $m)) {
        $monthKey = strtolower(substr($m[1], 0, 3));
        if (isset($monthMap[$monthKey])) {
            return $m[3] . '-' . $monthMap[$monthKey] . '-' . $m[2];
        }
    }
    
    // Fallback: return as-is
    return $dateString;
}

/**
 * Format a RouterOS date for display, handling both v6 and v7 formats.
 * Output: "01 Jan 2025" style
 *
 * @param string $dateString Raw date from router
 * @return string Human-readable date
 */
function formatROSDate($dateString) {
    $normalized = normalizeROSDate($dateString);
    $timestamp = strtotime($normalized);
    if ($timestamp !== false) {
        return date('d M Y', $timestamp);
    }
    return $dateString; // fallback
}

/**
 * Safe bytes formatter - handles missing or non-numeric values gracefully
 *
 * @param mixed $bytes    Byte value from API
 * @param int   $decimals Number of decimal places
 * @return string Formatted string
 */
function safeFormatBytes($bytes, $decimals = 2) {
    if (!is_numeric($bytes) || $bytes <= 0) return '0 B';
    return formatBytes($bytes, $decimals);
}
?>
