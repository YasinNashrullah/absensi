<?php
// Set session lifetime (example: 1 year)
ini_set('session.gc_maxlifetime', 31536000); // 60 * 60 * 24 * 365 (1 year)
session_set_cookie_params(31536000); // Set cookie lifetime to match session

session_start();
require_once('db_connect.php');

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Logout handler
if (isset($_POST['logout'])) {
    session_unset();     // Menghapus semua variabel sesi
    session_destroy();   // Menghancurkan sesi
    header('Location: awal.php'); // Redirect ke halaman login setelah logout
    exit();
}

$all_students = [];

// Query untuk semua siswa
$query_all = "SELECT * FROM students ORDER BY kelas, nama";
$result_all = mysqli_query($conn, $query_all);

while ($row = mysqli_fetch_assoc($result_all)) {
    $all_students[] = $row;
}

// Menambahkan siswa baru
if (isset($_POST['add_student'])) {
    $nama = $_POST['nama'];
    $kelas_utama = $_POST['kelas_utama'];
    $kelas_detail = $_POST['kelas_detail'];

    // Query untuk memasukkan data siswa baru
    $query_insert = "INSERT INTO students (nama, kelas) VALUES ('$nama', '$kelas_utama $kelas_detail')";
    if (mysqli_query($conn, $query_insert)) {
        $_SESSION['notification'] = 'Siswa berhasil ditambahkan!';
    } else {
        $_SESSION['notification'] = 'Gagal menambahkan siswa!';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Menambahkan absensi siswa
if (isset($_POST['mark_attendance'])) {
    $student_id = $_POST['student_id'];
    $nama_id = $_POST['nama_id'];
    $waktu = date('Y-m-d H:i:s'); // Waktu sekarang
    $status = 'Hadir';

    $query = "INSERT INTO attendance (student_id, nama_id, waktu, status) VALUES ('$student_id', '$nama_id', '$waktu', '$status')";
    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = 'Absensi berhasil ditambahkan!';
    } else {
        $_SESSION['notification'] = 'Gagal menambahkan absensi!';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Check for notifications
$notification = '';
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Coach Dashboard</title>
    <link rel="stylesheet" type="text/css" href="coach.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        #notification {
            display: none;
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            z-index: 9999;
        }

        #notification .close {
            cursor: pointer;
            float: right;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>Coach Dashboard</h2>
    <div id="notification">
        <span class="close" onclick="this.parentNode.style.display='none';">&times;</span>
        <span id="notification_message"><?php echo $notification; ?></span>
    </div>
    <form class="logout" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="submit" name="logout" value="Logout">
    </form>
    <h3>Tambah Siswa</h3>
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label>Nama Siswa:</label>
        <input type="text" name="nama" class="pjg" required>
        <label>Kelas:</label>
        <select name="kelas_utama" required>
            <option value="10E">10E</option>
            <option value="11F">11F</option>
            <option value="12G">12G</option>
        </select>
        <label>Fase:</label>
        <input class="kelasD" type="text" name="kelas_detail" required><br>
        <input type="submit" name="add_student" value="Tambah Siswa">
    </form>

    <h3>Cari Siswa</h3>
    <input type="text" id="searchInput" placeholder="Cari Nama atau Kelas">

    <h3>Daftar Siswa</h3>
    <table id="studentsTable">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Absen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_students as $row) { ?>
                <tr>
                    <td class="nama"><?php echo $row['nama']; ?></td>
                    <td class="kelas"><?php echo $row['kelas']; ?></td>
                    <td class="aksi">
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="markAttendanceForm">
                            <input type="hidden" name="student_id" value="<?php echo $row['no']; ?>">
                            <input type="hidden" name="nama_id" value="<?php echo $row['nama']; ?>">
                            <input type="submit" class="add" name="mark_attendance" value="Add">
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            // Show notification if message is present
            var notificationMessage = $('#notification_message').text();
            if (notificationMessage.trim() !== '') {
                $('#notification').fadeIn().delay(5000).fadeOut();
            }

            // Search functionality
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#studentsTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });

        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>

</html>
