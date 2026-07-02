<?php
function formatDate($date, $format = 'M d, Y h:i A') {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;
    return date($format, $timestamp);
}

function timeAgo($timestamp) {
    if (empty($timestamp)) return '';
    $diff = time() - strtotime($timestamp);
    if ($diff < 0) return 'in the future';
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    if ($diff < 2592000) return floor($diff/604800) . 'w ago';
    if ($diff < 31536000) return floor($diff/2592000) . 'mo ago';
    return floor($diff/31536000) . 'y ago';
}

function getStatusBadge($status) {
    $map = [
        'pending' => 'badge-pending',
        'assigned' => 'badge-assigned',
        'in_progress' => 'badge-in_progress',
        'completed' => 'badge-completed',
        'resolved' => 'badge-resolved',
        'cancelled' => 'badge-cancelled',
        'rejected' => 'badge-rejected',
        'open' => 'badge-open',
        'planned' => 'badge-planned',
        'missed' => 'badge-missed',
    ];
    return $map[$status] ?? 'badge-pending';
}
?>