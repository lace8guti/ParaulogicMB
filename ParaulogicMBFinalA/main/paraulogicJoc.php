<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>

<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../user_management/login.php");
    exit;
}
// Establecemos la variable $date//
$date = date('Y-m-d');

///////////////PETICIÓN DE COMPROBACIÓN/CREACIÓN DE DAILY_SCORES//////////


function establish_connection() {
    // Conectar a la base de datos
    include("../other_resources/BBDD_credentials2.php");

    $conn = new mysqli($host, $usuari, $password, $database);

    // Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function check_daily_score() {
    // Establecer conexión a la base de datos
    $conn = establish_connection();

    // Recuperar el id del usuario de la variable de sesión
    $usr_id = $_SESSION['id'];

    // Crear una consulta SQL que seleccione una fila de la tabla daily_scores para el usuario y la fecha actual
    $date = date('Y-m-d');
    $sql = "SELECT * FROM daily_scores WHERE user_id = $usr_id AND date = '$date'";

    // Ejecutar la consulta y recuperar el resultado
    $result = $conn->query($sql);

    // Verificar si la consulta devuelve una fila
    if ($result->num_rows == 0) {
        // Si la consulta no devuelve ninguna fila, insertar una nueva fila en la tabla daily_scores para el usuario y la fecha actual
        $sql = "INSERT INTO daily_scores (user_id, daily_score, date) VALUES ($usr_id, 0, '$date')";
        $conn->query($sql);
    }

    // Cerrar la conexión a la base de datos
    $conn->close();
}

// Llamamos a la función check_daily_score
check_daily_score();

/////////////////PETICIÓN PARA CONSEGUIR EL ID DEL PATRÓN DIARIO////////

function conseguirIdPatronDiario($fecha) {
    // Conectar a la base de datos
    
    $conn = establish_connection();

// Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

// Preparar la consulta SQL

    $sql = "SELECT DISTINCT(`created_at`) FROM `patterns`";
    $stmt = $conn->prepare($sql);

// Verificar si hay errores en la preparación de la consulta
    if (!$stmt) {
        die("La preparación de la consulta falló: " . $conn->error);
    }



// Ejecutar la consulta preparada
    if (!$stmt->execute()) {
        echo "Error al ejecutar la consulta: " . $conn->error;
    }

// Obtener los resultados de la consulta
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $fechaCreacion = $fila['created_at'];

// Cerrar la conexión a la base de datos
    $stmt->close();
    $conn->close();

    $conn = establish_connection();

// Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

// Preparar la consulta SQL

    $sql = "SELECT TIMESTAMPDIFF(DAY, '$fechaCreacion', '$fecha')";
    $stmt = $conn->prepare($sql);

// Verificar si hay errores en la preparación de la consulta
    if (!$stmt) {
        die("La preparación de la consulta falló: " . $conn->error);
    }


// Ejecutar la consulta preparada
    if (!$stmt->execute()) {
        echo "Error al ejecutar la consulta: " . $conn->error;
    }

// Obtener los resultados de la consulta
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    $diff = intval($fila["TIMESTAMPDIFF(DAY, '$fechaCreacion', '$fecha')"]);
    $pattern_id = $diff + 1;

    $stmt->close();
    $conn->close();
    return $pattern_id;
}

// Llamamos a la función conseguirIdPatronDiario y almacenamos su contenido en la variabe $pattern_id
$pattern_id=conseguirIdPatronDiario($date);

////////////////PETICIÓN DEL PATRÓN A LA BBDD//////////////////


