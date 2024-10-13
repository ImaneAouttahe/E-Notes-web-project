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
// Vérifiez si des données de formulaire ont été envoyées
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['niveau_id'])) {
    // Récupérez l'ID du niveau sélectionné depuis le formulaire
    $niveau_id = $_POST['niveau_id'];

    // Connexion à la base de données
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
        exit();
    }
 
    // Requête pour récupérer les modules associés à ce niveau
    $stmt_modules = $pdo->prepare('SELECT id_mod, nom_mod FROM modules where id_n IN (select id_n from niveau WHERE nom_n = :niveau_id)');
    $stmt_modules->execute(['niveau_id' => $niveau_id]);
    $modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);
    $all_notes_done = true;
    // Afficher les moyennes des étudiants pour chaque module
    foreach ($modules as $module) {
        $module_id = $module['id_mod'];
        $module_name = $module['nom_mod'];

        $stmt_pourcentages = $pdo->prepare('SELECT pourcentage
        FROM notes
        WHERE id_m = :module_id
        GROUP BY type_epreuve');
        $stmt_pourcentages->execute(['module_id' => $module_id]);
        $pourcentages = $stmt_pourcentages->fetchAll(PDO::FETCH_ASSOC);
        if (count($pourcentages) == 2){
        // Requête pour récupérer les notes des étudiants pour ce module
        $stmt_notes = $pdo->prepare('SELECT id_ut, SUM(note * pourcentage/ 100) AS moyenne  ,  etat 
                                    FROM notes 
                                    WHERE id_m = :module_id 
                                    GROUP BY id_ut');
        $stmt_notes->execute(['module_id' => $module_id]);
        $moyennes = $stmt_notes->fetchAll(PDO::FETCH_ASSOC);

        // Afficher les moyennes pour ce module
        foreach ($moyennes as $moyenne) {
            if ($moyenne['etat'] != "done") {
                $all_notes_done = false;
                exit('Notes pas encore disponibles .');
            }

        }
        if (!$all_notes_done) {
            break;
        }
        }else{
            header("Location: notes_annee.php");
            exit();
        }
    }
} else {
    // Redirection si les données de formulaire sont manquantes
    header("Location: notes_annee.php");
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
div.form-container {
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7;
    margin-bottom: 20px;
}

div.form-container h3 {
    margin-bottom: 10px;
}

div.form-container select,
div.form-container input {
    padding: 8px;
    border-radius: 5px;
    border: none;
    margin-right: 10px;
    box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7;
}

div.form-container button {
    padding: 8px 20px;
    background: linear-gradient(180deg, #2FDF84 0%, #8944D7 100%);
    border: none;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
}

div.form-container button:hover {
    background: #8944D7;
}

/* Style pour les éléments en focus */
div.form-container select:focus,
div.form-container input:focus {
    outline: none; /* Supprime le contour par défaut */
    box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7; /* Réapplique le style du focus */
}

text-danger{
    
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

<li><a href="consulter.php"><span>consulter emploi  </span></a></li>
</ul>
</li>
<li>
<a href="affect_mod.php"><img src="assets/img/sidebar/icon-14.png" alt="icon"> <span>Affectation modules</span></a>
</li>
<li>
<a href="#"><img src="assets/img/sidebar/icon-19.png" alt="icon"> <span>Notes</span><span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="notes_modules.php"><span>Notes modules</span></a></li>
<li><a href="#"><span>Notes annee</span></a></li>
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

</div>
</div>
</div>
<div class="page-wrapper">
<div class="content container-fluid">

<div class="page-header">
<div class="row">
<div class="col-md-6">
<h3 class="page-title mb-0">notes annee  </h3>
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
                    Niveau: <?= $niveau_id ?>&nbsp;&nbsp;
                         </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                <table id="tableData" class="table table-hover">
                <thead>
                    <tr>
                        <th  style="width: 300px;">Étudiant</th>
                        <?php
                        // En-têtes de colonnes pour chaque module
                        foreach ($modules as $module) {
                            echo "<th  style='width: 300px;' >{$module['nom_mod']}</th>";
                        }
                        ?>
                        <th  style="width: 300px;" >Moyenne</th>
                        <th  style="width: 300px;">État</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Requête pour récupérer les étudiants
                    $stmt_etudiants = $pdo->prepare('SELECT Distinct e.id_ut, u.nom, u.prenom FROM utilisateurs u JOIN etudiants e ON u.id_ut = e.id_ut where id_n IN (select id_n from niveau WHERE nom_n = :niveau_id)');
                    $stmt_etudiants->execute( ['niveau_id' => $niveau_id ]);
                    $etudiants = $stmt_etudiants->fetchAll(PDO::FETCH_ASSOC);

                    // Parcourir les étudiants
                    foreach ($etudiants as $etudiant) {
                        $id_etudiant = $etudiant['id_ut'];
                        $nom_etudiant = $etudiant['nom'];
                        $prenom_etudiant = $etudiant['prenom'];
                        $moyenne_totale = 0;
                        $status = "";
                        $count_modules = count($modules);
                        echo "<tr>";
                        echo "<td>$nom_etudiant $prenom_etudiant</td>";

                        // Pour chaque module, récupérer la moyenne de l'étudiant
                        foreach ($modules as $module) {
                            $module_id = $module['id_mod'];

                            // Requête pour récupérer la moyenne de l'étudiant pour ce module
                            $stmt_moyenne = $pdo->prepare('SELECT SUM(note * pourcentage / 100) AS moyenne 
                                                           FROM notes 
                                                           WHERE id_m = :module_id AND id_ut = :id_etudiant');
                            $stmt_moyenne->execute(['module_id' => $module_id, 'id_etudiant' => $id_etudiant]);
                            $moyenne = $stmt_moyenne->fetchColumn();
                            $moyenne_totale += $moyenne;
                            // Afficher la moyenne dans la cellule correspondante
                            echo "<td>$moyenne</td>";
                        }
                        // Calcul de la moyenne totale
    $moyenne_totale /= $count_modules;

    // Déterminer le statut
    if ($moyenne_totale >= 12) {
        $status = "Valider";
    } else {
        $status = "Rattrapage";
    }
    $status_class = ($status === "Rattrapage") ? "text-danger" : "text-success";

    // Afficher la moyenne totale et le statut
    echo "<td>$moyenne_totale</td>";
    echo "<td class='$status_class'> $status </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            </div>
                                </div>
                            </div>
                        </div>
                        <button id="downloadExcel" class="btn btn-outline-primary mr-2">
    <img src="assets/img/excel.png" alt="">
    <span class="ml-2">Excel</span>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

<script>
    document.getElementById('downloadExcel').addEventListener('click', function() {
    // Sélection du tableau
    var table = document.getElementById('tableData');

    // Convertir le tableau HTML en un objet worksheet Excel
    var ws = XLSX.utils.table_to_sheet(table);

    // Créer un nouveau classeur Excel et ajouter la feuille de calcul
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

    // Générer un fichier Excel à partir du classeur
    var excelBuffer = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });

    // Convertir le fichier Excel en blob
    var blob = new Blob([excelBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });

    // Créer un objet URL à partir du blob
    var url = URL.createObjectURL(blob);

    // Créer un lien de téléchargement pour le fichier Excel
    var link = document.createElement('a');
    link.href = url;
    link.download = 'tableau.xlsx';

    // Clic automatique sur le lien pour télécharger le fichier Excel
    document.body.appendChild(link);
    link.click();

    // Libérer l'objet URL après le téléchargement
    URL.revokeObjectURL(url);
});
</script>


</body>
</html>