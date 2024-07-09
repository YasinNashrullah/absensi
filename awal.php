<?php
session_start();
require_once('db_connect.php');

$all_students = [];

// Query untuk semua siswa
$query_all = "SELECT a.no, a.nama_id, s.kelas, a.waktu, a.status
              FROM attendance a
              JOIN students s ON a.student_id = s.no
              ORDER BY a.waktu, s.kelas, a.nama_id";
$result_all = mysqli_query($conn, $query_all);

while ($row = mysqli_fetch_assoc($result_all)) {
    $all_students[] = $row;
}

$search_query = '';
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
    $query = "SELECT a.no, s.nama, s.kelas, a.waktu, a.status
              FROM attendance a
              JOIN students s ON a.student_id = s.no
              WHERE s.nama LIKE '%$search_query%' OR s.kelas LIKE '%$search_query%'
              ORDER BY a.waktu, s.kelas, s.nama";
} else {
    $query = "SELECT a.no, s.nama, s.kelas, a.waktu, a.status
              FROM attendance a
              JOIN students s ON a.student_id = s.no
              ORDER BY a.waktu, s.kelas, s.nama";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    <h2>Dashboard</h2>
    <div class="tamp">
        <input type="text" id="searchInput" placeholder="Cari Nama/Kelas">
        <a href="login.php" class="login">Login</a>
    </div>

    <table id="studentsTable">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Waktu</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($all_students as $row) { ?>
                <tr>
                    <td class="no"><?php echo $row['no']; ?></td>
                    <td><?php echo $row['nama_id']; ?></td>
                    <td class="kelas"><?php echo $row['kelas']; ?></td>
                    <td class="waktu"><?php echo $row['waktu']; ?></td>
                    <td class="status"><?php echo $row['status']; ?></td>
                </tr>
            <?php } ?>

        </tbody>
    </table>
    <script>
        $(document).ready(function() {
            // Search functionality
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#studentsTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>

</html>