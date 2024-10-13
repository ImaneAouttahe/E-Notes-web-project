
<?php
session_start(); // Démarrez la session (si ce n'est pas déjà fait)

try {
    // Connexion à la base de données personnelle
    $pdo_enote = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
    $pdo_enote->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Gestion des erreurs

    // Récupérez les valeurs de l'utilisateur (email et mot de passe)
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];

    $stmt_id1 = $pdo_enote->prepare('SELECT f.nom_f , f.description
    FROM filieres f 
    WHERE f.id_d = (SELECT chef_d.id_d FROM chef_d WHERE chef_d.id_ut = (SELECT id_ut FROM utilisateurs WHERE utilisateurs.email = :email AND utilisateurs.mdp = :password))');
// Exécuter la requête avec les valeurs fournies
$stmt_id1->execute(['email' => $email, 'password' => $password]);
    
    // Récupérer les résultats
    $results = $stmt_id1->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
<?php
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
<?php

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
   table.custom-table {
        width: 100%;
        background: linear-gradient(180deg, #2FDF84 0%, #8944D7 100%);
        border-collapse: collapse;
        box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7; /* Ajout de l'ombre pour simuler la bordure dégradée */
    }
    table.custom-table th, table.custom-table td {
        border: 1px solid;
        padding: 8px;
        text-align: center;
        border-collapse: collapse;
        box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7;
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
<a href="../fichier_chefsdepartements.php">View all Notifications</a>
</div>
</div>
</li>
<li class="nav-item dropdown d-none d-sm-block">
<a href="javascript:void(0);" id="open_msg_box" class="hasnotifications nav-link"><img src="assets/img/sidebar/icon-23.png" alt=""> </a>
</li>
 <li class="nav-item dropdown has-arrow">
<a href="#" class=" nav-link user-link" data-toggle="dropdown">
<span class="user-img"><img class="rounded-circle" src="<?= $img ?>" width="30" alt="chef"></span>
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
<a href="../fichier_chefsdepartements.php"><img src="assets/img/sidebar/icon-1.png" alt="icon"><span>Dashboard</span></a>
</li>
<li >
<a href="profes_d.php"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> Profs departement</span></a>
</li>
<li >
<a href="#"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> filieres dep</span></a>
</li>
<li>
<a href="modules_d.php"><img src="assets/img/sidebar/icon-5.png" alt="icon"> <span>Modules dep</span></a>
</li>
<li>
<a href="consulter_emploi.php"><img src="assets/img/sidebar/icon-18.png" alt="icon"> <span>Emplois de temps</span></a>
</li>
<li>
<a href="affect_mod.php"><img src="assets/img/sidebar/icon-14.png" alt="icon"> <span>Affectation modules</span></a>
</li>
<li>
<a href="arch/archivage.php"><img src="assets/img/sidebar/icon-8.png" alt="icon"> <span>archivage</span></a>
</li>
<li>
<a href="matieres_affect.php"><img src="assets/img/sidebar/icon-10.png" alt="icon"> <span>p.Matières affectées</span></a>
</li>
<li>
<a href="liste_etu.php"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span>p.Listes Etudiants</span></a>
</li>
<li>
<a href="inserer_notes.php"><img src="assets/img/sidebar/icon-12.png" alt="icon"> <span>p.Insertion des Notes</span></a>
</li>
<li>
<a href="#"><img src="assets/img/sidebar/icon-26.png" alt="icon"> <span>p.Consulter Notes</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="notes_ds.php"><span>Notes DS</span></a></li>
<li><a href="notes_examen.php"><span>Notes Examen</span></a></li>
<li><a href="moyenne.php"><span>Moyenne</span></a></li>
</ul>
</li>
<li>
<a href="modifier.php"><img src="assets/img/sidebar/icon-7.png" alt="icon"> <span>p.Modifier Notes</span></a>
</li>
</div>
</div>
</div>
<div class="page-wrapper">
<div class="content container-fluid">

<div class="page-header">
<div class="row">
<div class="col-md-6">
<h3 class="page-title mb-0">filieres de departement</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="../fichier_chefsdepartements.php"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item"><span>Dashboard</span></li>
</ul>
</div>
</div>
</div>




<div >
<table class="custom-table">
        <thead>
            <tr>
                <th>Nom filiere</th>
                <th>description</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($results)) {
                foreach ($results as $row) {
                    echo "<tr>";
                    echo "<td>" . $row['nom_f'] . "</td>";
                    echo "<td>" . $row['description'] . "</td>"; 
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>Aucun résultat trouvé.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>













<div class="notification-box">
<div class="msg-sidebar notifications msg-noti">
<div class="topnav-dropdown-header">
<span>Messages</span>
</div>
<div class="topnav-dropdown-footer">
<a href="#">See all messages</a>
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
