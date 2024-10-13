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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider'])) {
    foreach ($_POST['chef_departement'] as $departement_id => $professeur_id) {
        if (!empty($professeur_id)) {
            $stmt_check_chef = $pdo->prepare('SELECT * FROM chef_d WHERE id_d = :departement_id');
            $stmt_check_chef->execute(['departement_id' => $departement_id]);
            $chef_exist = $stmt_check_chef->fetch(PDO::FETCH_ASSOC);

            if ($chef_exist) {
                $old_chef_id = $chef_exist['id_ut'];
    
                // Mettre à jour l'ancien chef en tant que professeur
                $stmt_update_prof = $pdo->prepare('UPDATE utilisateurs SET role = "professeur" WHERE id_ut = :old_chef_id');
                $stmt_update_prof->execute(['old_chef_id' => $old_chef_id]);
                
                // Mettre à jour le nouveau chef
                $stmt_update_chef = $pdo->prepare('UPDATE chef_d SET id_ut = :id_ut WHERE id_d = :departement_id');
                $stmt_update_chef->execute(['id_ut' => $professeur_id, 'departement_id' => $departement_id]);
                
                // Mettre à jour le rôle de l'utilisateur sélectionné en tant que chef de département
                $stmt_update = $pdo->prepare('UPDATE utilisateurs SET role = "chef_departement" WHERE id_ut = :id_ut');
                $stmt_update->execute(['id_ut' => $professeur_id]);
            } else {
                // Insérer le nouveau chef
                $stmt_insert_chef = $pdo->prepare('INSERT INTO chef_d (id_ut, id_d) VALUES (:id_ut, :departement_id)');
                $stmt_insert_chef->execute(['id_ut' => $professeur_id, 'departement_id' => $departement_id]);
                
                // Mettre à jour le rôle de l'utilisateur sélectionné en tant que chef de département
                $stmt_update = $pdo->prepare('UPDATE utilisateurs SET role = "chef_departement" WHERE id_ut = :id_ut');
                $stmt_update->execute(['id_ut' => $professeur_id]);
            }
        }
    }

    
    header("Location: ../fichier_administrateur.php");
    exit();
}

// Sélectionner tous les départements
$stmt_departements = $pdo->query('SELECT id_d, nom_d FROM departements');
$departements = $stmt_departements->fetchAll(PDO::FETCH_ASSOC);

// Sélectionner tous les professeurs
$stmt_professeurs = $pdo->query('SELECT u.id_ut, u.nom, u.prenom FROM utilisateurs u INNER JOIN profes p ON u.id_ut = p.id_ut');
$professeurs = $stmt_professeurs->fetchAll(PDO::FETCH_ASSOC);
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
    table.custom-table {
        width: 100%;
        background: linear-gradient(180deg, #2FDF84 0%, #8944D7 100%);
        border-collapse: collapse;
        box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7; /* Ajout de l'ombre pour simuler la bordure dégradée */
    }
    table.custom-table th, table.custom-table td {
        border: 1px solid ;
        padding: 8px;
        text-align: center;
        border-collapse: collapse;
        box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7;
    }
    button.custom-button {
        background: linear-gradient(180deg, #2FDF84 0%, #8944D7 100%);
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        color : white ;
    }
</style>
</head>
<body>

< class="main-wrapper">

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
<a href="#"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> Professeurs</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="consulter_profes.php"><span>consulter </span></a></li>
<li><a href="ajouter_prof.php"><span>ajouter Professeur</span></a></li>
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
<li><a href="#"><span>affecter Chef</span></a></li>
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


< class="page-wrapper">
< class="content container-fluid">

<div class="page-header">
<div class="row">
<div class="col-md-6">
<h3 class="page-title mb-0">Affecter chef</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="../fichier_administrateur.php"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item"><span>Dashboard</span></li>
</ul>
</div>
</div>
</div>

<form method="post">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Département</th>
                    <th>Chef de département</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departements as $departement): ?>
                    <tr>
                        <td><?= $departement['nom_d'] ?></td>
                        <td>
                            <select name="chef_departement[<?= $departement['id_d'] ?>]">
                                <option value="">Choisir un professeur</option>
                                <?php foreach ($professeurs as $professeur): ?>
                                    <option value="<?= $professeur['id_ut'] ?>"><?= $professeur['nom'] . ' ' . $professeur['prenom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <button type="submit" class="custom-button" name="valider">Valider</button>
    </form>



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