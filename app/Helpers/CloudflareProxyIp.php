<?php

// Trusted CF IPs should be filtered by reverse proxy
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}
