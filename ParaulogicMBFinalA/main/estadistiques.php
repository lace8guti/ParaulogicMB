<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

session_start();

// newPDO: connectem amb la BD
include("../other_resources/BBDD_credentials2.php");

try {
    //primer parámetro: cadena de conexión, es String mediante concanetaciones
    //mysql:host='nombre del host';dbname='nombre de la BD',usuario,contraseña
    $bd = new PDO('mysql:host='.$host.';dbname='.$database, 
                     $usuari, $password); 	   
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	echo "No s'ha pogut connectar amb la Base de dades";
        echo $e->getMessage();
	exit;
}


// M'he connectat correctament!

//var_dump($_SESSION);


$sql= "SELECT word_guessed, date FROM challenges WHERE user_id =".$_SESSION['id'];
$query = $bd->query($sql);

function getScoreRanking() {
    // Conectar a la base de datos
    include("../other_resources/BBDD_credentials2.php");

    $conn = new mysqli($host, $usuari, $password, $database);

    // Verificar si se ha establecido una conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Preparar la consulta SQL
    $sql = "SELECT users.username, users.total_score, COUNT(challenges.word_guessed) AS words_guessed , users.id
            FROM users
            LEFT JOIN challenges ON users.id = challenges.user_id
            GROUP BY users.id
            ORDER BY users.total_score DESC";

    // Ejecutar la consulta y obtener los resultados
    $result = $conn->query($sql);

    // Crear un array para almacenar los resultados
    $ranking = array();

    // Iterar por los resultados y agregarlos al array
    while ($row = $result->fetch_assoc()) {
        $ranking[] = array(
            'username' => $row['username'],
            'score' => $row['total_score'],
            'words_guessed' => $row['words_guessed'],
            'id' => $row['id']
        );
    }

    // Cerrar la conexión y devolver el ranking
    $conn->close();
    return $ranking;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Estadístiques</title>
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
			width: 450px;
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
                .infoParaules{
                    
                    
                }
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
.estadistiques{
    padding-left: 1000px;
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
      <img src="../images/TitolParaulogic.png" width="100" height="40" alt="Imatge del logo del Paraulogic">
    </a>
    
    <!-- enlaces -->
    <div class="collapse navbar-collapse" id="opciones">   
      <ul class="navbar-nav">
          <a href="../user_management/nova_password.php">Canvia la teva contrasenya</a>
        </li>
        <li class="nav-item">
            <a class="estadistiques" href="estadistiques.php">Estadístiques</a>
        </li>
        <li class="nav-item">
          <a href="../user_management/logout.php">Tanca la sessió</a>
        </li>
      </ul>
    </div>
  </nav>
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>    
    
<div class='container'>
  <table class='table table-stripped'>
   <thead>
    <tr">
        <th scope="col">Paraula</th>
        <th scope="col">Data</th>
        <th scope="col">Definició</th>
        <th scope="col">Sinònims</th>
    </tr>
   </thead>
   <tbody>

<?php
    $resultat=$query->fetchAll(PDO::FETCH_ASSOC);
   foreach ($resultat as $super){
       $palabra=strtolower($super['word_guessed']);
                
                $url = 'https://ca.wiktionary.org/wiki/'.urlencode($palabra);
                
                $url2= 'https://www.softcatala.org/diccionari-de-sinonims/paraula/'.urlencode($palabra).'/';
       
       echo "<tr>";
       echo "<td>".$super['word_guessed']."</td>";
       echo "<td>".$super['date']."</td>";
       echo "<td>".'Visita '.'<a href="' . $url . '" target="_blank">' . $palabra . ' </a> per aprendre més sobre aquesta paraula'."</td>";
       echo "<td>".'Visita '.'<a href="' . $url2 . '" target="_blank">' . $palabra . ' </a> per veure sinònims de la paraula'."</td>";
       echo "</tr>";
   }


?>
       
   </tbody>
</table>
</div>
  <div class='container'>
<table class='table table-stripped'>
    <thead>
  <tr">
    <th scope="col">Nom d'usuari</th>
    <th scope="col">Puntuació total</th>
    <th scope="col">Paraules encertades</th>
    <?php
    if($_SESSION['username']=='admin'){
    echo "<th scope='col'>Eliminar usuari</th>";
    }
    ?>
  </tr>
  </thead>
  <tbody>
  <?php foreach (getScoreRanking() as $user): ?>
  <tr>
    <td><?php echo $user['username']; ?></td>
    <td><?php echo $user['score']; ?></td>
    <td><?php echo $user['words_guessed']; ?></td>
    <?php
    if ($_SESSION['username'] == 'admin') {
    if ($_SESSION['id'] != $user['id']) {
        echo "<td><a href='../user_management/delete.php?id=" . $user['id'] . "'>Esborrar</a></td>";
    }
}
    ?>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
</body>
</html>
