
<?php
session_start();
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $ville = $_POST['ville'];
    $email = $_POST['email'];
    $mdp = $_POST['mdp'] ;
    $filiere_id = $_POST['filiere_id'];
    $niveau_id = $_POST['niveau_id'];
    
    // Traitement du fichier d'image
    $file_name = $_FILES['photo']['name'];
    $file_tmp = $_FILES['photo']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // Obtenir l'extension du fichier
    $extensions = array("jpeg", "jpg", "png");

    // Vérifier si l'extension du fichier est autorisée
    if (!in_array($file_ext, $extensions)) {
        echo "Extension de fichier non autorisée, veuillez choisir une image JPEG ou PNG.";
        exit();
    }

    $file_name = uniqid('', true) . "." . $file_ext;
    $file_destination = "../les_images/" . $file_name;

    // Déplacer le fichier téléchargé vers un emplacement sur le serveur
    if (!move_uploaded_file($file_tmp, $file_destination)) {
        echo "Une erreur s'est produite lors du téléchargement du fichier.";
        exit();
    }

    // Connexion à la base de données
    $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt_utilisateur = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mdp, role, ville, img) VALUES (:nom, :prenom, :email, :mdp, "etudiant", :ville, :img)');
$stmt_utilisateur->execute(['nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'mdp' => $mdp, 'ville' => $ville, 'img' => $file_name]);

    $id_ut = $pdo->lastInsertId();
    $stmt_etudiant = $pdo->prepare("INSERT INTO etudiants (id_ut, id_f, id_n) VALUES (:id_ut, :id_f, :id_n)");
    $stmt_etudiant->execute(['id_ut' => $id_ut, 'id_f' =>$filiere_id, 'id_n' =>  $niveau_id]);

    // Redirection vers une page de confirmation ou autre action
    header("Location: ../fichier_administrateur.php");
    exit();
}
?>
<?php
// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email']) || !isset($_SESSION['password'])) {
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit();
}

// Récupérer l'email et le mot de passe de la session
$email = $_SESSION['email'];
$password = $_SESSION['password'];

try {
    $sql_img = $pdo->prepare('SELECT u.nom , u.prenom , u.email , u.ville ,u.img , u.role  FROM utilisateurs u  WHERE email = :email AND mdp = :password');
    $sql_img->execute(array('email' => $email, 'password' => $password));
$admin = $sql_img->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération de l'image : " . $e->getMessage();
    exit();
}
?>