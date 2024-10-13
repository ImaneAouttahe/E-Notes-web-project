<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email']) || !isset($_SESSION['password'])) {
    exit('Vous devez être connecté pour effectuer cette action.');
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Récupérer les données envoyées par AJAX
$newNote = isset($_POST['newNote']) ? $_POST['newNote'] : null;
$idNote = isset($_POST['idNote']) ? $_POST['idNote'] : null;

// Vérifier si toutes les données nécessaires sont présentes
if ($newNote !== null && $idNote !== null) {
    // Mettre à jour la note de l'étudiant dans la base de données
    $stmt = $pdo->prepare('UPDATE notes SET note = :newNote WHERE id_note = :idNote');
    $stmt->execute(['newNote' => $newNote, 'idNote' => $idNote]);
    exit('Note mise à jour avec succès.');
} else {
    exit('Toutes les données nécessaires n\'ont pas été fournies.');
}
?>
