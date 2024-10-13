
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

// Récupérez les données du formulaire
$niveau_id = isset($_POST['niveau_id']) ? $_POST['niveau_id'] : null;
$module_id = isset($_POST['module_id']) ? $_POST['module_id'] : null;
$type_epreuve = isset($_POST['type_epreuve']) ? $_POST['type_epreuve'] : null;
$pourcentage = isset($_POST['pourcentage']) ? $_POST['pourcentage'] : null;

// Vérifiez si toutes les données nécessaires sont présentes
if ($niveau_id && $module_id && $type_epreuve && $pourcentage) {
    $stmt_etudiants = $pdo->prepare('SELECT u.nom, u.prenom, u.email, n.nom_n, e.id_et
    FROM utilisateurs u
    INNER JOIN etudiants e ON u.id_ut = e.id_ut
    INNER JOIN niveau n ON e.id_n = n.id_n
    WHERE e.id_n = :niveau_id ');

    $stmt_etudiants->execute(['niveau_id' => $niveau_id]);
    $etudiants = $stmt_etudiants->fetchAll(PDO::FETCH_ASSOC);

    $stmt_notes = $pdo->prepare('SELECT id_et, note FROM notes
        WHERE id_n = :niveau_id AND id_m = :module_id AND type_epreuve = :type_epreuve AND pourcentage = :pourcentage');
    $stmt_notes->execute([
        'niveau_id' => $niveau_id,
        'module_id' => $module_id,
        'type_epreuve' => $type_epreuve,
        'pourcentage' => $pourcentage
    ]);
    $notes_existantes = $stmt_notes->fetchAll(PDO::FETCH_ASSOC);

    // Créez un tableau associatif des notes existantes avec l'identifiant de l'étudiant comme clé
    $notes = [];
    foreach ($notes_existantes as $note) {
        $notes[$note['id_et']] = $note['note'];
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
<span class="user-img"><img class="rounded-circle" src="<?= $img ?>" width="30" alt="chef"></span>
<span class="status online"></span></span>
<span><?php echo $admin['nom'] ; ?></span>
</a>
<div class="dropdown-menu">
<a class="dropdown-item" href="profile.php">My Profile</a>
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
<a href="etud_dep.php"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> filieres dep</span></a>
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
<h3 class="page-title mb-0">Insertion des notes</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="../fichier_chefsdepartements.php"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item"><span>Dashboard</span></li>
</ul>
</div>
</div>
</div>

<div class="main-wrapper">
                <div class="content container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                    <form method="post" action="save_note.php">
    <input type="hidden" name="id_n" value="<?= $niveau_id ?>">
    <input type="hidden" name="id_m" value="<?= $module_id ?>">
    <input type="hidden" name="type_e" value="<?= $type_epreuve ?>">
    <input type="hidden" name="pourcentage" value="<?= $pourcentage ?>">
    <input type="hidden" id="action" name="action" >
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($etudiants as $etudiant):?>
                <tr> 
                    <td><?= $etudiant['nom'] ?></td>
                    <td><?= $etudiant['prenom'] ?></td>
                    <td>
    <input type="hidden" name="id_et[]" value="<?= $etudiant['id_et'] ?>">
    <?php
        // Vérifiez si une note existe pour cet étudiant
        $note_value = isset($notes[$etudiant['id_et']]) ? $notes[$etudiant['id_et']] : '';
        echo '<input type="float" name="note[]" class="form-control" placeholder="Entrez la note" value="' . $note_value . '">';
    ?>
</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Bouton d'enregistrement -->
    <button type="submit" class="btn btn-primary mt-2" onclick="document.getElementById('action').value='enregistrer'">Enregistrer</button>
    <button type="submit" class="btn btn-success mt-2" onclick="document.getElementById('action').value='done'">Terminer</button>
</form>
                                    </div>
                                </div>
                            </div>
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
<?php
} else {
    // Redirigez l'utilisateur vers la page précédente si des données sont manquantes
    header('Location: direction22.php');
    exit();
}
?>