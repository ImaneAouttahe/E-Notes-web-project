<?php
session_start();

// Vérification de la session de connexion
if (!isset($_SESSION['email']) || !isset($_SESSION['password'])) {
    exit('Accès non autorisé');
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("Erreur de connexion à la base de données : " . $e->getMessage());
}

$email = $_SESSION['email'];
$password = $_SESSION['password'];

try {
    // Requête pour récupérer les informations complètes de l'utilisateur, incluant l'image
    $sql_user = $pdo->prepare('SELECT id_ut, nom, prenom, email, ville, img, role 
                               FROM utilisateurs 
                               WHERE email = :email AND mdp = :password');
    $sql_user->execute(['email' => $email, 'password' => $password]);
    $user = $sql_user->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        exit('Utilisateur non trouvé');
    }

    // Construction de l'URL de l'image
    $imgURL = "../les_images/" . htmlspecialchars($user['img']);

    // Récupération des fichiers PDF de l'utilisateur
    $stmt_pdf = $pdo->prepare('SELECT id, nom_fichier, contenu_pdf, date_creation 
                               FROM fichiers_pdf
                               WHERE id_ut = :id_ut');
    $stmt_pdf->execute(['id_ut' => $user['id_ut']]);
    $pdf_files = $stmt_pdf->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    exit("Erreur lors de la récupération des données : " . $e->getMessage());
}

try {
    $sql_img = $pdo->prepare('SELECT u.nom , u.prenom , u.email , u.ville ,u.img , u.role  FROM utilisateurs u  WHERE email = :email AND mdp = :password');
    $sql_img->execute(array('email' => $email, 'password' => $password));
$admin = $sql_img->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération de l'image : " . $e->getMessage();
    exit();
}

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
     /* Style pour la section de profil */
.section.profile {
  padding: 20px;
}

/* Style pour la carte du profil */
.card.profile-card {
  text-align: center;
}

/* Style pour l'image de profil */
.profile-card img {
  border-radius: 50%;
  margin-bottom: 20px;
  border: 2px solid #2FDF84; /* Ajoute une bordure bleue avec une épaisseur de 2px */
}

/* Style pour le nom */
.profile-card h2 {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin-bottom: 10px;
}

/* Style pour le rôle */
.profile-card h3 {
  font-size: 18px;
  color: #777;
  margin-bottom: 20px;
}

/* Style pour les onglets */
.nav-tabs.nav-tabs-bordered {
  border-bottom: 2px solid #8944D7;
}

/* Style pour les liens des onglets */
.nav-tabs.nav-tabs-bordered .nav-item .nav-link {
  border: none;
  background-color: transparent;
  color: #333;
  font-weight: bold;
}

/* Style pour le lien actif des onglets */
.nav-tabs.nav-tabs-bordered .nav-item .nav-link.active {
  color: #fff;
  background-color: #8944D7;
}

/* Style pour le contenu des onglets */
.tab-content.pt-2 {
  padding-top: 20px;
}

/* Style pour le titre de la carte */
.card-title {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
}

/* Style pour les étiquettes */
.label {
  font-weight: bold;
  color: #555;
}

/* Style pour les détails */
.profile-overview .row {
  margin-bottom: 10px;
}

/* Style pour les colonnes des détails */
.profile-overview .row .col-lg-3,
.profile-overview .row .col-md-4 {
  font-weight: bold;
  color: #777;
}

/* Style pour les valeurs des détails */
.profile-overview .row .col-lg-9,
.profile-overview .row .col-md-8 {
  color: #333;
}
/* Style du conteneur principal */
#fichiers-container {
    font-family: Arial, sans-serif; /* Police de texte */
    margin: 20px; /* Marge autour du conteneur */
    padding: 15px; /* Espace interne au conteneur */
    background-color: #f4f4f4; /* Couleur de fond */
    border-radius: 8px; /* Bords arrondis */
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Ombre légère */
}

/* Style des div internes pour chaque fichier */
.fichier-item {
    background-color: white; /* Fond blanc pour les fichiers */
    margin-bottom: 10px; /* Marge en bas de chaque fichier */
    padding: 10px; /* Espace interne pour chaque fichier */
    border: 1px solid #ddd; /* Bordure subtile */
    border-radius: 5px; /* Bords arrondis */
}

/* Style des paragraphes pour la date */
.fichier-date {
    color: #555; /* Couleur du texte pour la date */
    font-size: 0.9em; /* Taille de police légèrement réduite */
    margin: 0 0 5px 0; /* Marge réduite pour la date */
}

