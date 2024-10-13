<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'enotee';

try {
    // Establish a connection to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the request contains base64 PDF data and necessary metadata
        if (isset($_POST['pdfBase64'], $_POST['filename'], $_POST['professeur_id'])) {
            $pdfBase64 = $_POST['pdfBase64'];
            $filename = $_POST['filename'];
            $professeur_id = $_POST['professeur_id'];

            // Decode the PDF from the base64 string
            $pdfContent = base64_decode(explode(',', $pdfBase64)[1]);

            // Insert the PDF file information into the database
            $query = "INSERT INTO fichiers_pdf (id_ut, nom_fichier, contenu_pdf, date_creation) VALUES (:id_prof, :nom_fichier, :contenu_pdf, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':id_prof' => $professeur_id,
                ':nom_fichier' => $filename,
                ':contenu_pdf' => $pdfContent
            ]);

            echo json_encode(['success' => true, 'message' => 'Fichier PDF sauvegardé avec succès.']);
        }
        // Check if the request contains a file upload
        elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'emploi//'; // Replace with your directory
            $uploadFile = $uploadDir . basename($_FILES['file']['name']);

            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
                echo json_encode(['success' => true, 'message' => 'File successfully uploaded.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    }
} catch (PDOException $e) {
    // Handle potential errors here
    die(json_encode(['success' => false, 'message' => 'Could not connect to the database: ' . $e->getMessage()]));
}
?>