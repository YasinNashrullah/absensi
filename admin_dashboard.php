<?php
// Set session lifetime (example: 1 year)
ini_set('session.gc_maxlifetime', 31536000); // 60 * 60 * 24 * 365 (1 year)
session_set_cookie_params(31536000); // Set cookie lifetime to match session

session_start();
require_once('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Logout handler
if (isset($_POST['logout'])) {
    session_unset();     // Menghapus semua variabel sesi
    session_destroy();   // Menghancurkan sesi
    header('Location: awal.php'); // Redirect ke halaman login setelah logout
    exit();
}

$search_query = '';
$search_results = [];
$data_students = [];
$all_students = [];

// Query untuk semua siswa
$query_all = "SELECT * FROM students ORDER BY no, kelas, nama";
$result_all = mysqli_query($conn, $query_all);

while ($row = mysqli_fetch_assoc($result_all)) {
    $data_students[] = $row;
}

// Query untuk semua siswa
$query_all = "SELECT a.no, s.nama, s.kelas, a.waktu, a.status
              FROM attendance a
              JOIN students s ON a.student_id = s.no
              ORDER BY a.waktu, s.kelas, s.nama";
$result_all = mysqli_query($conn, $query_all);

while ($row = mysqli_fetch_assoc($result_all)) {
    $all_students[] = $row;
}

// Query untuk pencarian siswa
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
    $query_search = "SELECT a.no, s.nama, s.kelas, a.waktu, a.status
                     FROM attendance a
                     JOIN students s ON a.student_id = s.no
                     WHERE s.nama LIKE '%$search_query%' OR s.kelas LIKE '%$search_query%'
                     ORDER BY a.waktu, s.kelas, s.nama";
    $result_search = mysqli_query($conn, $query_search);

    while ($row = mysqli_fetch_assoc($result_search)) {
        $search_results[] = $row;
    }
}

// edit student
$student = null;
if (isset($_GET['edit_student'])) {
    $id = $_GET['edit_student'];

    // Ambil data siswa berdasarkan ID
    $query = "SELECT * FROM students WHERE no = $id";
    $result = mysqli_query($conn, $query);
    $student = mysqli_fetch_assoc($result);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = $_POST['nama'];
        $kelas = $_POST['kelas'];

        // Update data siswa
        $query_update = "UPDATE students SET nama = '$nama', kelas = '$kelas' WHERE no = $id";
        mysqli_query($conn, $query_update);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// delete student
if (isset($_GET['delete_student'])) {
    $id = $_GET['delete_student'];

    // Hapus siswa berdasarkan ID
    $query_delete = "DELETE FROM students WHERE no = $id";
    mysqli_query($conn, $query_delete);

    // Reset ulang ID siswa
    $query_reset = "SET @count = 0";
    mysqli_query($conn, $query_reset);
    $query_reset = "UPDATE students SET no = @count:= @count + 1";
    mysqli_query($conn, $query_reset);
    $query_reset = "ALTER TABLE students AUTO_INCREMENT = 1";
    mysqli_query($conn, $query_reset);

    $query_reset = "SET @count = 0";
    mysqli_query($conn, $query_reset);
    $query_reset = "UPDATE attendance SET no = @count:= @count + 1";
    mysqli_query($conn, $query_reset);
    $query_reset = "ALTER TABLE attendance AUTO_INCREMENT = 1";
    mysqli_query($conn, $query_reset);

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    <h2>Admin Dashboard</h2>

    <div class="nav">
        <form method="POST" action="export_excel.php">
            <input type="submit" class="excl" value="Unduh Data Absensi">
        </form>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="submit" class="excl" name="logout" value="Logout">
        </form>
    </div>

    <h3>Cari Siswa</h3>
    <input type="text" id="searchInput" placeholder="Cari Nama atau Kelas">

    <table id="studentsTable">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th style="width: 50px;">Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_students as $row) { ?>
                <tr>
                    <td class="no"><?php echo $row['no']; ?></td>
                    <td class="nama"><?php echo $row['nama']; ?></td>
                    <td class="kelas"><?php echo $row['kelas']; ?></td>
                    <td class="edit">
                        <a href="?edit_student=<?php echo $row['no']; ?>">Edit</a>
                    </td>
                    <td class="delete">
                        <a href="?delete_student=<?php echo $row['no']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <?php if ($student) { ?>
            <div class="array">
                <form method="POST">
                    <label for="nama">Nama:</label>
                    <input type="text" id="nama" name="nama" value="<?php echo $student['nama']; ?>" required>
                    <label for="kelas">Kelas:</label>
                    <input type="text" id="kelas" name="kelas" value="<?php echo $student['kelas']; ?>" required>
                    <input type="submit" value="Update">
                </form>
            </div>
        <?php } ?>
    </table>


    <h3>Daftar Siswa</h3>
    <table id="studentstable">
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
                    <td><?php echo $row['nama']; ?></td>
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