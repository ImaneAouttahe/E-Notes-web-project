<?php
class pdf{
    public static $alerts=[];
    public static function connect()
    {
        $conn=new PDO("mysql:host=localhost;dbname=enotee","root","");
        return $conn;
    }
    public static function insert($name, $img) {
        try {
            $conn = pdf::connect();
            $stmt = $conn->prepare("INSERT INTO archive_table (name, img, date) VALUES (?, ?, NOW())");
            $stmt->execute([$name, $img]);
            if ($stmt->rowCount() > 0) {
                pdf::$alerts[] = 'RECORD ADDED!';
            } else {
                pdf::$alerts[] = 'RECORD NOT ADDED!';
            }
        } catch (PDOException $e) {
            pdf::$alerts[] = 'Error: ' . $e->getMessage();
        }
    }
    
    
    
    public static function select(){
        $list = pdf::connect()->prepare("SELECT * FROM archive_table");
        $list->execute();
        $fetch = $list->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    
}

?>