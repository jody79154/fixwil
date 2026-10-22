<?php
// Retain the authenticated student endpoint while sharing public knowledge rules.
require_once __DIR__ . '/../includes/auth.inc.php';
require_role('student');
require __DIR__ . '/../chatbot.php';
