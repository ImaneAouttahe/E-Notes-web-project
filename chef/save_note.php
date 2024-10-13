<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email']) || !isset($_SESSION['password'])) {
    // Redirigez l'utilisateur vers une page de connexion s'il n'est pas connecté
    header('Location: ../index2.php');
    exit();
}

// Vérifiez si des données ont été soumises via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérez les données du formulaire
    $id_niveau = $_POST['id_n'];
    $id_module = $_POST['id_m'];
    $id_etudiant = $_POST['id_et'];
    $type_epreuve = $_POST['type_e'];
    $pourcentage = $_POST['pourcentage'];
    $note = $_POST['note'];
    $action = $_POST['action'];

    // Connexion à la base de données
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // En cas d'erreur de connexion, affichez un message d'erreur et quittez le script
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
        exit();
    }

    $stmt_insert = $pdo->prepare('INSERT INTO notes (id_et, id_n, id_m, type_epreuve, pourcentage, note, etat , id_ut) VALUES (:id_et, :id_n, :id_m, :type_epreuve, :pourcentage, :note, :etat , :id_ut)');
    $stmt_update = $pdo->prepare('UPDATE notes SET note = :note, etat = :etat WHERE id_et = :id_et AND id_n = :id_n AND id_m = :id_m AND type_epreuve = :type_epreuve AND id_ut = :id_ut');
    $etat = ($action === 'done') ? 'done' : 'enregistrer';

    // Parcourez les données et insérez-les dans la base de données
    foreach ($id_etudiant as $index => $id) {
        // Vérifiez si une note a été saisie pour cet étudiant
        if (!empty($note[$index])) {
            // Récupérez l'id_utilisateur correspondant à l'étudiant
            $stmt_get_user = $pdo->prepare('SELECT id_ut FROM etudiants WHERE id_et = :id_et');
            $stmt_get_user->execute(['id_et' => $id]);
            $id_utilisateur = $stmt_get_user->fetchColumn();
            $stmt_check_note = $pdo->prepare('SELECT COUNT(*) FROM notes WHERE id_et = :id_et AND id_n = :id_n AND id_m = :id_m AND type_epreuve = :type_epreuve AND id_ut = :id_ut');
            $stmt_check_note->execute([
                'id_et' => $id,
                'id_n' => $id_niveau,
                'id_m' => $id_module,
                'type_epreuve' => $type_epreuve,
                'id_ut' => $id_utilisateur
            ]);
            $existing_note_count = $stmt_check_note->fetchColumn();
    
            if ($existing_note_count > 0) {
                // Mettre à jour la note existante
                $stmt_update->execute([
                    'id_et' => $id,
                    'id_n' => $id_niveau,
                    'id_m' => $id_module,
                    'type_epreuve' => $type_epreuve,
                    'note' => $note[$index],
                    'etat' => $etat,
                    'id_ut' => $id_utilisateur
                ]);
            } else {
                // Insérez la nouvelle note
                $stmt_insert->execute([
                    'id_et' => $id,
                    'id_n' => $id_niveau,
                    'id_m' => $id_module,
                    'type_epreuve' => $type_epreuve,
                    'pourcentage' => $pourcentage,
                    'note' => $note[$index],
                    'etat' => $etat,
                    'id_ut' => $id_utilisateur
                ]);
            }
        }
    }

    // Redirigez l'utilisateur vers une page de confirmation ou une autre page appropriée après l'insertion
    header('Location: inserer_notes.php');
    exit();
} else {
    // Si les données n'ont pas été soumises via POST, redirigez l'utilisateur ou affichez un message d'erreur
    header('Location: inserer_notes.php'); // Redirigez vers la page précédente
    exit();
}
?>