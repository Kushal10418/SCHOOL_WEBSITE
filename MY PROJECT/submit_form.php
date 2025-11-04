<?php
require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;


$studentName = filter_input(INPUT_POST, 'studentName', FILTER_SANITIZE_STRING);
$gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
$dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);
$currentClass = filter_input(INPUT_POST, 'currentClass', FILTER_SANITIZE_STRING);
$parentName = filter_input(INPUT_POST, 'parentName', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_STRING);
$addar =filter_input(INPUT_POST,'addar',FILTER_SANITIZE_STRING);

$uploadDir = 'uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}


$studentPhotoPath = '';
if (isset($_FILES['studentPhoto']) && $_FILES['studentPhoto']['error'] === UPLOAD_ERR_OK) {
    $photoTmpPath = $_FILES['studentPhoto']['tmp_name'];
    $photoName = basename($_FILES['studentPhoto']['name']);
    $photoSize = $_FILES['studentPhoto']['size'];
    $photoType = $_FILES['studentPhoto']['type'];

    
    if (($photoType === 'image/jpeg' || $photoType === 'image/pjpeg') && $photoSize <= 100 * 1024) {
        // Create unique file name
        $photoExt = pathinfo($photoName, PATHINFO_EXTENSION);
        $newPhotoName = 'photo_' . time() . '_' . uniqid() . '.' . $photoExt;
        $photoDest = $uploadDir . $newPhotoName;
        move_uploaded_file($photoTmpPath, $photoDest);
        $studentPhotoPath = $photoDest; // Relative path saved
    } else {
        // Handle invalid photo upload (you can customize error handling)
        echo"<script>alert('Invalid student photo. It must be an image and max size 100KB Please upload again.');
        window.location.href='STUDENT ADMISON.PHP';</script>";
        exit;
    }
} else {
    echo"<script>alert('Student photo upload failed.');
    window.location.href='STUDENT ADMISON.PHP';</script>";
    exit;
}

// Handling student signature upload
$studentSignaturePath = '';
if (isset($_FILES['studentSignature']) && $_FILES['studentSignature']['error'] === UPLOAD_ERR_OK) {
    $signTmpPath = $_FILES['studentSignature']['tmp_name'];
    $signName = basename($_FILES['studentSignature']['name']);
    $signSize = $_FILES['studentSignature']['size'];
    $signType = $_FILES['studentSignature']['type'];

    // Validate size max 50KB, allow any image type
    $allowedSignTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
    if (in_array($signType, $allowedSignTypes) && $signSize <= 50 * 1024) {
        $signExt = pathinfo($signName, PATHINFO_EXTENSION);
        $newSignName = 'signature_' . time() . '_' . uniqid() . '.' . $signExt;
        $signDest = $uploadDir . $newSignName;
        move_uploaded_file($signTmpPath, $signDest);
        $studentSignaturePath = $signDest; // Relative path saved
    } else {
        echo"<script>alert('Invalid student signature. It must be an image and max size 50KB Please upload again.');
        window.location.href='STUDENT ADMISON.PHP';</script>";
        exit;
    }
} else {
    echo"<script>alert('Student signature upload failed.');
    window.location.href='STUDENT ADMISON.PHP';</script>";
    exit;
}

// Excel file path
$file = 'admissions_data.xlsx';

// Load existing Excel or create new
if (file_exists($file)) {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
} else {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Student Admission');

    $headers=['Sl.No','Student Name','Gender','Date Of Birth','Current Class','Parent Name','Email','Mobile','Student Addhaer','Student Photo','Student Signature'];
    $col='A';
    foreach($headers as $header){
        $sheet->setCellValue($col.'1',$header);
        $sheet->getStyle($col.'1')->getFont()->setBold(true);
        $sheet->getStyle($col.'1')->getAlignment()->setHorizontal('center');
        $col++;
    }

    $sheet->getRowDimension(1)->setRowHeight(28);
}

    
$row = $sheet->getHighestRow() + 1;

$serialNumber=$row-1;
$sheet->setCellValue("A{$row}",$serialNumber);
$sheet->setCellValue("B{$row}", $studentName);
$sheet->setCellValue("C{$row}", $gender);
$sheet->setCellValue("D{$row}", $dob);
$sheet->setCellValue("E{$row}", $currentClass);
$sheet->setCellValue("F{$row}", $parentName);
$sheet->setCellValue("G{$row}", $email);
$sheet->setCellValue("H{$row}", $mobile);
$sheet->setCellValue("I{$row}",$addar);


$sheet->getRowDimension($row)->setRowHeight(80);
$sheet->getColumnDimension('A')->setWidth(7);
$sheet->getColumnDimension('B')->setWidth(19);
$sheet->getColumnDimension('F')->setWidth(25);
$sheet->getColumnDimension('G')->setWidth(25);
$sheet->getColumnDimension('J')->setWidth(19);
$sheet->getColumnDimension('K')->setWidth(19);
$sheet->getColumnDimension('I')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(20);

if(file_exists($studentPhotoPath)){
    $photo=new Drawing();
    $photo->setName('Student Photo');
    $photo->setDescription('Photo');

    $photo->setPath($studentPhotoPath);
    $photo->setHeight(70);
    $photo->setCoordinates('J'.$row);
    $photo->setOffsetX(10);
    $photo->setOffsetY(8);
    $photo->setWorksheet($sheet);
}

if
(file_exists($studentSignaturePath)){
    $sign= new Drawing();
    $sign->setName('Student Signature');
    $sign->setDescription('Signature');

    $sign->setPath($studentSignaturePath);
    $sign->setHeight(50);
    $sign->setCoordinates('K'.$row);
    $sign->setOffsetX(10);
    $sign->setOffsetY(10);
    $sign->setWorksheet($sheet);
}

$sheet->getStyle("A{$row}:K{$row}")->getAlignment()->setVertical('center');
$sheet->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal('center');

$sheet->setShowGridlines(false);

$pageSetup=$sheet->getPageSetup();
$pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$pageSetup->setPaperSize(PageSetup::PAPERSIZE_A4);
$pageSetup->setFitToWidth(1);
$pageSetup->setFitToHeight(0);

$margins=$sheet->getPageMargins();
$margins->setTop(0.4);
$margins->setBottom(0.4);
$margins->setLeft(0.4);
$margins->setRight(0.4);

$sheet->getStyle("A1:K{$row}")
->getBorders()
->getAllBorders()

->setBorderStyle(Border::BORDER_THIN);


 // Save signature path

// Save the Excel file
$writer = new Xlsx($spreadsheet);

$writer->save($file);

// Redirect to thank you page after successful submission
header('Location: Thankyou.php');
exit();


?>