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
try {
    $pdo = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données: " . $e->getMessage();
    exit(); // Arrête l'exécution du script en cas d'erreur de connexion à la base de données
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_filiere'])) {
    // Vérifier si les données sont présentes
    if(isset($_POST['nom_filiere']) && isset($_POST['departement'])) {
        $nom_filiere = $_POST['nom_filiere'];
        $departement = $_POST['departement'];

        // Insérer la nouvelle filière dans la table filieres avec une requête préparée
        try {
            $stmt = $pdo->prepare('INSERT INTO filieres (nom_f, id_d) VALUES (?, ?)');
            $stmt->execute([$nom_filiere, $departement]);
        } catch (PDOException $e) {
            echo "Erreur d'insertion de la filière: " . $e->getMessage();
            exit(); // Arrête l'exécution du script en cas d'erreur d'insertion de la filière
        }

        // Récupérer l'ID de la filière insérée
        $id_filiere = $pdo->lastInsertId();

        // Ajouter automatiquement les niveaux
        $niveaux = ['1', '2', '3']; // Liste des niveaux
        $semestres = ['1', '2', '3', '4', '5', '6']; // Liste des semestres

        // Après l'insertion des niveaux dans la table niveau
foreach ($niveaux as $niveau) {
    // Déterminer les semestres à associer en fonction du niveau
    $semestres_associes = [];
    if ($niveau == '1') {
        $semestres_associes = ['1', '2'];
    } elseif ($niveau == '2') {
        $semestres_associes = ['3', '4'];
    } elseif ($niveau == '3') {
        $semestres_associes = ['5', '6'];
    }

    // Insérer le niveau avec les semestres associés
    $nom_niveau = $nom_filiere . $niveau; // Nom du niveau avec le nom de la filière
    try {
        // Utilisation d'une seule requête pour l'insertion des semestres avec ou sans options
        $stmt = $pdo->prepare('INSERT INTO niveau (nom_n, id_s, id_op) VALUES (?, ?, ?)');
        foreach ($semestres_associes as $semestre) {
            // Par défaut, l'ID de l'option est NULL
            $id_option = null;
            // Vérifier si des options sont présentes pour le 3ème niveau
            if ($niveau == '3' && !empty($_POST['options'])) {
                foreach ($_POST['options'] as $option) {
                    if (!empty($option)) { // Vérifie si l'option n'est pas vide avant d'insérer
                        try {
                            // Vérifier si l'option existe déjà dans la table choix_3
                            $stmt_option = $pdo->prepare('SELECT idop FROM choix_3 WHERE nomop = ?');
                            $stmt_option->execute([$option]);
                            $row_option = $stmt_option->fetch(PDO::FETCH_ASSOC);
                            if ($row_option) {
                                $id_option = $row_option['idop'];
                            } else {
                                // Insérer l'option dans la table choix_3
                                $stmt_insert_option = $pdo->prepare('INSERT INTO choix_3 (nomop) VALUES (?)');
                                $stmt_insert_option->execute([$option]);
                                // Récupérer l'ID de l'option insérée
                                $id_option = $pdo->lastInsertId();
                            }
                        } catch (PDOException $e) {
                            echo "Erreur d'insertion de l'option: " . $e->getMessage();
                            exit(); // Arrête l'exécution du script en cas d'erreur d'insertion de l'option
                        }
                    }
                }
            }
            // Insérer le semestre avec l'ID de l'option dans la table niveau
            $stmt->execute([$nom_niveau, $semestre, $id_option]);
            
            // Insérer les modules associés aux options de la 3ème année
            if ($niveau == '3' && !empty($_POST['options'])) {
                foreach ($_POST['options'] as $option) {
                    if (!empty($option)) {
                        // Vérifier si les modules pour cette option sont définis dans $_POST
                        $module_key = "module_s${semestre}_${option}";
                        if (isset($_POST[$module_key]) && is_array($_POST[$module_key])) {
                            foreach ($_POST[$module_key] as $module) {
                                if (!empty($module)) {
                                    // Insérer le module dans la table modules avec l'id du niveau associé
                                    try {
                                        $stmt_module = $pdo->prepare('INSERT INTO modules (nom_mod, id_fil, id_n) VALUES (?, ?, ?)');
                                        $stmt_module->execute([$module, $id_filiere, $id_niveau]);
                                    } catch (PDOException $e) {
                                        echo "Erreur d'insertion du module: " . $e->getMessage();
                                        exit(); // Arrête l'exécution du script en cas d'erreur d'insertion du module
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
        echo "Erreur d'insertion du niveau: " . $e->getMessage();
        exit(); // Arrête l'exécution du script en cas d'erreur d'insertion du niveau
    }
}


        // Ajouter la logique d'enregistrement des modules dans la base de données
        // Ajouter la logique d'enregistrement des modules dans la base de données
if(isset($_POST['module_s1']) && is_array($_POST['module_s1'])) {
    // Boucler à travers chaque semestre
    for ($i = 1; $i <= 6; $i++) {
        // Vérifier s'il y a des données de modules pour ce semestre
        if (isset($_POST["module_s$i"]) && is_array($_POST["module_s$i"])) {
            // Récupérer l'id du niveau correspondant au semestre
            try {
                $stmt_niveau = $pdo->prepare('SELECT id_n FROM niveau WHERE nom_n = ?');
                $stmt_niveau->execute([$nom_filiere . $i]);
                $row_niveau = $stmt_niveau->fetch(PDO::FETCH_ASSOC);
                if ($row_niveau) {
                    $id_niveau = $row_niveau['id_n'];
                } else {
                    echo "Aucun résultat trouvé pour cette requête.";
                    exit(); // Arrête l'exécution du script si aucun résultat n'est trouvé
                }
            } catch (PDOException $e) {
                echo "Erreur lors de la récupération de l'identifiant du niveau: " . $e->getMessage();
                exit(); // Arrête l'exécution du script en cas d'erreur de récupération de l'identifiant du niveau
            }
    
            // Boucler à travers chaque module de ce semestre
            foreach ($_POST["module_s$i"] as $module) {
                // Vérifier si le module n'est pas vide avant de l'insérer
                if (!empty($module)) {
                    // Insérer le module dans la table modules avec l'id du niveau associé
                    try {
                        $stmt_module = $pdo->prepare('INSERT INTO modules (nom_mod, id_fil, id_n) VALUES (?, ?, ?)');
                        $stmt_module->execute([$module, $id_filiere, $id_niveau]);
                    } catch (PDOException $e) {
                        echo "Erreur d'insertion du module: " . $e->getMessage();
                        exit(); // Arrête l'exécution du script en cas d'erreur d'insertion du module
                    }
                }
            }
        }
    }

    // Vérifier si des options sont présentes pour la 3ème année
    if (!empty($_POST['options']) && is_array($_POST['options'])) {
        foreach ($_POST['options'] as $option) {
            if (!empty($option)) {
                // Insérer les modules associés à cette option dans la base de données
                for ($i = 5; $i <= 6; $i++) {
                    // Vérifier s'il y a des données de modules pour ce semestre
                    if (isset($_POST["module_s${i}_${option}"]) && is_array($_POST["module_s${i}_${option}"])) {
                        foreach ($_POST["module_s${i}_${option}"] as $module) {
                            // Vérifier si le module n'est pas vide avant de l'insérer
                            if (!empty($module)) {
                                // Insérer le module dans la table modules avec l'id du niveau associé
                                try {
                                    $stmt_module = $pdo->prepare('INSERT INTO modules (nom_mod, id_fil, id_n) VALUES (?, ?, ?)');
                                    $stmt_module->execute([$module, $id_filiere, $id_niveau]);
                                } catch (PDOException $e) {
                                    echo "Erreur d'insertion du module: " . $e->getMessage();
                                    exit(); // Arrête l'exécution du script en cas d'erreur d'insertion du module
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

    }
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
        border: 1px solid ;
        box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7;
    }

    /* Styles pour les étiquettes */
    label {
        font-weight: bold;
        color:#fff;background:linear-gradient(180deg,#2FDF84 0%,#8944D7 100%);border-radius:10px
    }

    /* Styles pour les champs de texte */
    input[type="text"],
    select {
        width: 100%;
        margin-bottom: 10px;
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
        border: 1px solid ;
        box-shadow: 0 0 0 2px #2FDF84, 0 0 0 4px #8944D7;
    }

    .module-input {
        width: 100%;
        margin-bottom: 10px;
       
    }

    button.active {
      color:#fff;background:linear-gradient(180deg,#2FDF84 0%,#8944D7 100%);border-radius:10px
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
<a href="#"><img src="assets/img/sidebar/icon-4.png" alt="icon"> <span> Professeurs </span> <span class="menu-arrow"></span></a>
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
<li><a href="affecter_chef.php"><span>affecter Chef</span></a></li>
<li><a href="#"><span>modifier Chef</span></a></li>
</ul>
</li>
<li class="submenu">
<a href="#"><img src="assets/img/sidebar/icon-20.png" alt="icon"> <span> filieres</span> <span class="menu-arrow"></span></a>
<ul class="list-unstyled" style="display: none;">
<li><a href="#"><span>creer filiere</span></a></li>
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
<h3 class="page-title mb-0">creer filiere </h3>
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
    <form class="form-horizontal" method="post" action="#">
        <div class="col-md-12 col-md-offset-1">
            <div class="form-group">
                <div class="form-group">
                  <br>
                    <label>Nom de la filière (obligatoire)</label>
                    <input type="text" required name="nom_filiere" class="form-control" placeholder="Entrez un nom de filière"/>
                </div>
                <div class="form-group">
                    <label>Département</label>
                    <select name="departement" class="form-control">
                        <?php
                        // Récupérer et afficher la liste des départements depuis la base de données avec une requête préparée
                        try {
                            $stmt_departements = $pdo->query('SELECT * FROM departements');
                            while ($departement = $stmt_departements->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $departement['id_d'] . '">' . $departement['nom_d'] . '</option>';
                            }
                        } catch (PDOException $e) {
                            echo "Erreur de récupération des départements: " . $e->getMessage();
                            exit(); // Arrête l'exécution du script en cas d'erreur de récupération des départements
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description de la filière</label>
                    <input type="text" required name="description" class="form-control" placeholder="Entrez la description"/>
                </div>
                <div class="form-group">
                    <label>Options pour la 3ème année (s'il y en a)</label>
                    <input type="checkbox" name="options_presentes" id="options_presentes"> Options présentes pour la 3ème année
                    <div id="options_container" style="display: none;">
                        <input type="text" name="options[]" class="form-control" placeholder="Entrez une option"/>
                        <input type="text" name="options[]" class="form-control" placeholder="Entrez une option"/>
                        <input type="text" name="options[]" class="form-control" placeholder="Entrez une option"/>
                    </div>
                </div>
                <div id="modules_container">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="form-group">
                            <label for="module_s<?= $i ?>">Modules pour le semestre <?= $i ?></label>
                            <?php for ($j = 1; $j <= 6; $j++): ?>
                                <input type="text" name="module_s<?= $i ?>[]" class="form-control" placeholder="Entrez un module"/>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            <button type="submit" name="add_filiere" class="active">Ajouter</button>
        </div>
    </form>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function(){
    $('#options_presentes').change(function(){
        if(this.checked) {
            $('#options_container').show();
            $('#modules_container').empty(); // Supprimer les champs de module existants
            
            // Ajouter les champs de module pour les semestres s1 à s6
            for (var i = 1; i <= 4; i++) {
                $('#modules_container').append(`
                    <div class="form-group">
                        <label for="module_s${i}">Modules pour le semestre ${i}</label>`);
                for (var j = 1; j <= 4; j++) {
                    $('#modules_container').append(`<input type="text" name="module_s${i}[]" class="form-control" placeholder="Entrez un module"/>`);
                }
                $('#modules_container').append(`</div>`);
            }

            // Afficher les champs de module pour chaque option de la 3ème année
            var options = $('input[name="options[]"]').map(function(){ return $(this).val(); }).get();
            if (options.length > 0) {
                options.forEach(function(option) {
                    for (var i = 5; i <= 6; i++) {
                        $('#modules_container').append(`
                            <div class="form-group">
                                <label for="module_s${i}">Modules pour le semestre ${i} - Option: ${option}</label>`);
                        for (var j = 1; j <= 6; j++) {
                            $('#modules_container').append(`<input type="text" name="module_s${i}_${option}[]" class="form-control" placeholder="Entrez un module"/>`);
                        }
                        $('#modules_container').append(`</div>`);
                    }
                });
            }
        } else {
            $('#options_container').hide();
            $('#modules_container').empty(); // Supprimer les champs de module existants
            
            // Ajouter les champs de module pour les semestres s1 à s6
            for (var i = 1; i <= 6; i++) {
                $('#modules_container').append(`
                    <div class="form-group">
                        <label for="module_s${i}">Modules pour le semestre ${i}</label>`);
                for (var j = 1; j <= 6; j++) {
                    $('#modules_container').append(`<input type="text" name="module_s${i}[]" class="form-control" placeholder="Entrez un module"/>`);
                }
                $('#modules_container').append(`</div>`);
            }
        }
    });
});
</script>






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
