<?php
require 'vendor/autoload.php';
require_once('db_connect.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query untuk mendapatkan semua data absensi
$query = "SELECT a.no, a.nama_id, s.kelas, a.waktu, a.status
          FROM attendance a
          JOIN students s ON a.student_id = s.no
          ORDER BY s.kelas, s.nama";
$result = mysqli_query($conn, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Menambahkan header kolom
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Nama');
$sheet->setCellValue('C1', 'Kelas');
$sheet->setCellValue('D1', 'Waktu');
$sheet->setCellValue('E1', 'Status');

$rowNum = 2;
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowNum, $row['no']);
    $sheet->setCellValue('B' . $rowNum, $row['nama_id']);
    $sheet->setCellValue('C' . $rowNum, $row['kelas']);
    $sheet->setCellValue('D' . $rowNum, $row['waktu']);
    $sheet->setCellValue('E' . $rowNum, $row['status']);
    $rowNum++;
}

$writer = new Xlsx($spreadsheet);
$filename = 'Data_Absensi.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit();
