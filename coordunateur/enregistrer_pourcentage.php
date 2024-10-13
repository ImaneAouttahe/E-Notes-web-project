<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email']) || !isset($_SESSION['password'])) {
    exit();
}

// Vérifie si la nouvelle valeur du pourcentage a été envoyée via POST
if (isset($_POST['newPourcentage'], $_POST['niveau_id'], $_POST['module_id'], $_POST['type_epreuve'])) {
    // Nouvelle valeur du pourcentage
    $newPourcentage = $_POST['newPourcentage'];
    $niveau_id = $_POST['niveau_id'];
    $module_id = $_POST['module_id'];
    $type_epreuve = $_POST['type_epreuve'];

    // Connexion à la base de données
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
        exit();
    }

    // Mettre à jour le pourcentage dans la base de données
    $stmt_update_pourcentage = $pdo->prepare('UPDATE notes SET pourcentage = :newPourcentage WHERE id_n = :niveau_id AND id_m = :module_id AND type_epreuve = :type_epreuve');
    $stmt_update_pourcentage->execute(['newPourcentage' => $newPourcentage, 'niveau_id' => $niveau_id, 'module_id' => $module_id, 'type_epreuve' => $type_epreuve]);

    // Réponse de succès
    echo "Pourcentage mis à jour avec succès !";
} else {
    // Si la nouvelle valeur du pourcentage n'est pas définie dans POST
    echo "Erreur : Nouveau pourcentage non spécifié ou données manquantes.";
}
?>