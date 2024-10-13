


<?php
session_start();
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

try {
    // Connexion à la base de données personnelle
    $pdo_enote = new PDO('mysql:host=localhost;dbname=enotee', 'root', '');
    $pdo_enote->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Gestion des erreurs

    // Récupérez les valeurs de l'utilisateur (email et mot de passe)
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];

    $stmt_id_filiere = $pdo->prepare("SELECT c.id_f FROM cordonnateur c WHERE c.id_ut IN (select id_ut from utilisateurs where email = :email AND mdp = :password)");
    $stmt_id_filiere->execute(array('email' => $email, 'password' => $password));
    $id_filiere = $stmt_id_filiere->fetchColumn();
    $stmt_filiere = $pdo->prepare("SELECT f.nom_f FROM filieres f WHERE f.id_f = :id_f");
    $stmt_filiere->bindParam(':id_f', $id_filiere, PDO::PARAM_INT);
    $stmt_filiere->execute();
    $filiere = $stmt_filiere->fetch(PDO::FETCH_ASSOC);
    $nom_filiere = $filiere ? htmlspecialchars($filiere['nom_f']) : "aucune filière.";

   $stmt_niveaux = $pdo->prepare('SELECT n.id_n, n.nom_n, n.id_s, n.id_op, c.nomop 
   from niv_fil nf 
   left join niveau n on n.id_n = nf.id_n
   left join choix_3 c on n.id_op = c.idop
   WHERE nf.id_f = :id_f');


$stmt_niveaux->bindParam(':id_f', $id_filiere, PDO::PARAM_INT);
$stmt_niveaux->execute();
$niveaux = $stmt_niveaux->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
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
    
    .choix{
      background-color:  #2FDF84;
      border: none;
      color: white;
      border-radius: 5px;
    }
    .choix {
        padding: 7px 10px;
        margin: 5px 0;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .choix:hover {
        background-color: #20c997; /* Darker green */
    }
    
    /* Style for when the button is clicked */
    .choix.active {
        background-color: #333; /* Dark gray */
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        border: 1px solid #ddd;
    }
    
    .blue {
        background-color: #2FDF84; /* Bleu-vert, votre couleur initiale */
    }
    .yellow {
        background-color: #FF6347;/* Orange */
    }
    .purple {
       
        background-color: #FFA500;  /* Rouge */
    }
    
    #modulesList li {
        max-width: 210px; 
        max-height:90px;/* Changer la valeur de la largeur selon vos besoins */
        padding: 10px; /* Ajouter un remplissage pour l'apparence */
        margin-bottom: 5px; /* Ajouter un espacement entre les modules */
        border-radius: 5px; /* Ajouter une bordure arrondie pour un meilleur aspect */
    }
    
    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff;
        margin: 0;
        padding: 20px;
        color: #000000;
    }
    .container {
        background-color: #ffffff;
        border: 1px solid #4CAF50;
        border-radius: 8px;
        padding: 20px;
        max-width: 800px;
        margin: auto;
    }
    h2 {
        color: #4CAF50;
    }
    p {
        font-size: 16px;
        line-height: 1.5;
    }
    .selected {
        color: #4CAF50;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }td {
        /* autres styles */
        align-items: center; /* Centre verticalement */
        justify-content: center; /* Centre horizontalement */
        text-align: center;
        vertical-align: middle;
        overflow-wrap: break-word; /* Permet de passer à la ligne si nécessaire */
    }
    
    th, td {
        border: 1px solid #dddddd;
        text-align: center;
        padding: 8px;
    }
    th {
        background:linear-gradient(180deg,#8944D7 0%,#2FDF84  100%);
        color: white;
    }
    td[contenteditable="true"]:focus {
        border: 2px solid #20c997; /* Couleur de bordure verte lors du focus */
        outline: none;
    }
    
    ul {
        style="list-style-type: none;
    }
     .form-control {
        display: inline-block;
        width: 100%;
        height: calc(1.5em + .75rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #6e707e;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #d1d3e2;
        border-radius: .35rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    #btnTerminer{
        background:linear-gradient(180deg, #2FDF84 0%,#8944D7 100%);
        border: none;
        color:white;
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

</div>
</div>
</div>
<div class="page-wrapper">
<div class="content container-fluid">

<div class="page-header">
<div class="row">
<div class="col-md-6">
<h3 class="page-title mb-0">creer emploi prof</h3>
</div>
<div class="col-md-6">
<ul class="breadcrumb mb-0 p-0 float-right">
<li class="breadcrumb-item"><a href="../fichier_coordinateursfiliers.php"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item"><span>Dashboard</span></li>
</ul>
</div>
</div>
</div>

<?php 
$showButton = false; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['professeur_id'])) {
    $stmt = $pdo->prepare("SELECT nom, prenom FROM utilisateurs WHERE id_ut = :id_ut");
    $stmt->bindParam(':id_ut', $_POST['professeur_id'], PDO::PARAM_INT);
    $stmt->execute();
    $professeur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($professeur) {
        $showButton = true; 
        echo '<table>';
        echo '<tr><td colspan="8">Professeur: ' . htmlspecialchars($professeur['nom']) . " " . htmlspecialchars($professeur['prenom']) . '</td></tr>';
        echo '<tr><th>emplois</th><th>8:30-10:30</th><th>10:30-12:30</th><th>14:30-16:30</th><th>16:30-18:30</th></tr>';
        echo '<tr>
                <td>lundi</td>
                <td contenteditable="true"></td><td contenteditable="true"></td>
                <td contenteditable="true"></td> <td contenteditable="true"></td>
              </tr>
              <tr>
                <td>Mardi</td>
                <td contenteditable="true"></td><td contenteditable="true"></td>
                <td contenteditable="true"></td> <td contenteditable="true"></td>
              </tr>
              <tr>
                <td>Mercredi</td>
                <td contenteditable="true"></td><td contenteditable="true"></td>
                <td contenteditable="true"></td> <td contenteditable="true"></td>
              </tr>
              <tr>
                <td>Jeudi</td>
                <td contenteditable="true"></td><td contenteditable="true"></td>
                <td contenteditable="true"></td> <td contenteditable="true"></td>
              </tr>
              <tr>
                <td>Vendredi</td>
                <td contenteditable="true"></td> <td contenteditable="true"></td>
                <td contenteditable="true"></td> <td contenteditable="true"></td>
              </tr>
              <tr>
                <td>Samedi</td>
                <td contenteditable="true"></td><td contenteditable="true"></td>
                <td contenteditable="true"></td><td contenteditable="true"></td>
              </tr>';
        echo '</table>';
        echo '<br><br>';
    } else {
        echo "<p>Aucun professeur sélectionné ou trouvé.</p>";
    }
}
?><?php 
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'enotee';
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    if (isset($_POST['professeur_id'])) {
        $professeur_id = $_POST['professeur_id'];
        $stmt = $pdo->prepare("SELECT m.id_mod, m.nom_mod, m.id_n
                               FROM modules m
                               JOIN profes p ON m.id_prof = p.id_p
                               WHERE p.id_ut = ?");
        $stmt->execute([$professeur_id]);

        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Modules :</h3><ul id='modulesList' style='list-style: none;'>"; 
        $colorClasses = ['blue', 'yellow', 'purple']; 
        $index = 0; 
        
        foreach ($modules as $module) {
            $colorClass = $colorClasses[$index % count($colorClasses)]; 
            
            $stmt_niveau = $pdo->prepare("SELECT nom_n, id_s, id_op FROM niveau WHERE id_n = ?");
            $stmt_niveau->execute([$module['id_n']]);
            $niveau = $stmt_niveau->fetch(PDO::FETCH_ASSOC);

            $display_name = $niveau['nom_n'] . ' S' . $niveau['id_s'];
            if ($niveau['id_op'] != 0) {
                $stmt_option = $pdo->prepare('SELECT nomop FROM choix_3 WHERE idop = :id_op');
                $stmt_option->execute(['id_op' => $niveau['id_op']]);
                $nom_option = $stmt_option->fetchColumn();
                if ($nom_option) {
                    $display_name .= ' option : ' . $nom_option;
                }
            }
            echo "<li id='module-" . htmlspecialchars($module['id_mod']) . "' draggable='true' class='module " . $colorClass . "'>" . htmlspecialchars($module['nom_mod']) . "<br>" . $display_name . "</li>";

            $index++; 
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<script>
document.addEventListener('DOMContentLoaded', (event) => {
const items = document.querySelectorAll('#modulesList li');
const cells = document.querySelectorAll('td');

items.forEach(item => {
    item.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text', e.target.id);
    });
});

cells.forEach(cell => {
    cell.addEventListener('dragover', (e) => {
        e.preventDefault(); 
    });

    cell.addEventListener('drop', (e) => {
        e.preventDefault();
        const id = e.dataTransfer.getData('text');
        const draggableElement = document.getElementById(id);
        const dropZone = e.target;
        if (dropZone.tagName === 'TD') { 
            let clone = draggableElement.cloneNode(true);
            dropZone.innerHTML = ''; 
            dropZone.appendChild(clone); 
        }
    });
});
});
</script>
<br><br><?php if ($showButton): ?>
    <button id="btnEnregistrer"><i class="fas fa-save"></i> Enregistrer </button><br><br>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>


<script>

document.getElementById('btnEnregistrer').addEventListener('click', function() {
    generatePDF(function(pdf, filename) {
        var pdfBase64 = pdf.output('datauristring');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'save_pdf.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('pdfBase64=' + encodeURIComponent(pdfBase64) + '&filename=' + encodeURIComponent(filename) + '&professeur_id=' + encodeURIComponent(<?php echo json_encode($professeur_id); ?>));
    });
});
function generatePDF(callback) {
    const table = document.querySelector('table');
    const date = new Date();
    const filename = 'emploi_' + date.getFullYear() + (date.getMonth() + 1) + date.getDate() + '_' + date.getHours() + date.getMinutes() + '.pdf';
    const options = {
        filename: filename, 
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'pt', format: 'letter', orientation: 'portrait' }
    };

    html2pdf().from(table).set(options).toPdf().get('pdf').then(function(pdf) {
        // Send the generated PDF to the server
        pdf.save(filename); // This will trigger the download

        const pdfData = pdf.output('blob');

        const formData = new FormData();
        formData.append('file', pdfData, filename);

        fetch('save_pdf.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  console.log('PDF saved on the server.');
              } else {
                  console.error('Failed to save PDF on the server.');
              }
          }).catch(error => {
              console.error('Error:', error);
          });

        if (callback) {
            callback(pdf, filename);
        }
    });
}


</script>

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
