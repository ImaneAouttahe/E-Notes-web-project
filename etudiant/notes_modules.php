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

// Récupérer l'email et le mot de passe de la session
$email = $_SESSION['email'];
$password = $_SESSION['password'];

try {
  // Requête pour récupérer le nom de l'image de l'utilisateur connecté
  $sql_img = $pdo->prepare('SELECT img FROM utilisateurs WHERE email = :email AND mdp = :password');
  $sql_img->execute(array('email' => $email, 'password' => $password));
  $img_name = $sql_img->fetchColumn(); // Récupérer seulement le nom de l'image
  $img = "../les_images/" . $img_name; // Construire l'URL complète de l'image
} catch (PDOException $e) {
  echo "Erreur lors de la récupération de l'image : " . $e->getMessage();
  exit();
}

// Récupérer l'utilisateur connecté
try {
    $stmt_user = $pdo->prepare('SELECT u.id_ut, u.nom, u.prenom, u.email, u.ville, u.img, u.role, n.nom_n 
                                FROM utilisateurs u 
                                JOIN etudiants e ON u.id_ut = e.id_ut 
                                JOIN niveau n ON e.id_n = n.id_n 
                                WHERE email = :email AND mdp = :password');
    $stmt_user->execute(['email' => $email, 'password' => $password]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        exit('Utilisateur non trouvé');
    }

    $user_id = $user['id_ut'];

    // Récupérer uniquement les modules avec l'état "envoyer"
    $stmt_modules = $pdo->prepare('SELECT DISTINCT m.id_mod, m.nom_mod 
                                   FROM modules m 
                                   JOIN notes n ON m.id_mod = n.id_m 
                                   WHERE n.id_ut = :user_id AND n.etat2 = "envoyer"');
    $stmt_modules->execute(['user_id' => $user_id]);
    $modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);

    $modules_moyennes = [];

    foreach ($modules as $module) {
        $module_id = $module['id_mod'];
        $module_name = $module['nom_mod'];
        
        // Sélectionner les pourcentages des épreuves pour le module donné
        $stmt_pourcentages = $pdo->prepare('SELECT pourcentage, type_epreuve 
                                            FROM notes 
                                            WHERE id_m = :module_id AND etat2 = "envoyer"
                                            GROUP BY type_epreuve');
        $stmt_pourcentages->execute(['module_id' => $module_id]);
        $pourcentages = $stmt_pourcentages->fetchAll(PDO::FETCH_ASSOC);

        if (count($pourcentages) == 2) {
            $pourcentage_ds = $pourcentages[0]['pourcentage'];
            $pourcentage_exam = $pourcentages[1]['pourcentage'];

            // Sélectionner les notes de DS et d'examen uniquement si l'état est "envoyer"
            $stmt_notes = $pdo->prepare('SELECT 
                                            SUM(CASE WHEN type_epreuve = "DS" THEN note END) AS note_ds,
                                            SUM(CASE WHEN type_epreuve = "Examen" THEN note END) AS note_exam
                                         FROM notes 
                                         WHERE id_ut =:user_id 
                                           AND id_m = :module_id AND etat2 = "envoyer"');
            $stmt_notes->execute(['user_id' => $user_id, 'module_id' => $module_id]);
            $notes = $stmt_notes->fetch(PDO::FETCH_ASSOC);

            if (!is_null($notes['note_ds']) && !is_null($notes['note_exam'])) {
                $moyenne = (($notes['note_ds'] * $pourcentage_ds) + ($notes['note_exam'] * $pourcentage_exam)) / 100;
                $modules_moyennes[] = [
                    'module' => $module_name,
                    'moyenne' => $moyenne
                ];
            }
        }
    }

} catch (PDOException $e) {
    exit('Erreur lors de la récupération des informations : ' . $e->getMessage());
}

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

<link rel="shortcut icon" type="image/x-icon" href="../assetss/img/favicon.png">

<link href="../../../../css?family=Roboto:300,400,500,700,900" rel="stylesheet">

<link rel="stylesheet" href="../assetss/css/bootstrap.min.css">

<link rel="stylesheet" href="../assetss/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="../assetss/plugins/fontawesome/css/fontawesome.min.css">

<link rel="stylesheet" href="../assetss/css/fullcalendar.min.css">

<link rel="stylesheet" href="../assetss/css/dataTables.bootstrap4.min.css">

<link rel="stylesheet" href="../assetss/plugins/morris/morris.css">

<link rel="stylesheet" href="../assetss/css/style.css">
 <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
</head>
<!--[if lt IE 9]>
    <script src="../assetss/js/html5shiv.min.js"></script>
    <script src="../assetss/js/respond.min.js"></script>
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
<img src="../assetss/img/sidebar/icon-21.png" alt="">
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
<a href="#" class="mobile-logo d-md-block d-lg-none d-block"><img src="../assetss/img/logo1.png" alt="" width="30" height="30"></a>
</li>
</ul>

<ul class="nav user-menu float-right">
<li class="nav-item dropdown d-none d-sm-block">
<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
<img src="../assetss/img/sidebar/icon-22.png" alt="">
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
<img alt="John Doe" src="../assetss/img/user-06.jpg" class="img-fluid rounded-circle">
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
<a href="javascript:void(0);" id="open_msg_box" class="hasnotifications nav-link"><img src="../assetss/img/sidebar/icon-23.png" alt=""> </a>
</li>
 <li class="nav-item dropdown has-arrow">
<a href="#" class=" nav-link user-link" data-toggle="dropdown">
<span class="user-img"><img class="rounded-circle" src="<?= $img ?>" width="30" alt="coor"></span>
<span class="status online"></span></span>
<span><?php echo $admin['nom'] ; ?></span>
</a>
<div class="dropdown-menu">
<a class="dropdown-item" href="#">mon Profile</a>
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
<img src="../assetss/img/logo1.png" width="40" height="40" alt="">
<span class="text-uppercase">ENOTES</span>
</a>
</div>
<ul class="sidebar-ul">
<li class="menu-title">Menu</li>
<li class="active">
<a href="../fichier_etudiant.php"><img src="../assetss/img/sidebar/icon-1.png" alt="icon"><span>Dashboard</span></a>
</li>
<li>
<a href="emplois_t.php"><img src="../assetss/img/sidebar/icon-5.png" alt="icon"> <span>Emplois de temps</span></a>
</li>
<li>
<a href="#"><img src="../assetss/img/sidebar/icon-7.png" alt="icon"> <span>Notes</span></a>
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
<h3 class="page-title mb-0">Notes</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="../fichier_etudiant.php"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item"><span>Dashboard</span></li>
</ul>
</div>
</div>
</div>




<div class="main-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-md-12" id="pdfContent">
                    <div class="alert alert-info" role="alert">
                        Étudiant: <?= htmlspecialchars($user['nom']) ?> <?= htmlspecialchars($user['prenom']) ?>&nbsp;&nbsp;
                        Niveau: <?= htmlspecialchars($user['nom_n']) ?>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Module</th>
                                            <th>Moyenne</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($modules_moyennes as $module_moyenne): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($module_moyenne['module']) ?></td>
                                                <td><?= number_format($module_moyenne['moyenne'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <button id="pdfButton" class="btn btn-outline-danger mr-2">
                    <img src="assetss/img/pdf1.png" alt="" height="18"><span class="ml-2">PDF</span>
                </button>
            </div>
        </div>
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
<div class="list-item">
<div class="list-left">
<span class="avatar">R</span>
</div>
<div class="list-body">
<span class="message-author">Richard Miles </span>
<span class="message-time">12:28 AM</span>
<div class="clearfix"></div>
<span class="message-content">Lorem ipsum dolor sit amet, consectetur adipiscing</span>
</div>
</div>
</a>
</li>
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
<span class="avatar">T</span>
</div>
<div class="list-body">
<span class="message-author"> Tarah Shropshire </span>
<span class="message-time">12:28 AM</span>
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
<li>
<a href="#">
<div class="list-item">
<div class="list-left">
<span class="avatar">C</span>
</div>
<div class="list-body">
<span class="message-author"> Catherine Manseau </span>
<span class="message-time">12:28 AM</span>
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
<span class="avatar">D</span>
</div>
<div class="list-body">
<span class="message-author"> Domenic Houston </span>
<span class="message-time">12:28 AM</span>
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
<span class="avatar">B</span>
</div>
<div class="list-body">
<span class="message-author"> Buster Wigton </span>
<span class="message-time">12:28 AM</span>
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
<span class="avatar">R</span>
</div>
<div class="list-body">
<span class="message-author"> Rolland Webber </span>
<span class="message-time">12:28 AM</span>
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
<span class="avatar">C</span>
</div>
<div class="list-body">
<span class="message-author"> Claire Mapes </span>
<span class="message-time">12:28 AM</span>
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
<span class="message-author">Melita Faucher</span>
<span class="message-time">12:28 AM</span>
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
<span class="avatar">J</span>
</div>
<div class="list-body">
<span class="message-author">Jeffery Lalor</span>
<span class="message-time">12:28 AM</span>
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
<span class="avatar">L</span>
</div>
<div class="list-body">
<span class="message-author">Loren Gatlin</span>
<span class="message-time">12:28 AM</span>
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
<span class="avatar">T</span>
</div>
<div class="list-body">
<span class="message-author">Tarah Shropshire</span>
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

<script src="../assetss/js/jquery-3.6.0.min.js"></script>

<script src="../assetss/js/bootstrap.bundle.min.js"></script>

<script src="../assetss/js/jquery.slimscroll.js"></script>
 
<script src="../assetss/js/select2.min.js"></script>
<script src="../assetss/js/moment.min.js"></script>

<script src="../assetss/js/fullcalendar.min.js"></script>
<script src="../assetss/js/jquery.fullcalendar.js"></script>

<script src="../assetss/plugins/morris/morris.min.js"></script>
<script src="../assetss/plugins/raphael/raphael-min.js"></script>
<script src="../assetss/js/apexcharts.js"></script>
<script src="../assetss/js/chart-data.js"></script>

<script src="../assetss/js/app.js"></script>


<script>
    // Fonction pour télécharger le tableau au format PDF
    document.getElementById('pdfButton').addEventListener('click', function () {
        // Options pour html2pdf
        const options = {
            margin: [0.5, 0.5],
            filename: 'notes.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        // Convertir en PDF
        html2pdf().from(document.getElementById('pdfContent')).set(options).save();
    });
</script>
</body>
</html>