function obtener_patron_diario($date) {
    // Configurar las credenciales de conexión a la base de datos
    $conn = establish_connection();

    // Verificar si hay errores de conexión
    if ($conn->connect_error) {
        die("La conexión a la base de datos falló: " . $conn->connect_error);
    }

    // Preparar la consulta SQL
    $id = conseguirIdPatronDiario($date);
    $sql = "SELECT pattern FROM patterns WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Verificar si hay errores en la preparación de la consulta
    if (!$stmt) {
        die("La preparación de la consulta falló: " . $conn->error);
    }

    // Asignar los parámetros de la consulta preparada
    $stmt->bind_param("i", $id);

    // Ejecutar la consulta preparada
    if (!$stmt->execute()) {
        echo "Error al ejecutar la consulta: " . $conn->error;
    }

    // Obtener los resultados de la consulta
    $resultado = $stmt->get_result();

    // Obtener el patrón de la primera fila del resultado
    $fila = $resultado->fetch_assoc();
    $patronPedido = $fila['pattern'];

    // Cerrar la consulta preparada y la conexión a la base de datos
    $stmt->close();
    $conn->close();

    return $patronPedido;
}

// Llamamos a la función obtener_patron_diario y almacenamos su contenido en la variabe $patronPedido
$patronPedido=obtener_patron_diario($date);
/////////////////PETICIÓN DE LAS PALABRAS VÁLIDAS////////////////



function obtenerPalabrasCorrectasDiarias($date) {
    $conn = establish_connection();

    // Verificar si hay errores de conexión
    if ($conn->connect_error) {
        die("La conexión a la base de datos falló: " . $conn->connect_error);
    }

    // Definir el ID del patrón que deseas consultar
    $pattern_id = conseguirIdPatronDiario($date);

    // Preparar la consulta SQL para obtener las palabras correspondientes al patrón
    $sql = "SELECT word FROM valid_words WHERE pattern_id = ?";

    // Preparar la sentencia SQL
    $stmt = $conn->prepare($sql);

    // Verificar si hay errores en la preparación de la sentencia
    if (!$stmt) {
        die("La preparación de la sentencia falló: " . $conn->error);
    }

    // Asignar el valor del parámetro de la consulta
    $stmt->bind_param("i", $pattern_id);

    // Ejecutar la consulta
    if (!$stmt->execute()) {
        die("Error al ejecutar la consulta: " . $stmt->error);
    }

    // Obtener los resultados de la consulta
    $resultado = $stmt->get_result();

    // Inicializar el array para almacenar las palabras
    $arrayPalabrasCorrectasDiarias = array();

    // Recorrer cada resultado y almacenar la palabra en el array
    while ($fila = $resultado->fetch_assoc()) {
        $arrayPalabrasCorrectasDiarias[] = $fila['word'];
    }

    // Cerrar la sentencia preparada y la conexión a la base de datos
    $stmt->close();
    $conn->close();
    
    return $arrayPalabrasCorrectasDiarias;
}

// Llamamos a la función obtenerPalabrasCorrectasDiarias y almacenamos su contenido en la variabe $arrayPalabrasCorrectasDiarias
$arrayPalabrasCorrectasDiarias=obtenerPalabrasCorrectasDiarias($date);

////////////////////////////////////////




if (preg_match('/\[(.*?)\]/', $patronPedido, $matches)) {
    $letrasNoCentralesEnSucio = $matches[1]; //todavía hay que quitarle el caracter central
}

$asterisco = strpos($patronPedido, '*');
$segundo_corchete = strpos($patronPedido, '[', $asterisco);
$letraCentral = substr($patronPedido, $asterisco + 1, $segundo_corchete - $asterisco - 1);

$letrasNoCentrales = str_replace($letraCentral, "", $letrasNoCentralesEnSucio);

// Guardar cada valor del array en una variable diferente
$var1 = $letrasNoCentrales[0];
$var2 = $letrasNoCentrales[1];
$var3 = $letrasNoCentrales[2];
$var4 = $letrasNoCentrales[3];
$var5 = $letrasNoCentrales[4];
$var6 = $letrasNoCentrales[5];

///////////////////////////////////////////////////////

// Crear un array en $_SESSION
$_SESSION['arrayDePalabrasAcertadas'] = array();

/////////////////////////////FUNCIÓN PARA ACTUALIZAR PALABRAS ACCERTADAS//////////


