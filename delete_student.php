<?php
require_once('db_connect.php');

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    // Hapus siswa dari tabel students
    $delete_student_query = "DELETE FROM students WHERE no = $student_id";
    if (mysqli_query($conn, $delete_student_query)) {
        // Setelah menghapus siswa, panggil fungsi untuk memperbarui nomor urut
        updateStudentNumbers($conn);

        // Redirect kembali ke halaman admin
        header('Location: admin_dashboard.php');
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

function updateStudentNumbers($conn) {
    // Ambil semua nomor urut dari tabel students
    $query = "SELECT no FROM students ORDER BY no";
    $result = mysqli_query($conn, $query);
    $counter = 1;

    $conn->begin_transaction(); // Mulai transaksi untuk memastikan konsistensi data

    try {
        // Perbarui setiap nomor urut
        while ($row = mysqli_fetch_assoc($result)) {
            $update_query = "UPDATE students SET no = $counter WHERE no = " . $row['no'];
            mysqli_query($conn, $update_query);
            $counter++;
        }

        $conn->commit(); // Commit transaksi jika semua operasi berhasil
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaksi jika terjadi kesalahan
        throw $e;
    }
}
?>
