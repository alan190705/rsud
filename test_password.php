<?php

$password = 'admin123';

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "<br><br>";
echo "Hash baru:<br>";
echo "<textarea style='width:600px;height:100px;'>" . htmlspecialchars($hash) . "</textarea>";