function actualizarPalabrasAcertadas() {
    
    $conn = establish_connection();
  
  // Obtener el user_id y pattern_id actuales
  $user_id = $_SESSION['id'];
  $date = date('Y-m-d');
  $pattern_id = conseguirIdPatronDiario($date); // Función que obtiene el pattern_id actual
  
  // Consultar la tabla challenges
  $query = "SELECT word_guessed FROM challenges WHERE user_id = $user_id AND pattern_id = $pattern_id";
  $result = mysqli_query($conn, $query);
  
  // Crear el array de palabras acertadas
  $palabras_acertadas = array();
  while ($row = mysqli_fetch_assoc($result)) {
    $palabras_acertadas[] = $row['word_guessed'];
  }
  
  // Actualizar el array de palabras acertadas en $_SESSION
  $_SESSION['arrayDePalabrasAcertadas'] = $palabras_acertadas;
  
  // Cerrar la conexión a la base de datos
  mysqli_close($conn);
}


/////////////////////////////FUNCIÓN PARA COMPROBAR PALABRAS INTENTO/////////////////////////////////


function comprobarPalabraAcertada($intento, $arrayPalabrasCorrectasDiarias,$arrayPalabrasAcertadasDiarias) {
    
    if (in_array($intento, $arrayPalabrasAcertadasDiarias)) {
        // Palabra ya acertada previamente
        echo "Ja has encertat aquesta paraula";
    } elseif (in_array($intento, $arrayPalabrasCorrectasDiarias)) {
        // Palabra acertada
        $_SESSION['arrayDePalabrasAcertadas'][] = $intento;
        $score = strlen($intento);
        $user_id = $_SESSION['id'];
        $date = date("Y-m-d H:i:s");
        $pattern_id=conseguirIdPatronDiario($date);
        // Configurar las credenciales de conexión a la base de datos
        $conn = establish_connection();

        // Verificar si hay errores de conexión
        if ($conn->connect_error) {
            die("La conexión a la base de datos falló: " . $conn->connect_error);
        }
        
        
        // INSERT en tabla challenges
        $insert_challenges_query = "INSERT INTO challenges (user_id, pattern_id, date, score, word_guessed) 
                                    VALUES ('$user_id', '$pattern_id', '$date', '$score', '$intento')";
        $insert_challenges_result = mysqli_query($conn, $insert_challenges_query);
        
        // UPDATE en tabla daily_scores
        $update_daily_scores_query = "UPDATE daily_scores 
                                      SET daily_score = daily_score + $score 
                                      WHERE user_id = $user_id AND date = CURDATE()";
        $update_daily_scores_result = mysqli_query($conn, $update_daily_scores_query);
        $conn->close();
        // Mensaje de éxito
        echo "Has aconsseguit $score punts!";
    } else {
        // Palabra fallida
        echo "Prova una altra vegada";
    }    
}

//EJECUTAR LA FUNCIÓN DE ACTUALIZAR EL ARRAY DE SESION DE PALABRAS ACERTADAS Y LA FUNCIÓN DE COMPROBAR PALABRAS ACERTADAS


actualizarPalabrasAcertadas();


/////////////////////////////////////



//////////////////////////////FUNCIÓN PARA MOSTRAR LAS PALABRAS CORRECTAS DEL RETO DE AYER///////

function obtenerFechaAnterior() {
  $hoy = date('Y-m-d');
  $fechaAnterior = date('Y-m-d', strtotime($hoy . ' -1 day'));
  return $fechaAnterior;
}

///////////////////FUNCIONES PARA LAS PISTAS///////////////

function contarPalabrasPorLongitud($arrayPalabras) {
    $longitudes = array();
    foreach ($arrayPalabras as $palabra) {
        $longitud = strlen($palabra);
        if (isset($longitudes[$longitud])) {
            $longitudes[$longitud]++;
        } else {
            $longitudes[$longitud] = 1;
        }
    }
    $resultado = '';
    foreach ($longitudes as $longitud => $cantidad) {
        $resultado .= "Hi ha $cantidad paraules de $longitud caracters de longitud <br>";
        
    }
    return $resultado;
}


