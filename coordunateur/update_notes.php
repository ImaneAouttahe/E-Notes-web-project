<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email']) || !isset($_SESSION['password'])) {
    exit('Utilisateur non connecté');
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Récupérer l'ID du module
$module_id = isset($_POST['module_id']) ? $_POST['module_id'] : null;

if ($module_id) {
    try {
        // Mettre à jour l'état des notes pour ce module
        $stmt = $pdo->prepare('UPDATE notes SET etat2 = "envoyer" WHERE id_m = :module_id');
        $stmt->execute(['module_id' => $module_id]);

        echo 'Les notes ont été mises à jour avec succès';
    } catch (PDOException $e) {
        echo 'Erreur lors de la mise à jour des notes : ' . $e->getMessage();
    }
} else {
    echo 'ID du module non fourni';
}
?>
