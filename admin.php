if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}