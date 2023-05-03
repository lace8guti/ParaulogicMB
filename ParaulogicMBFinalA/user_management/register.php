<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<?php
// Mete un include conn.php
require_once "conn.php";
 
// Define variables e initializa con valores vacíos
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
 
// Procesa el formulario cuando esté se remita mediante POST
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Valida el nombre de usuario
    if(empty(trim($_POST["username"]))){
        $username_err = "Si us plau, introdueixi un usuari.";
    } else{
        // Prepara la sentenica select
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Une las variables a los parámetros de la sentecia preparada
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Prepara los parámetros de la sentencia
            $param_username = trim($_POST["username"]);
            
            // Prueba a ejecutar la sentencia preparada
            if(mysqli_stmt_execute($stmt)){
                // alamacena el resutaldo
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "Aquest usuari ja és en us.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Alguna cosa ha sortit malament.";
            }
        }
         
        // Cierra la sentencia
        mysqli_stmt_close($stmt);
    }
    
    // Valida la contraseña
    if(empty(trim($_POST["password"]))){
        $password_err = "Si us plau, introdueixi una contrasenya.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "La contrasenya com a mínim ha de contenir 6 caracters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Valida la confirmación de la contraseña
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Confirma la teva contrasenya.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "La contrasenya no coincideix.";
        }
    }
    
    // Comprueba los posibles errores antes de insertar en la BBDD
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Prepara la sentencia del insert
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Iguala las variables a parámetros de la sentencia preparada
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
            
            // Prepara los parámetros
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Intentar ejecutar la sentencia preparada
            if(mysqli_stmt_execute($stmt)){
                // Redirigir a la página de login
                header("location: login.php");
            } else{
                echo "Hi ha hagut un error, si us plau, torni a provar.";
            }
        }
         
        // Cerrar sentencia SQL
        mysqli_stmt_close($stmt);
    }
    
    // Cerrar conexión
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registre ParaulogicMB</title>
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
        <h2>Registre ParaulogicMB</h2>
        <p>Si us plau, ompli aquest formulari per a crear un comte.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Usuario</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Contrasenya</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirmar Contrasenya</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Ingresar">
                <input type="reset" class="btn btn-default" value="Esborrar">
            </div>
            <p>Ja tens un comte? <a href="login.php">Ingressa aquí</a>.</p>
        </form>
    </div>    
</body>
</html>