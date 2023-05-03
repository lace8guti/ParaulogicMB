<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<?php
// Inicializar la sesión
session_start();
 
// Comprobar si el usuario está logueado, si no redirigir a la página de inicio de sesión
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
 
// Incluir el archivo de configuración
require_once "conn.php";
 
// Definir variables e inicializar con valores vacíos
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
 
// Procesar los datos del formulario cuando se envíe
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validar la nueva contraseña
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Si us plau, introdueixi la nova contrasenya.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "La contrasenya ha de tindrem 6 caracters com a mínim.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validar la confirmación de contraseña
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Si us plau, confirmi la contrasenya.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Les contrasenyes no coincideixen.";
        }
    }
        
    // Comprobar si hay errores antes de actualizar la base de datos
    if(empty($new_password_err) && empty($confirm_password_err)){
        // Preparar una declaración de actualización
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Vincular variables a la declaración preparada como parámetros
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
            
            // Establecer parámetros
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            // Intentar ejecutar la declaración preparada
            if(mysqli_stmt_execute($stmt)){
                // La contraseña se actualizó correctamente. Destruir la sesión y redirigir a la página de inicio de sesión
                session_destroy();
                header("location: login.php");
                exit();
            } else{
                echo "Hi ha hagut un problema, si us plau, provi-ho un altre cop.";
            }
        }
        
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
    
    // Cerrar la conexión
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Canvia la teva contrasenya</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
       body {
  font-family: Arial, sans-serif;
  background-color: #f1f1f1;
}

.wrapper {
  background-color: #fff;
  padding: 40px;
  border-radius: 5px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
  max-width: 400px;
  margin: 0 auto;
}

h2 {
  font-size: 24px;
  color: #333;
  margin-bottom: 30px;
}

.form-group label {
  display: block;
  margin-bottom: 10px;
  color: #333;
}

.form-control {
  width: 100%;
  height: 40px;
  padding: 0 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  background-color: #f7f7f7;
  color: #333;
  font-size: 14px;
}

.form-control:focus {
  outline: none;
  box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
  border-color: #66afe9;
}

.has-error .form-control {
  border-color: #a94442;
}

.help-block {
  color: #a94442;
  margin-top: 5px;
}

.btn-primary {
  background-color: #4CAF50;
  color: #fff;
  border: none;
  border-radius: 5px;
  height: 40px;
  padding: 0 20px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background-color: #3e8e41;
}

    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Canvii la seva contrasenya</h2>
        <p>Omlpi aquest formulari per a restablir la seva contrasenya.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
            <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                <label>Nova contrasenya</label>
                <input type="password" name="new_password" class="form-control" value="<?php echo $new_password; ?>">
                <span class="help-block"><?php echo $new_password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirmar contrasenya</label>
                <input type="password" name="confirm_password" class="form-control">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Enviar">
                <a class="btn btn-link" href="../main/paraulogicJoc.php">Cancelar</a>
            </div>
        </form>
    </div>    
</body>
</html>