// Llamamos a la función contarPalabrasPorLongitud con el array de las palabras correctas de hoy como parámetro y lo guardamos
$pista1=contarPalabrasPorLongitud($arrayPalabrasCorrectasDiarias);


function letrasComunes($arrayPalabras) {
    $letras = array();
    
    // Recorrer cada palabra y contar las letras
    foreach ($arrayPalabras as $palabra) {
        // Convertir la palabra en un array de letras
        $letrasPalabra = str_split($palabra);
        
        // Recorrer cada letra y contarla
        foreach ($letrasPalabra as $letra) {
            // Si la letra ya está en el array, incrementar su contador
            if (isset($letras[$letra])) {
                $letras[$letra]++;
            } else {
                // Si la letra no está en el array, agregarla
                $letras[$letra] = 1;
            }
        }
    }
    
    // Ordenar el array de letras por el número de ocurrencias
    arsort($letras);
    
    // Mostrar las letras más comunes
    $maxOcurrencias = reset($letras);
    foreach ($letras as $letra => $ocurrencias) {
        if ($ocurrencias == $maxOcurrencias) {
            echo "$letra és una lletra força comú, té $ocurrencias ocurrències\n";
        } else {
            break;
        }
    }
}

//////////////////////////////FUNCIÓN PARA MOSTRAR LA PUNTUACIÓN DIARIA//////
function getDailyScore($user_id, $date) {
    // Conectar a la base de datos
    $conn = establish_connection();

    // Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Preparar la consulta SQL
    $sql = "SELECT SUM(daily_score) as score FROM daily_scores WHERE user_id = ? AND date = ?";
    $stmt = $conn->prepare($sql);

    // Verificar si hay errores en la preparación de la consulta
    if (!$stmt) {
        die("La preparación de la consulta falló: " . $conn->error);
    }

    // Enlazar los parámetros de la consulta
    $stmt->bind_param("is", $user_id, $date);

    // Ejecutar la consulta preparada
    if (!$stmt->execute()) {
        echo "Error al ejecutar la consulta: " . $conn->error;
    }

    // Obtener los resultados de la consulta
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $score = $fila['score'];

    $stmt->close();
    $conn->close();

    return $score;
}
//////////FUNCIÓN PARA MOSTRAR TU PUNTUACIÓN TOTAL///////////

function getTotalScore($user_id) {
    // Conectar a la base de datos
    $conn = establish_connection();

    // Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Preparar la consulta SQL
    $sql = "SELECT total_score FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Verificar si hay errores en la preparación de la consulta
    if (!$stmt) {
        die("La preparación de la consulta falló: " . $conn->error);
    }

    // Vincular los parámetros de la consulta
    $stmt->bind_param("i", $user_id);

    // Ejecutar la consulta preparada
    if (!$stmt->execute()) {
        echo "Error al ejecutar la consulta: " . $conn->error;
    }

    // Obtener los resultados de la consulta
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $total_score = $fila['total_score'];

    $stmt->close();
    $conn->close();

    return $total_score;
}

///////////////////////FUNCIÓN DE PARA MOSTRAR EL RANKING DE LAS PUNTUACIONES DIARIAS////////
function showDailyRanking($user_id) {
    // Conectar a la base de datos
    $conn = establish_connection();

    // Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Preparar la consulta SQL
    $sql = "SELECT daily_score, date FROM daily_scores WHERE user_id = ? ORDER BY daily_score DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    // Verificar si hay errores en la preparación de la consulta
    if (!$stmt) {
        die("La preparación de la consulta falló: " . $conn->error);
    }

    // Ejecutar la consulta preparada
    if (!$stmt->execute()) {
        echo "Error al ejecutar la consulta: " . $conn->error;
    }

    // Obtener los resultados de la consulta
    $resultado = $stmt->get_result();

    // Mostrar el ranking de puntuaciones diarias
    echo "Ranking de les puntuacions diàries:<br>";
    $i = 1;
    while ($fila = $resultado->fetch_assoc()) {
        echo $i . ". Puntuació: " . $fila["daily_score"] . " - Data: " . $fila["date"] . "<br>";
        $i++;
    }

    $stmt->close();
    $conn->close();
}

