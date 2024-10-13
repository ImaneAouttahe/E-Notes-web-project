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
    $sql_img = $pdo->prepare('SELECT img FROM utilisateurs WHERE email = :email AND mdp = :password');
    $sql_img->execute(array('email' => $email, 'password' => $password));
    $img_name = $sql_img->fetchColumn(); // Récupérer seulement le nom de l'image
    $img = "les_images/" . $img_name; // Construire l'URL complète de l'image
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
    $sql_img = $pdo->prepare('SELECT u.nom , u.prenom , u.email , u.ville ,u.img , u.role , p.specialite , d.nom_d , f.nom_f FROM utilisateurs u JOIN profes p ON u.id_ut = p.id_ut JOIN departements d ON p.id_d = d.id_d JOIN cordonnateur c ON u.id_ut = c.id_ut JOIN filieres f ON c.id_f = f.id_f WHERE email = :email AND mdp = :password');
    $sql_img->execute(array('email' => $email, 'password' => $password));
$admin = $sql_img->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération de l'image : " . $e->getMessage();
    exit();
}try {
    $sql = "SELECT COUNT(*) as count FROM utilisateurs WHERE role='etudiant'";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $student_count = $row['count'];
} catch (PDOException $e) {
    echo "Erreur lors de la récupération du nombre d'étudiants : " . $e->getMessage();
    exit();
}try {
    $sql = "SELECT COUNT(*) as count FROM profes";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $professor_count = $row['count'];
} catch (PDOException $e) {
    echo "Erreur lors de la récupération du nombre de professeurs : " . $e->getMessage();
    exit();
}
try {
    $sql = "SELECT COUNT(*) as count FROM filieres";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $filiere_count = $row['count'];
} catch (PDOException $e) {
    echo "Erreur lors de la récupération du nombre de filières : " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>ENOTES </title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

<link rel="shortcut icon" type="image/x-icon" href="assetss/img/favicon.png">

<link href="../../../../css?family=Roboto:300,400,500,700,900" rel="stylesheet">

<link rel="stylesheet" href="assetss/css/bootstrap.min.css">

<link rel="stylesheet" href="assetss/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="assetss/plugins/fontawesome/css/fontawesome.min.css">

<link rel="stylesheet" href="assetss/css/fullcalendar.min.css">

<link rel="stylesheet" href="assetss/css/dataTables.bootstrap4.min.css">

<link rel="stylesheet" href="assetss/plugins/morris/morris.css">

<link rel="stylesheet" href="assetss/css/style.css">
<!--[if lt IE 9]>
    <script src="assetss/js/html5shiv.min.js"></script>
    <script src="assetss/js/respond.min.js"></script>
  <![endif]-->
</head>
<body>

<div class="main-wrapper">

<div class="header-outer">
<div class="header">
<a id="mobile_btn" class="mobile_btn float-left" href="#sidebar"><i class="fas fa-bars" aria-hidden="true"></i></a>
<a id="toggle_btn" class="float-left" href="javascript:void(0);">
<img src="assetss/img/sidebar/icon-21.png" alt="">
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
<a href="#" class="mobile-logo d-md-block d-lg-none d-block"><img src="assetss/img/logo1.png" alt="" width="30" height="30"></a>
</li>
</ul>

<ul class="nav user-menu float-right">
<li class="nav-item dropdown d-none d-sm-block">
<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
<img src="assetss/img/sidebar/icon-22.png" alt="">
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
<a href="javascript:void(0);" id="open_msg_box" class="hasnotifications nav-link"><img src="assetss/img/sidebar/icon-23.png" alt=""> </a>
</li>
 <li class="nav-item dropdown has-arrow">
<a href="#" class=" nav-link user-link" data-toggle="dropdown">
<span class="user-img"><img class="rounded-circle" src="<?= $img ?>" width="30" alt="coor"></span>
<span class="status online"></span></span>
<span><?php echo $admin['nom'] ; ?></span>
</a>
<div class="dropdown-menu">
<a class="dropdown-item" href="./coordunateur/profile.php">Mon Profile</a>
<a class="dropdown-item" href="index2.php">Logout</a>
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
<img src="assetss/img/logo1.png" width="40" height="40" alt="">
<span class="text-uppercase">ENOTES</span>
</a>
</div>
<ul class="sidebar-ul">
<li class="menu-title">Menu</li>
<li class="active">
<a href="fichier_coordinateursfiliers.php"><img src="assetss/img/sidebar/icon-1.png" alt="icon"><span>Dashboard</span></a>
</li>
<li >
<a href="./coordunateur/profes_filiere.php"><img src="assetss/img/sidebar/icon-4.png" alt="icon"> <span> profes filiere</span></a>
</li>
<li >
<a href="./coordunateur/etud_filiere.php"><img src="assetss/img/sidebar/icon-4.png" alt="icon"> <span> Etudiants filiere</span></a>
</li>
<li>
<a href="./coordunateur/modules_f.php"><img src="assetss/img/sidebar/icon-5.png" alt="icon"> <span>modules filiere</span></a>
</li>
<li>
<a href="#"><img src="assetss/img/sidebar/icon-18.png" alt="icon"> <span>Emplois de temps</span><span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="./coordunateur/emploi.php"><span>creer emploi niveau</span></a></li>
<li><a href="./coordunateur/emploi_prof.php"><span>creer emploi prof </span></a></li>
<li><a href="./coordunateur/consulter.php"><span>consulter emploi  </span></a></li>
</ul>
</li>
<li>
<a href="./coordunateur/affect_mod.php"><img src="assetss/img/sidebar/icon-14.png" alt="icon"> <span>Affectation modules</span></a>
</li>
<li>
<a href="#"><img src="assetss/img/sidebar/icon-19.png" alt="icon"> <span>Notes</span><span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="./coordunateur/notes_modules.php"><span>Notes modules</span></a></li>
<li><a href="./coordunateur/notes_annee.php"><span>Notes annee</span></a></li>
</ul>
</li>
<li>
<a href="./coordunateur/arch/archivage.php"><img src="assetss/img/sidebar/icon-8.png" alt="icon"> <span>archivage</span></a>
</li>
<li>
<a href="./coordunateur/matieres_affect.php"><img src="assetss/img/sidebar/icon-10.png" alt="icon"> <span>p.Matières affectées</span></a>
</li>
<li>
<a href="./coordunateur/liste_etu.php"><img src="assetss/img/sidebar/icon-4.png" alt="icon"> <span>p.Listes Etudiants</span></a>
</li>
<li>
<a href="./coordunateur/inserer_notes.php"><img src="assetss/img/sidebar/icon-12.png" alt="icon"> <span>p.Insertion des Notes</span></a>
</li>
<li>
<a href="#"><img src="assetss/img/sidebar/icon-26.png" alt="icon"> <span>p.Consulter Notes</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="./coordunateur/notes_ds.php"><span>Notes DS</span></a></li>
<li><a href="./coordunateur/notes_examen.php"><span>Notes Examen</span></a></li>
<li><a href="./coordunateur/moyenne.php"><span>Moyenne</span></a></li>
</ul>
</li>
<li>
<a href="./coordunateur/modifier.php"><img src="assetss/img/sidebar/icon-7.png" alt="icon"> <span>p.Modifier Notes</span></a>
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
<h3 class="page-title mb-0">Dashboard</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="fichier_coordinateursfiliers.php"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item"><span>Dashboard</span></li>
</ul>
</div>
</div>
</div>

<div class="row">
<div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
<div class="dash-widget dash-widget5">
<span class="float-left"><img src="assetss/img/dash/dash-1.jpeg" alt="" width="90"></span>
<div class="dash-widget-info text-right">
<span>Etudiants</span>
<h3><?php echo htmlspecialchars($student_count); ?></h3>
</div>
</div>
</div>
<div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
<div class="dash-widget dash-widget5">
<div class="dash-widget-info text-left d-inline-block">
<span>Professeurs</span>
<h3><?php echo htmlspecialchars($professor_count); ?></h3>
</div>
<span class="float-right"><img src="assetss/img/dash/dash-2.jpeg" width="90" alt=""></span>
</div>
</div>
<div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
<div class="dash-widget dash-widget5">
<span class="float-left"><img src="assetss/img/dash/dash-3.jpeg" alt="" width="90"></span>
<div class="dash-widget-info text-right">
<span>Filieres</span>
<h3><?php echo htmlspecialchars($filiere_count); ?></h3>
</div>
</div>
</div>
<div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
<div class="dash-widget dash-widget5">
<div class="dash-widget-info d-inline-block text-left">
<span>Departements</span>
<h3>2</h3>
</div>
<span class="float-right"><img src="assetss/img/dash/dash-4.jpeg" alt="" width="130"></span>
</div>
</div>
</div>
<div class="row">
<div class="col-lg-6 d-flex">
<div class="card flex-fill">
<div class="card-header">
<div class="row align-items-center">
<div class="col-auto">
<div class="page-title">
Etudiants
</div>
</div>
<div class="col text-right">
<div class=" mt-sm-0 mt-2">
<button class="btn btn-light" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></button>
 <div class="dropdown-menu dropdown-menu-right">
<a class="dropdown-item" href="#">Action</a>
<div role="separator" class="dropdown-divider"></div>
<a class="dropdown-item" href="#">Autre action</a>
<div role="separator" class="dropdown-divider"></div>
<a class="dropdown-item" href="#">Quelque chose d'autre ici</a>
</div>
</div>
</div>
</div>
</div>
<div class="card-body">
<div id="chart1"></div>
</div>
</div>
</div>
<div class="col-lg-6 d-flex">
<div class="card flex-fill">
<div class="card-header">
<div class="row align-items-center">
<div class="col-auto">
<div class="page-title">
Performance des étudiants
</div>
</div>
<div class="col text-right">
<div class=" mt-sm-0 mt-2">
<button class="btn btn-light" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></button>
<div class="dropdown-menu dropdown-menu-right">
<a class="dropdown-item" href="#">Action</a>
<div role="separator" class="dropdown-divider"></div>
<a class="dropdown-item" href="#">Autre action</a>
<div role="separator" class="dropdown-divider"></div>
<a class="dropdown-item" href="#">Quelque chose d'autre ici</a>
</div>
</div>
</div>
</div>
</div>
<div class="card-body">
<div id="chart2"></div>
</div>
</div>
</div>
</div>
<div class="row">
<div class="col-lg-6 col-md-12 col-12 d-flex">
<div class="card flex-fill">
<div class="card-header">
<div class="row align-items-center">
<div class="col-auto">
<div class="page-title">
Evénements
</div>
</div>
<div class="col text-right">
<div class=" mt-sm-0 mt-2">
<button class="btn btn-light" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></button>
<div class="dropdown-menu dropdown-menu-right">
<a class="dropdown-item" href="#">Action</a>
<div role="separator" class="dropdown-divider"></div>
<a class="dropdown-item" href="#">Autre action</a>
<div role="separator" class="dropdown-divider"></div>
 <a class="dropdown-item" href="#">Quelque chose d'autre ici</a>
</div>
</div>
</div>
</div>
</div>
<div class="card-body">
<div id="calendar" class=" overflow-hidden"></div>
</div>
</div>
</div>
<div class="col-lg-6 col-md-12 col-12 d-flex">
<div class="card flex-fill">
<div class="card-header">
<div class="row align-items-center">
<div class="col-auto">
<div class="page-title">
Membre total
</div>
</div>
<div class="col text-right">
<div class=" mt-sm-0 mt-2">
<button class="btn btn-light" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></button>
<div class="dropdown-menu dropdown-menu-right">
<a class="dropdown-item" href="#">Action</a>
<div role="separator" class="dropdown-divider"></div>
<a class="dropdown-item" href="#">Autre action</a>
<div role="separator" class="dropdown-divider"></div>
<a class="dropdown-item" href="#">Quelque chose d'autre ici</a>
</div>
</div>
</div>
</div>
</div>
<div class="card-body d-flex align-items-center justify-content-center overflow-hidden">
<div id="chart3"> </div>
</div>
</div>
</div>
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


<script src="assetss/js/jquery-3.6.0.min.js"></script>

<script src="assetss/js/bootstrap.bundle.min.js"></script>

<script src="assetss/js/jquery.slimscroll.js"></script>
 
<script src="assetss/js/select2.min.js"></script>
<script src="assetss/js/moment.min.js"></script>

<script src="assetss/js/fullcalendar.min.js"></script>
<script src="assetss/js/jquery.fullcalendar.js"></script>

<script src="assetss/plugins/morris/morris.min.js"></script>
<script src="assetss/plugins/raphael/raphael-min.js"></script>
<script src="assetss/js/apexcharts.js"></script>
<script src="assetss/js/chart-data.js"></script>

<script src="assetss/js/app.js"></script>
</body>
</html>