<?php
session_start();
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
    // Requête pour récupérer le nom de l'image de l'utilisateur connecté
    $sql_imgg = $pdo->prepare('SELECT img FROM utilisateurs WHERE email = :email AND mdp = :password');
    $sql_imgg->execute(array('email' => $email, 'password' => $password));
    $img_name = $sql_imgg->fetchColumn(); // Récupérer seulement le nom de l'image
    $img = "../les_images/" . $img_name; // Construire l'URL complète de l'image
} catch (PDOException $e) {
    echo "Erreur lors de la récupération de l'image : " . $e->getMessage();
    exit();
}
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $specialite = $_POST['specialite'];
    $ville = $_POST['ville'];
    $email = $_POST['email'];
    $mdp = $_POST['mdp'] ;
    
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

    // Insertion dans la table utilisateurs
    $stmt_utilisateur = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mdp , role ,ville , img) VALUES (:nom, :prenom, :email, :mdp ,"professeur",:ville , :img)');
    $stmt_utilisateur->execute(['nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'mdp' => $mdp ,'ville' => $ville , 'img' => $file_name]);
    $id_utilisateur = $pdo->lastInsertId(); // Récupération de l'ID de l'utilisateur inséré

    // Détermination de l'ID du département en fonction de la spécialité
    $id_departement = ($specialite == 'Mathematique' || $specialite == 'Informatique') ? 1 : 2;

    // Insertion dans la table professeurs
    $stmt_professeur = $pdo->prepare('INSERT INTO profes (id_ut, specialite, id_d) VALUES (:id_ut, :specialite, :id_d)');
    $stmt_professeur->execute(['id_ut' => $id_utilisateur, 'specialite' => $specialite, 'id_d' => $id_departement]);

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


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>ENOTES </title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">

<link href="../../../../css?family=Roboto:300,400,500,700,900" rel="stylesheet">

<link rel="stylesheet" href="assets/css/bootstrap.min.css">

<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">

<link rel="stylesheet" href="assets/css/fullcalendar.min.css">

<link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">

<link rel="stylesheet" href="assets/plugins/morris/morris.css">

<link rel="stylesheet" href="assets/css/style.css">
<!--[if lt IE 9]>
    <script src="assets/js/html5shiv.min.js"></script>
    <script src="assets/js/respond.min.js"></script>
  <![endif]-->



  <style>
    /* Styles pour le formulaire */
    .form-horizontal {
        margin-top: 20px;
        border: 1px solid;
        box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7;
    }

    /* Styles pour les étiquettes */
    label {
        font-weight: bold;
        color: #fff;
        background: linear-gradient(180deg, #2FDF84 0%, #8944D7 100%);
        border-radius: 10px;
    }

    /* Styles pour les champs de texte */
    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
        width: 100%;
        margin-bottom: 10px;
    }

    /* Styles pour les boutons */
    .btn {
        color: #fff;
        background: linear-gradient(180deg, #2FDF84 0%, #8944D7 100%);
        border-radius: 10px;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
    }

    /* Styles pour les options présentes */
    #options_container {
        margin-top: 10px;
    }

    /* Styles pour les modules */
    .form-group.modules {
        margin-bottom: 20px;
    }

    .module-label {
        font-weight: bold;
        margin-bottom: 10px;
        border: 1px solid;
        box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7;
    }

    .module-input {
        width: 100%;
        margin-bottom: 10px;
    }
</style>

</head>
<body>

<div class="main-wrapper">

<div class="header-outer">
<div class="header">
<a id="mobile_btn" class="mobile_btn float-left" href="#sidebar"><i class="fas fa-bars" aria-hidden="true"></i></a>
<a id="toggle_btn" class="float-left" href="javascript:void(0);">
<img src="assets/img/sidebar/icon-21.png" alt="">
</a>

<ul class="nav float-left">
<li>
<div class="top-nav-search">
<a href="javascript:void(0);" class="responsive-search">
<i class="fa fa-search"></i>
</a>
<form action="inbox.html">
<input class="form-control" type="text" placeholder="Search here">
<button class="btn" type="submit"><i class="fa fa-search"></i></button>
</form>
</div>
</li>
<li>
<a href="#" class="mobile-logo d-md-block d-lg-none d-block"><img src="assets/img/logo1.png" alt="" width="30" height="30"></a>
</li>
</ul>

<ul class="nav user-menu float-right">
<li class="nav-item dropdown d-none d-sm-block">
<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
<img src="assets/img/sidebar/icon-22.png" alt="">
</a>
<div class="dropdown-menu notifications">
<div class="topnav-dropdown-header">
<span>Notifications</span>
</div>
<div class="topnav-dropdown-footer">
<a href="#">View all Notifications</a>
</div>
</div>
</li>
<li class="nav-item dropdown d-none d-sm-block">
<a href="javascript:void(0);" id="open_msg_box" class="hasnotifications nav-link"><img src="assets/img/sidebar/icon-23.png" alt=""> </a>
</li>
 <li class="nav-item dropdown has-arrow">
<a href="#" class=" nav-link user-link" data-toggle="dropdown">
<span class="user-img"><img class="rounded-circle" src="<?= $img ?>" width="30" alt="admin"></span>
<span class="status online"></span></span>
<span><?php echo $admin['nom'] ; ?></span>
</a>
<div class="dropdown-menu">
<a class="dropdown-item" href="profile.php">mon Profile</a>
<a class="dropdown-item" href="../index2.php">Logout</a>
</div>
</li>
</ul>
</div>
</div>


<div class="sidebar" id="sidebar">
<div class="sidebar-inner slimscroll">
<div id="sidebar-menu" class="sidebar-menu">
<div class="header-left">
<a href="#" class="logo">
<img src="assets/img/logo1.png" width="40" height="40" alt="">
<span class="text-uppercase">ENOTES</span>
</a>
</div>
<ul class="sidebar-ul">
<li class="menu-title">Menu</li>
<li class="active">
<a href="../fichier_administrateur.php"><img src="assets/img/sidebar/icon-1.png" alt="icon"><span>Dashboard</span></a>
</li>
<li class="submenu">
<a href="#"><img src="assets/img/sidebar/icon-2.png" alt="icon"> <span> Professeurs</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="consulter_profes.php"><span>consulter </span></a></li>
<li><a href="#"><span>ajouter Professeur</span></a></li>
<li><a href="#"><span>modifier Professeur</span></a></li>
</ul>
</li>
<li class="submenu">
<a href="#"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> Etudiants</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="consulter_etudiants.php"><span>consulter Etudiants</span></a></li>
<li><a href="ajouter_etud.php"><span>ajouter Etudiant</span></a></li>
<li><a href="#"><span>modifier Etudiant</span></a></li>
</ul>
</li>
<li class="submenu">
<a href="#"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> coordinateurs</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="consulter_coor.php"><span>consulter </span></a></li>
<li><a href="affecter_coor.php"><span>affecter </span></a></li>
<li><a href="#"><span>modifier </span></a></li>
</ul>
</li>
<li class="submenu">
<a href="#"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> Chefs</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="consulter_chefs.php"><span>consulter Chefs</span></a></li>
<li><a href="affecter_chef.php"><span>affecter Chef</span></a></li>
<li><a href="#"><span>modifier Chef</span></a></li>
</ul>
</li>
<li class="submenu">
<a href="#"><img src="assets/img/sidebar/icon-20.png" alt="icon"> <span> filieres</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="creerfiliere.php"><span>creer filiere</span></a></li>
<li><a href="consulter_f.php"><span>consulter filieres</span></a></li>
</ul>
</li>

</ul>
</div>
</div>
</div>


<div class="page-wrapper">
<div class="content container-fluid">

<div class="page-header">
<div class="row">
<div class="col-md-6">
<h3 class="page-title mb-0">Ajouter professeur</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="../fichier_administrateur.php"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item"><span>Dashboard</span></li>
</ul>
</div>
</div>
</div>




<div class="center-block col-md-12">
    <form class="form-horizontal" method="post" action="#" enctype="multipart/form-data">
            <div class="col-md-12 col-md-offset-1">
                <div class="form-group">
                    <p></p>
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" placeholder="Entrez votre nom">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" class="form-control" placeholder="Entrez votre prénom">
                </div>
                <div class="form-group">
                    <label>Spécialité</label>
                    <input type="text" name="specialite" class="form-control" placeholder="Entrez votre spécialité">
                </div>
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="ville" class="form-control" placeholder="Entrez votre  ville">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Entrez votre email">
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="mdp" class="form-control" placeholder="Entrez votre mot de passe">
                </div>
                <div class="form-group">
                    <label for="exampleInputFile">Ajouter une photo</label>
                    <input type="file" name="photo" id="exampleInputFile">
                    <p class="help-block">Ajouter une photo au format .jpg ou .png</p>
                    <button type="submit" name="direction9" class="btn btn-default">Ajouter</button>
                </div>
            </div>
        </form>
    </div>



<div class="notification-box">
<div class="msg-sidebar notifications msg-noti">
<div class="topnav-dropdown-header">
<span>Messages</span>
</div>
<div class="topnav-dropdown-footer">
<a href="#">See all messages</a>
</div>
</div>
</div>
</div>
</div>

</div>


<script src="assets/js/jquery-3.6.0.min.js"></script>

<script src="assets/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/jquery.slimscroll.js"></script>
 
<script src="assets/js/select2.min.js"></script>
<script src="assets/js/moment.min.js"></script>

<script src="assets/js/fullcalendar.min.js"></script>
<script src="assets/js/jquery.fullcalendar.js"></script>

<script src="assets/plugins/morris/morris.min.js"></script>
<script src="assets/plugins/raphael/raphael-min.js"></script>
<script src="assets/js/apexcharts.js"></script>
<script src="assets/js/chart-data.js"></script>

<script src="assets/js/app.js"></script>
</body>
</html>