/////////////////FUNCION DEL RANQUING GLOBAL/////////
function getScoreRanking() {
    // Conectar a la base de datos
    $conn = establish_connection();

    // Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Preparar la consulta SQL
    $sql = "SELECT username, total_score FROM users ORDER BY total_score DESC";

    // Ejecutar la consulta y obtener los resultados
    $result = $conn->query($sql);

    // Crear un array para almacenar los resultados
    $ranking = array();

    // Iterar por los resultados y agregarlos al array
    while ($row = $result->fetch_assoc()) {
        $ranking[] = array(
            'username' => $row['username'],
            'score' => $row['total_score']
        );
    }

    // Cerrar la conexión y devolver el ranking
    $conn->close();
    return $ranking;
}

////////////FUNCION PARA DECCIRTE LA CANTIDAD DE PALABRAS QUE HAS ACERTADO Y PARA FELICITARTE/////

$paraulesEncertades=count($_SESSION['arrayDePalabrasAcertadas']);

function animsFelicitats($numeroParaulesEncertades){
    if($numeroParaulesEncertades==15){
        echo'Portes la meitat de les paraules del repte!';
    }
 elseif ($numeroParaulesEncertades==30) {
        echo 'Has encertat totes les paraules del repte. Enhorabona!';
    }else{
        
    }
}

function quantesParaulesHasEncertat($numeroParaulesEncertades){
    echo "Has encertat <strong>".$numeroParaulesEncertades."</strong> paraules.";
}

