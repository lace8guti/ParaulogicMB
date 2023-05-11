<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<?php
include("../other_resources/BBDD_credentials2.php");

// Verificar si se ha recibido el parámetro id
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Conectar a la base de datos
    $conn = new mysqli($host, $usuari, $password, $database);

    // Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->begin_transaction();
    
    try {
        // Eliminar los registros asociados al usuario en la tabla challenges
        $conn->query("DELETE FROM challenges WHERE user_id = $user_id");

        // Eliminar los registros asociados al usuario en la tabla daily_scores
        $conn->query("DELETE FROM daily_scores WHERE user_id = $user_id");

        // Eliminar el usuario de la tabla users
        $conn->query("DELETE FROM users WHERE id = $user_id");

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al usuario a la página de lista de usuarios
        header('Location: ../main/estadistiques.php');
        } catch (mysqli_sql_exception $e) {
        // Si hay algún error, hacer rollback de la transacción y mostrar el error
        $conn->rollback();
        echo 'Error: ' . $e->getMessage();
      }
}
?>