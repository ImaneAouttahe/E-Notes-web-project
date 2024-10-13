<?php
require './conn.php';

if (isset($_POST['button'])) {
    $name = $_POST['name'];
    $img = '';

    // Vérifier si le fichier a été correctement téléchargé
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Vérifier le type de fichier
        if ($_FILES['file']['type'] == 'application/pdf') {
            $img = $_FILES['file']['name'];
            // Déplacer le fichier téléchargé vers le dossier approprié
            if (move_uploaded_file($_FILES['file']['tmp_name'], 'images/' . $img)) {
                if (!empty($name) && !empty($img)) {
                    // Insérer les données dans la base de données
                    pdf::insert($name, $img);
                } else {
                    pdf::$alerts[] = 'Fill the fields.......!';
                }
            } else {
                pdf::$alerts[] = 'Failed to move the file.';
            }
        } else {
            pdf::$alerts[] = 'Invalid file type. Only PDF files are allowed.';
        }
    } else {
        pdf::$alerts[] = 'Error uploading file.';
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Les Documents</title>
</head>
<body>
    <div class="alerts">
     <?php
        if(count(pdf::$alerts)>0){
            $alerts=pdf::$alerts;
            foreach($alerts as $value){
                echo $value;
            }
        }else{
            echo 'NO ALERTS';
        }



     ?>
    </div>



    <div class="form">
        <h1>Telecharger Documents </h1>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="importer votre doc(.pdf)">
            <div class="file-container">
               <label for="file-upload">Choisir un fichier</label>
              <input type="file" id="file-upload" name="file">
            </div>

            <input type="submit" value="enregistrer" name="button">

        </form>
    </div>



    <div class="content">
    <div class="title">
        <h1>PDF Documents</h1>
    </div>
    <div class="files">
        <table>
            <tr>
                <th>Name</th>
                <th>PDF</th>
                <th>Date d'insertion</th>
            </tr>
    <?php
    $fetch = pdf::select();
    if (is_array($fetch) && count($fetch) > 0) {
        foreach ($fetch as $value) {
            ?>
            <tr>
                <td><?php echo $value['name']; ?></td>
                <td><a href="images/<?php echo $value['img']; ?>" download="<?php echo $value['img']; ?>"><?php echo $value['img']; ?></a></td>
                <td><?php echo $value['date']; ?></td>
            </tr>
            <?php
        }
    }
    ?>
        </table>
    </div>
</div>

</body>
</html>