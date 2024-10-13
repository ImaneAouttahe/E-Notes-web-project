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
    $sql_img = $pdo->prepare('SELECT u.nom , u.prenom , u.email , u.ville ,u.img , u.role , p.specialite , d.nom_d , f.nom_f FROM utilisateurs u JOIN profes p ON u.id_ut = p.id_ut JOIN departements d ON p.id_d = d.id_d JOIN cordonnateur c ON u.id_ut = c.id_ut JOIN filieres f ON c.id_f = f.id_f WHERE email = :email AND mdp = :password');
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
$niveau_id = isset($_POST['niveau_id']) ? $_POST['niveau_id'] : null;
$module_id = isset($_POST['module_id']) ? $_POST['module_id'] : null;

// Vérifier si toutes les données nécessaires sont présentes
if ($niveau_id && $module_id) {

    $stmt_niveau = $pdo->prepare('SELECT nom_n , id_s FROM niveau WHERE id_n IN (select id_n from niveau where nom_n = :niveau_id)');
    $stmt_niveau->execute(['niveau_id' => $niveau_id]);
    $niveau = $stmt_niveau->fetch(PDO::FETCH_ASSOC);

    $stmt_jsp=  $pdo->prepare('SELECT nom , prenom  FROM utilisateurs WHERE id_ut IN (SELECT id_ut from profes WHERE id_p IN (SELECT id_prof FROM modules WHERE id_mod = :module_id) )');
    $stmt_jsp->execute(['module_id' => $module_id]);
    $prof = $stmt_jsp->fetch(PDO::FETCH_ASSOC);

    // Récupérer le nom du module
    $stmt_module = $pdo->prepare('SELECT nom_mod FROM modules WHERE id_mod = :module_id');
    $stmt_module->execute(['module_id' => $module_id]);
    $module = $stmt_module->fetchColumn();

    // Sélectionner les pourcentages des épreuves pour le module donné
    $stmt_pourcentages = $pdo->prepare('SELECT pourcentage
                                        FROM notes
                                        WHERE id_m = :module_id
                                        GROUP BY type_epreuve');
    $stmt_pourcentages->execute(['module_id' => $module_id]);
    $pourcentages = $stmt_pourcentages->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si les pourcentages ont été récupérés avec succès
    if (count($pourcentages) == 2) { // On suppose qu'il y a un pourcentage pour DS et un pourcentage pour Examen
        $pourcentage_ds = $pourcentages[0]['pourcentage'];
        $pourcentage_exam = $pourcentages[1]['pourcentage'];

        // Sélectionner les informations sur les étudiants et leurs notes de DS et d'examen uniquement si l'état est "done"
        $stmt_etudiants_notes = $pdo->prepare('SELECT u.nom, u.prenom, 
                                                SUM(CASE WHEN n.type_epreuve = "DS" AND n.etat = "done" THEN n.note END) AS note_ds,
                                                SUM(CASE WHEN n.type_epreuve = "Examen" AND n.etat = "done" THEN n.note END) AS note_exam
                                            FROM utilisateurs u
                                            JOIN etudiants et ON u.id_ut = et.id_ut
                                            LEFT JOIN notes n ON et.id_et = n.id_et
                                            WHERE et.id_n IN (select id_n from niveau where nom_n = :niveau_id) AND n.id_m = :module_id
                                            GROUP BY et.id_et');
        $stmt_etudiants_notes->execute(['niveau_id' => $niveau_id, 'module_id' => $module_id]);
        $etudiants = $stmt_etudiants_notes->fetchAll(PDO::FETCH_ASSOC);

        // Calculer la moyenne pour chaque étudiant
        $etudiants_moyennes = [];
        foreach ($etudiants as $etudiant) {
            if (!is_null($etudiant['note_ds']) && !is_null($etudiant['note_exam'])) {
                $moyenne = (($etudiant['note_ds'] * $pourcentage_ds) + ($etudiant['note_exam'] * $pourcentage_exam)) / 100;
                $etudiants_moyennes[] = [
                    'nom' => $etudiant['nom'],
                    'prenom' => $etudiant['prenom'],
                    'moyenne' => $moyenne
                ];
            }
        }

    } else {
        echo "Erreur: notes de DS ou Examen non encore saisir.";
    }
} else {
    // Rediriger l'utilisateur vers la page précédente si des données sont manquantes
    header('Location: moyenne.php');
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
<span class="user-img"><img class="rounded-circle" src="<?= $img ?>" width="30" alt="coor"></span>
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
<a href="../fichier_coordinateursfiliers.php"><img src="assets/img/sidebar/icon-1.png" alt="icon"><span>Dashboard</span></a>
</li>
<li >
<a href="profes_filiere.php"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> profes filiere</span></a>
</li>
<li >
<a href="etud_filiere.php"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> Etudiants filiere</span></a>
</li>
<li>
<a href="modules_f.php"><img src="assets/img/sidebar/icon-5.png" alt="icon"> <span>modules filiere</span></a>
</li>
<li>
<a href="#"><img src="assets/img/sidebar/icon-18.png" alt="icon"> <span>Emplois de temps</span><span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="emploi.php"><span>creer emploi niveau</span></a></li>
<li><a href="emploi_prof.php"><span>creer emploi prof </span></a></li>
<li><a href="consulter.php"><span>consulter emploi </span></a></li>
</ul>
</li>
<li>
<a href="affect_mod.php"><img src="assets/img/sidebar/icon-14.png" alt="icon"> <span>Affectation modules</span></a>
</li>
<li>
<a href="#"><img src="assets/img/sidebar/icon-19.png" alt="icon"> <span>Notes</span><span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="notes_modules.php"><span>Notes modules</span></a></li>
<li><a href="notes_annee.php"><span>Notes annee</span></a></li>
</ul>
</li>
<li>
<a href="./arch/archivage.php"><img src="assets/img/sidebar/icon-8.png" alt="icon"> <span>archivage</span></a>
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
<li>
<a href="#"><img src="assets/img/sidebar/icon-28.png" alt="icon"> <span>p.Insertion d'absence</span></a>
</li>

</div>
</div>
</div>
<div class="page-wrapper">
<div class="content container-fluid">

<div class="page-header">
<div class="row">
<div class="col-md-6">
<h3 class="page-title mb-0">notes modules</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="../fichier_coordinateursfiliers.php"><i class="fas fa-home"></i> Home</a></li>
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
                    Niveau: <?= $niveau['nom_n'] ?>&nbsp;&nbsp;S<?= $niveau['id_s'] ?>&nbsp;&nbsp;     
                    Module: <?= $module ?>&nbsp;&nbsp;&nbsp;&nbsp; 
                    Prof: <?=$prof['nom'] ?> <?= $prof['prenom'] ?>&nbsp;&nbsp;&nbsp;&nbsp; 
                         </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($etudiants_moyennes as $etudiant): ?>
                <tr>
                    <td><?= $etudiant['nom'] ?></td>
                    <td><?= $etudiant['prenom'] ?></td>
                    <td><?=  $etudiant['moyenne']  ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button id="pdfButton" class="btn btn-outline-danger mr-2" ><img src="assets/img/pdf.png" alt="" height="18"><span class="ml-2">PDF</span></button>
                        <button id="sendToStudentsButton" class="btn btn-outline-primary">envoyer aux etudiants</button>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
<script>
document.getElementById('sendToStudentsButton').addEventListener('click', function() {
    var moduleId = <?= json_encode($module_id) ?>;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_notes.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                alert('Les notes ont été envoyées aux étudiants.');
            } else {
                alert('Une erreur est survenue.');
            }
        }
    };
    xhr.send('module_id=' + moduleId);
});
</script>

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