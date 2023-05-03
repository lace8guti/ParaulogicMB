<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<?php
// Inicializar la sesión
session_start();
 
// Verificar si el usuario ya ha iniciado sesión, si es así, redirigirlo a la página de bienvenida
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
  header("location: ../main/paraulogicJoc.php");
  exit;
}
 
// Incluir archivo de configuración
require_once "conn.php";
 
// Definir variables e inicializar con valores vacíos
$username = $password = "";
$username_err = $password_err = "";
 
// Procesar los datos del formulario cuando se envía
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Verificar si el nombre de usuario está vacío
    if(empty(trim($_POST["username"]))){
        $username_err = "Si us plau, introdueixi el seu nom d'usuari.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Verificar si la contraseña está vacía
    if(empty(trim($_POST["password"]))){
        $password_err = "Si us plau, introdueixi la seva contrasenya.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validar las credenciales
    if(empty($username_err) && empty($password_err)){
        // Preparar una consulta select
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Vincular variables al enunciado preparado como parámetros
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Establecer los parámetros
            $param_username = $username;
            
            // Intentar ejecutar la consulta preparada
            if(mysqli_stmt_execute($stmt)){
                // Almacenar el resultado
                mysqli_stmt_store_result($stmt);
                
                // Verificar si el nombre de usuario existe, si es así, verificar la contraseña
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Vincular las variables de resultado
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // La contraseña es correcta, así que iniciar una nueva sesión
                            session_start();
                            
                            // Almacenar los datos en las variables de sesión
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Redirigir al usuario a la página de bienvenida
                            header("location: ../main/paraulogicJoc.php");
                        } else{
                            // Mostrar un mensaje de error si la contraseña no es válida
                            $password_err = "Contresnya no vàlida, provi-ho una altra vegada.";
                        }
                    }
                } else{
                    // Mostrar un mensaje de error si el nombre de usuario no existe
                    $username_err = "No hi ha cap comte registrat amb aquest nom.";
                }
            } else{
                echo "Hi ha un error, provi-ho un altre cop.";
            }
        }
        
        // Cerrar la consulta
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
    <title>Login ParaulogicMB</title>
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
        <h2>Login ParaulogicMB</h2>
        <p>Si us plau, li preguem que ompli les seves credencials per a iniciar sessió.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Usuari</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Contrasenya</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Ingressar">
            </div>
            <p>Encara no tens comte? <a href="register.php">Registrat aquí</a>.</p>
        </form>
    </div>    
</body>
</html>