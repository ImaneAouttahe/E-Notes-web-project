<?php
// Vérifier si les données POST existent
if(isset($_POST["email"]) && isset($_POST["password"])) {
    // Récupérer les valeurs du formulaire
    $email = $_POST["email"];
    $password = $_POST["password"];
    try {
        // Connexion à la base de données avec PDO (recommandé)
        $pdo_utilisateurs = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');

        // Préparer la requête SQL avec des paramètres
        $stmt_1 = $pdo_utilisateurs->prepare('SELECT * FROM utilisateurs WHERE email = :email AND mdp = :password');

        // Exécuter la requête avec les valeurs fournies
        $stmt_1->execute(array('email' => $email, 'password' => $password));

        session_start(); // Démarrez la session (si ce n'est pas déjà fait)
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        // Vérifier si l'utilisateur existe dans une des tables
        if ($user = $stmt_1->fetch()) {
            $stmt_2 = $pdo_utilisateurs->prepare('SELECT role FROM utilisateurs WHERE email = :email AND mdp = :password');
            $stmt_2->execute(array('email' => $email, 'password' => $password));
            $role = $stmt_2->fetchColumn(); // Récupérer le rôle de l'utilisateur

            if ($role === "chef_departement") {
                header("Location: fichier_chefsdepartements.php");
                exit();
            } else if ($role === "cordonnateur") {
                header("Location: fichier_coordinateursfiliers.php");
                exit();
            }else if ($role === "professeur") {
                header("Location: fichier_professeurs.php");
                exit();
            }else if ($role === "etudiant") {
                header("Location: fichier_etudiant.php");
                exit();
            }else if ($role === "directeur") {
                header("Location: fichier_administrateur.php");
                exit();
            }

        }  else {
            $error = "Mot de passe ou email incorectes";
        }
        
    } catch (PDOException $e) {
        // Gestion des erreurs de connexion à la base de données
        echo "Database connection failed: " . $e->getMessage();
    }
}
?>