/* Style des liens pour télécharger */
.fichier-link {
    display: inline-block; /* Bloc en ligne pour les liens */
    text-decoration: none; /* Pas de soulignement */
    color: #2a2a2a; /* Couleur du texte */
    background-color: #e2e2ff; /* Fond bleu clair */
    padding: 8px 12px; /* Espace interne pour le lien */
    border-radius: 4px; /* Bords arrondis pour le lien */
    font-weight: bold; /* Texte en gras */
}

.fichier-link:hover {
    background-color: #c6c6ff; /* Changement de couleur au survol */
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
<div class="drop-scroll">
<ul class="notification-list">
<li class="notification-message">
<a href="#">
<div class="media">
<span class="avatar">
<img alt="John Doe" src="assets/img/user-06.jpg" class="img-fluid rounded-circle">
</span>
<div class="media-body">
<p class="noti-details"><span class="noti-title">John Doe</span> is now following you </p>
<p class="noti-time"><span class="notification-time">4 mins ago</span></p>
</div>
</div>
</a>
</li>
<li class="notification-message">
<a href="#">
<div class="media">
<span class="avatar">T</span>
<div class="media-body">
<p class="noti-details"><span class="noti-title">Tarah Shropshire</span> sent you a message.</p>
<p class="noti-time"><span class="notification-time">6 mins ago</span></p>
</div>
</div>
</a>
</li>
<li class="notification-message">
<a href="#">
<div class="media">
<span class="avatar">L</span>
<div class="media-body">
<p class="noti-details"><span class="noti-title">Misty Tison</span> like your photo.</p>
<p class="noti-time"><span class="notification-time">8 mins ago</span></p>
</div>
</div>
</a>
</li>
<li class="notification-message">
<a href="#">
<div class="media">
<span class="avatar">G</span>
<div class="media-body">
<p class="noti-details"><span class="noti-title">Rolland Webber</span> booking appoinment for meeting.</p>
<p class="noti-time"><span class="notification-time">12 mins ago</span></p>
</div>
</div>
</a>
</li>
<li class="notification-message">
<a href="#">
<div class="media">
<span class="avatar">T</span>
<div class="media-body">
<p class="noti-details"><span class="noti-title">Bernardo Galaviz</span> like your photo.</p>
<p class="noti-time"><span class="notification-time">2 days ago</span></p>
</div>
</div>
</a>
</li>
</ul>
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
<span class="user-img"><img class="rounded-circle" src="<?= $img ?>" width="30" alt="prof"></span>
<span class="status online"></span></span>
<span><?php echo $admin['nom'] ; ?></span>
</a>
<div class="dropdown-menu">
<a class="dropdown-item" href="profile.php">Mon Profile</a>
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
<a href="../fichier_professeurs.php"><img src="assets/img/sidebar/icon-1.png" alt="icon"><span>Dashboard</span></a>
</li>
<li>
<a href="#"><img src="assets/img/sidebar/icon-18.png" alt="icon"> <span>Emplois de temps</span></a>
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
<h3 class="page-title mb-0">Liste Etudiants</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="../fichier_professeurs.php"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item"><span>Dashboard</span></li>
</ul>
</div>
</div>
</div>

<div id="fichiers-container">
    <?php foreach ($pdf_files as $fichier): ?>
        <div class="fichier-item">
            <p class="fichier-date">Date de création : <?= htmlspecialchars($fichier['date_creation']) ?></p>
            <!-- Lien pour télécharger le fichier -->
            <a class="fichier-link" href="../coordunateur/emploi/<?= htmlspecialchars($fichier['nom_fichier']) ?>" download="<?= htmlspecialchars($fichier['nom_fichier']) ?>">Télécharger le fichier</a>
        </div>
    <?php endforeach; ?>
</div>












<div class="notification-box">
<div class="msg-sidebar notifications msg-noti">
<div class="topnav-dropdown-header">
<span>Messages</span>
</div>
<div class="drop-scroll msg-list-scroll">
<ul class="list-box">
<li>
<a href="#">
<div class="list-item new-message">
<div class="list-left">
<span class="avatar">J</span>
</div>
<div class="list-body">
<span class="message-author">Ruth C. Gault</span>
<span class="message-time">1 Aug</span>
<div class="clearfix"></div>
<span class="message-content">Lorem ipsum dolor sit amet, consectetur adipiscing</span>
</div>
</div>
</a>
</li>
<li>
<a href="#">
<div class="list-item">
<div class="list-left">
<span class="avatar">M</span>
</div>
<div class="list-body">
<span class="message-author">Mike Litorus</span>
<span class="message-time">12:28 AM</span>
<div class="clearfix"></div>
<span class="message-content">Lorem ipsum dolor sit amet, consectetur adipiscing</span>
</div>
</div>
</a>
</li>
</ul>
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