?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ParaulogicMB</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>     
                .super-container {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    
                }
		.container {
			position: relative;
			width: 650px;
			height: 400px;
			width: 50%; /* ajusta el ancho del div según tus necesidades */
                        margin: 0 auto; /* establece los márgenes izquierdo y derecho a "auto" */
                        margin-bottom: 20px;
                        left: -50px;
		}
                form {
                    position: relative;
                    z-index: 1;
                }
                .button-container {
                    display: flex;
                    justify-content: space-between;
                    width: 100%;
                    max-width: 400px;
                    margin: 0 auto;
                }
		.letter-button {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background-color: white;
			color: black;
			border: 2px solid black;
			width: 60px;
			height: 70px;
			font-size: 28px;
			font-weight: bold;
			margin: 5px;
			clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
		}
		.letter-button.selected {
			background-color: red;
			color: white;
		}
		.letter-button:hover {
			background-color: yellow;
		}
		.letter-button:focus {
			outline: none;
		}
                input[type="text"] {
                    width: 100%;
                    box-sizing: border-box;
                    position: absolute;
                    top: -370px;
                    right: -10px;
                }
                body{
                    font-family: Arial, sans-serif;
                    background-color: #f1f1f1;
                }
                .navbar navbar-expand-sm navbar-light bg-light{
                    position: relative;
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
                .resposta::placeholder {
                  color: transparent;
                  animation: blink-caret 1s step-end infinite;
                }

                @keyframes blink-caret {
                  from, to {
                    color: transparent;
                  }
                  50% {
                    color: black;
                  }
                }
                .rounded-button {
                  border-radius: 5px;
                }

                .imatgeParaulogic{
                    border-radius: 15px;
                }
                .nav-item1{
                    padding-left: 20px;
                }
                .nav-item2{
                    padding-left:650px;
                }
                .puntuacionsRanq{
                    padding-right:600px;
                }
                .puntuacionsdiv{
                    padding-left:700px;
                }
                .infoParaulesdiv{
                    padding-right:700px;
                    padding-top:300px;
                }
	</style>
        <script>
function updateInput(button) {
  var input = document.getElementById("word-input");
  input.value += button.textContent;
}

function shuffleLetters() {
  var buttons = document.querySelectorAll('.letter-button:not(.selected)');
  var letters = [];

  buttons.forEach(function(button) {
    letters.push(button.textContent);
  });

  shuffle(letters);

  for (var i = 0; i < buttons.length; i++) {
    buttons[i].textContent = letters[i];
  }
}

function shuffle(array) {
  var currentIndex = array.length;
  var temporaryValue;
  var randomIndex;

  while (0 !== currentIndex) {
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex -= 1;

    temporaryValue = array[currentIndex];
    array[currentIndex] = array[randomIndex];
    array[randomIndex] = temporaryValue;
  }

  return array;
}

function borrarUltimoCaracter() {
  var input = document.getElementById("word-input");
  input.value = input.value.substring(0, input.value.length - 1);
}

function toggleDiv(id) {
  var div = document.getElementById(id);
  if (div.style.display === "none") {
    div.style.display = "block";
  } else {
    div.style.display = "none";
  }
}

  function mostrarVentana(id) {
    var ventana = document.getElementById(id);
    ventana.style.display = "block";
  }

function cerrarVentana(id) {
  var div = document.getElementById(id);
  div.style.display = "none";
}


</script>
</head>
<body>
    <nav class="navbar navbar-expand-sm navbar-light bg-light">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#opciones">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <!-- logo -->
    <a class="navbar-brand" href="paraulogicJoc.php">
        <img src="../images/TitolParaulogic.png" width="100" height="40" alt="Imatge del logo del Paraulogic" class="imatgeParaulogic">
    </a>
    
    <!-- enllaços -->
    <div class="collapse navbar-collapse" id="opciones">   
      <ul class="navbar-nav">
        <li class="nav-item">
            <button onclick="mostrarVentana('miDiv4')">Rànquing</button>
       </li>
        <li class="nav-item">
          <button onclick="mostrarVentana('pistes')">Pistes</button>
        </li>
        <li class="nav-item">
          <button onclick="mostrarVentana('solucions')">Solucions d'ahir</button>
        </li>
        <a class="navbar-brand">
        <img src="../images/signeInt.png" width="30" height="40" alt="Funcionament del joc" class="imatgeParaulogic" onclick="mostrarVentana('funcionament')">
        </a>
        <li class="nav-item2">
            <button onclick="mostrarVentana('miDiv1')" class="infoParaules">Mostra informació sobre les paraules</button>
       </li>
       <li class="nav-item">
            <button onclick="mostrarVentana('miDiv2')">Mostra la teva informació</button>
       </li>
       <li class="nav-item1">
          <a href="estadistiques.php" class="estadistiques">Estadístiques</a>
        </li>
        <li class="nav-item">
          <a href="../user_management/logout.php" class="tancaSessio">Tanca la sessió</a>
        </li>
      </ul>
    </div>
  </nav>
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  
  <div class="super-container">
      
	<div class="container">
		<button class="letter-button" style="transform: rotate(0deg) translate(120px) rotate(0deg);" onclick="updateInput(this)"><?php echo $var1; ?></button>
		<button class="letter-button" style="transform: rotate(60deg) translate(120px) rotate(-60deg);" onclick="updateInput(this)"><?php echo $var2; ?></button>
		<button class="letter-button" style="transform: rotate(120deg) translate(120px) rotate(-120deg);" onclick="updateInput(this)"><?php echo $var3; ?></button>
		<button class="letter-button" style="transform: rotate(180deg) translate(120px) rotate(180deg);" onclick="updateInput(this)"><?php echo $var4; ?></button>
		<button class="letter-button" style="transform: rotate(240deg) translate(120px) rotate(-240deg);" onclick="updateInput(this)"><?php echo $var5; ?></button>
		<button class="letter-button" style="transform: rotate(300deg) translate(120px) rotate(-300deg);" onclick="updateInput(this)"><?php echo $var6; ?></button>
		<button class="letter-button selected" style="transform: rotate(0deg) translate(0) rotate(0deg);" onclick="updateInput(this)"><?php echo $letraCentral; ?></button>
                <div id="miDiv4" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 600px; height: 80%; max-height: 600px; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); z-index: 999;">
        <p>
                <?php
                $ranquinPuntuacionesDiarias=showDailyRanking($_SESSION['id']);
                echo $ranquinPuntuacionesDiarias;
                ?>
        </p>     
        <p>
                <?php
                $ranquinPuntuacionesTotales=getScoreRanking();
                //print_r($ranquinPuntuacionesTotales);
                
            foreach ($ranquinPuntuacionesTotales as $r) {
                echo $r['username'] . ' : ' . $r['score'] . '<br>';
            }
            ?>
        </p>
        <button onclick="cerrarVentana('miDiv4')">Tanca</button>
      </div>
                <div id="miDiv1" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 600px; height: 80%; max-height: 600px; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); z-index: 999;">
                    <p>
                        <?php
                        if (isset($_SESSION['arrayDePalabrasAcertadas']) && !empty($_SESSION['arrayDePalabrasAcertadas'])) {

                            foreach ($_SESSION['arrayDePalabrasAcertadas'] as $palabra) {
                                $palabra = strtolower($palabra);

                                $url = 'https://ca.wiktionary.org/wiki/' . urlencode($palabra);

                                $url2 = 'https://www.softcatala.org/diccionari-de-sinonims/paraula/' . urlencode($palabra) . '/';

                                echo 'Visita ' . '<a href="' . $url . '" target="_blank">' . $palabra . ' </a> per aprendre més sobre aquesta paraula<br>';
                                echo 'Visita ' . '<a href="' . $url2 . '" target="_blank">' . $palabra . ' </a> per veure sinònims de la paraula<br>';
                            }
                        } else {
                            echo "Encara no has endevinat cap paraula :_(";
                        }
                        ?>
                    </p>
                    <button onclick="cerrarVentana('miDiv1')">Tanca</button>
                </div>
                <div id="miDiv2" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 600px; height: 80%; max-height: 600px; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); z-index: 999;">
                    <p>
                        <?php
                        $puntuacionDiaria = getDailyScore($_SESSION['id'], $date);
                        $puntuacionTotal = getTotalScore($_SESSION['id']);
                        echo "La teva puntuació diaria és: ";
                        echo $puntuacionDiaria;
                        echo '<br>';
                        echo '<br>';
                        echo 'La teva puntuació total és: ';
                        echo $puntuacionTotal;
                        ?>
                    </p>
                    <button onclick="cerrarVentana('miDiv2')">Tanca</button>
	</div>
      
                        </div>
      <form action="paraulogicJoc.php" method="post">
          <input type="text" placeholder="|" id="word-input" name="word-input" class="resposta">
          <div class="button-container">
              <button type="button" onclick="borrarUltimoCaracter()" class="rounded-button">Esborra l'última lletra</button>
              <button type="button" onclick="shuffleLetters()" class="rounded-button">Barreja les lletres</button>
              <button type="submit" name="comprobar" class="rounded-button">Comprovar</button>
          </div>
      </form>
      
      <div>
      <?php
      if(isset($_POST["word-input"]) && $_POST["word-input"] != ''){
        $intento=$_POST["word-input"];
        comprobarPalabraAcertada($intento, $arrayPalabrasCorrectasDiarias,$_SESSION['arrayDePalabrasAcertadas']);
        }else {
        // Palabra VACÍA
        echo '<br>';
        echo '<br>'; 
        echo "Prova una altra vegada";
        }
        echo '<br>';
        echo '<br>';
        //print_r($_SESSION['arrayDePalabrasAcertadas']);
        echo implode(", ",$_SESSION['arrayDePalabrasAcertadas']);
        echo '<br>';
    ?>
  </div>
  <div>
      <?php
      quantesParaulesHasEncertat($paraulesEncertades);
      animsFelicitats($paraulesEncertades);
      ?>
  </div>
      
        <div id="pistes" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 600px; height: 80%; max-height: 600px; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); z-index: 999;">
            <p>
                <?php
                echo 'Pista 1:';
                echo '<br>';
                print_r($pista1);
                echo '<br>';
                echo 'Pista 2:';
                echo '<br>';
                letrasComunes($arrayPalabrasCorrectasDiarias);
                ?>
            </p>
            <button onclick="cerrarVentana('pistes')">Tanca</button>
        </div>
        
        <div id="solucions" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 600px; height: 80%; max-height: 600px; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); z-index: 999;">
            <p>      
                <?php
                // Llamamos a la función obtenerFechaAnterior y almacenamos su valor en $fechaAnterior
                $fechaAnterior = obtenerFechaAnterior();
                
                $patronPedidoAntiguo=obtener_patron_diario($fechaAnterior);
                if($patronPedidoAntiguo!=null){
                    if (preg_match('/\[(.*?)\]/', $patronPedidoAntiguo, $matchesAnt)) {
                        $letrasNoCentralesEnSucioAnt = $matchesAnt[1]; //todavía hay que quitarle el caracter central
                    }

                    $asteriscoAnt = strpos($patronPedidoAntiguo, '*');
                    $segundo_corcheteAnt = strpos($patronPedidoAntiguo, '[', $asteriscoAnt);
                    $letraCentralAnt = substr($patronPedidoAntiguo, $asteriscoAnt + 1, $segundo_corcheteAnt - $asteriscoAnt - 1);

                    $letrasNoCentralesAnt = str_replace($letraCentralAnt, "", $letrasNoCentralesEnSucioAnt);

                    // Guardar cada valor del array en una variable diferente
                    $var1Ant = $letrasNoCentralesAnt[0];
                    $var2Ant = $letrasNoCentralesAnt[1];
                    $var3Ant = $letrasNoCentralesAnt[2];
                    $var4Ant = $letrasNoCentralesAnt[3];
                    $var5Ant = $letrasNoCentralesAnt[4];
                    $var6Ant = $letrasNoCentralesAnt[5];
                    echo "Lletres: ".$letraCentralAnt." + ".$var1Ant.$var2Ant.$var3Ant.$var4Ant.$var5Ant.$var6Ant;
                    echo '<br>';

                    // Ejecutamos la función obtenerPalabrasCorrectasDiarias y guardamos su valor en arrayDeSolucionesDeAyer
                    $arrayDeSolucionesDeAyer=obtenerPalabrasCorrectasDiarias($fechaAnterior);
                    echo 'Solucions del repte del dia anterior';
                    echo '<br>';
                    //print_r($arrayDeSolucionesDeAyer);
                    echo implode(", ",$arrayDeSolucionesDeAyer);
                }else{
                    echo 'Ahir no vam tenir cap repte';
                }
                ?>
            </p>
              <button onclick="cerrarVentana('solucions')">Tanca</button>
        </div>
      <div id='funcionament' style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 600px; height: 80%; max-height: 600px; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); z-index: 999;">
          <b>Aquest es el joc del ParaulogicMB!</b>
          <p>Aquest joc consisteix en endevinar paraules a partir de la combinació de les lletres que et proposem! La lletra central sempre és obligatòria. Cada dia hauràs de trovar 30 paraules.</p>
          <p>Cada dia a les 12 de la nit, es canviarà el repte del dia i començarà un repte nou!</p>
          <p>Cada paraula encertada et donarà un número de punts equivalent la llongitud de caracters de la paraula</p>
          <p>Pots utilitzar l'apartat de pistes si necessites cap mena d'ajuda</p>
          <p>A Estadístiques podràs veure el teu perfil personal, on podràs fer una ullada a les paraules que has encertat i al ranquin global</p>
          <p>També podràs canviar la teva contrasenya si ho necessites!</p>
          <p>Aquest joc es basa en Paraulogic de Vilaweb i en Spelling Bee de The New York Times.</p>
          <b>Referències: </b><br>
          <a href="https://www.vilaweb.cat/paraulogic/">Paraulogic de Vilaweb</a><br>
          <a href="https://spellbee.org/">Spelling Bee de The New York Times</a><br>
         <button onclick="cerrarVentana('funcionament')">Tanca</button>
      </div>
</body>
</html>
