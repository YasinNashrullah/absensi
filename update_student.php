<?php
require_once('db_connect.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

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

        header('Location: admin_dashboard.php');
        exit();
    }
} else {
    header('Location: admin_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Edit Student</h2>
    <form method="POST">
        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" value="<?php echo $student['nama']; ?>" required>
        <br>
        <label for="kelas">Kelas:</label>
        <input type="text" id="kelas" name="kelas" value="<?php echo $student['kelas']; ?>" required>
        <br>
        <input type="submit" value="Update">
    </form>
</body>
</html>
