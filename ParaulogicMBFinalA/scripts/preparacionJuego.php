<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */


///////////////////////////


function crearBaseDeDatos($rutaArchivoSQL, $servidor, $usuario, $contraseña, $nombreBD) {
  // Conectarse a la base de datos
  $conexion = mysqli_connect($servidor, $usuario, $contraseña);
  if (!$conexion) {
    die("La conexión falló: " . mysqli_connect_error());
  }

  // Seleccionar la base de datos
  $baseDeDatosSeleccionada = mysqli_select_db($conexion, $nombreBD);
  if (!$baseDeDatosSeleccionada) {
    // Si la base de datos no existe, la creamos
    $crearBD = "CREATE DATABASE $nombreBD";
    mysqli_query($conexion, $crearBD);
    mysqli_select_db($conexion, $nombreBD);
  }

  // Leer el archivo SQL
  $contenidoSQL = file_get_contents($rutaArchivoSQL);

  // Ejecutar el código SQL
  $resultado = mysqli_multi_query($conexion, $contenidoSQL);

  // Comprobar si hubo errores
  if (!$resultado) {
    die("Error al crear la base de datos: " . mysqli_error($conexion));
  }

  // Cerrar la conexión a la base de datos
  mysqli_close($conexion);

  // Devolver el mensaje de éxito
  return "Base de datos creada exitosamente";
}

//crearBaseDeDatos("../other_resources/ParaulogicMBFinal.sql", "localhost", "root", "power", "ParaulogicMBFinal");

///////////////////////////////////////////////

function crearArrayDePalabras($archivoTexto) {
    // Abres el archivo .txt
    $archivo = fopen($archivoTexto, "r");

// Inicializas el array $palabras
    $palabras = array();

// Recorres el archivo línea por línea
    while (!feof($archivo)) {
        // Lees cada línea del archivo
        $linea = fgets($archivo);

        // Divides la línea en palabras utilizando el espacio como separador
        $palabras_linea = explode(" ", $linea);

        // Añades cada palabra al array $palabras
        foreach ($palabras_linea as $palabra) {
            $palabras[] = $palabra;
        }
    }

    // Eliminamos "·" de las palabras
    foreach ($palabras as &$palabra) {
        $palabra = str_replace("·", "", $palabra);
    }

// Utilizar array_map y trim para eliminar los espacios en blanco de cada entrada del array
    $palabras = array_map('trim', $palabras);
// Cierras el archivo
    fclose($archivo);
    return $palabras;
}

// Definimos el path del archivo de texto con las palabras y un array $palabras con todas ellas.
$archivoTexto="../other_resources/DISC2-LP.txt";
$palabras= crearArrayDePalabras($archivoTexto);

////////////////////////////////////

function generarStringAleatorio() {
  // Definimos un array de consonantes y otro de vocales  
  $consonantes = array(
    'B', 'C', 'D', 'F', 'G', 'H', 'L', 'M', 'N', 'P', 
    'R', 'S', 'T', 'V', 'X', 'Y', 'J', 'Q'
  );
  $vocales = array('A', 'E', 'I', 'O', 'U', 'E', 'A');

  // Escoger dos vocales al azar
  $vocal1 = $vocales[array_rand($vocales)];
  $vocal2 = $vocales[array_rand($vocales)];

  // Asegurar que las dos vocales sean diferentes
  while ($vocal1 == $vocal2) {
    $vocal2 = $vocales[array_rand($vocales)];
  }

  // Aumentar la frecuencia de ciertas consonantes
  $consonantesAleatorias = array();
  while (count($consonantesAleatorias) < 5) {
    $consonante = $consonantes[array_rand($consonantes)];
    if (!in_array($consonante, $consonantesAleatorias)) {
      if (in_array($consonante, array('S', 'R', 'L', 'N', 'T'))) {
        // Aumentar la frecuencia de ciertas consonantes
        $consonantesAleatorias[] = $consonante;
      } else {
        // Reducir la frecuencia de ciertas consonantes
        if (mt_rand(1, 20) <= 19) {
          $consonantesAleatorias[] = $consonante;
        }
      }
    }
  }

  // Combinar las vocales y consonantes en un array
  $stringAleatorioArray = array($vocal1, $vocal2);
  shuffle($consonantesAleatorias);
  $stringAleatorioArray = array_merge($stringAleatorioArray, $consonantesAleatorias);

  // Convertir el array en un string y devolverlo
  return implode('', $stringAleatorioArray);
}

/////////////////////////////////////////////////////

function generarPangramas($miStringAleatorio, $letraAleatoria, $palabras) {
    // Definir la expresión regular: EL PATRÓN
    $patron = '/^['.$miStringAleatorio.']*'.$letraAleatoria.'['.$miStringAleatorio.']*$/';
    
    // Filtrar las palabras del array que cumplan el patrón y tengan al menos 4 caracteres
    $pangramas = array_filter($palabras, function($palabra) use ($patron) {
        return preg_match($patron, $palabra) && strlen($palabra) >= 5;
    });

    // Ordenar los pangramas alfabéticamente
    sort($pangramas);

    // Tomar los primeros 30 pangramas
    $pangramas = array_slice($pangramas, 0, 30);

    // Si no hay suficientes pangramas, mostrar un mensaje de aviso
    if (count($pangramas) < 30) {
        /*
        echo "No se han podido generar 30 pangramas con la combinación de letras proporcionada";
        echo "<br>";
        */
    }

    // Devolver el array de pangramas
    return $pangramas;
}

/////////////////////////////////

function generarPatronesValidos($palabras) {
    // Definir un array vacío
    $combinacionesAceptables = array();
    // Utilizando las funciones anteriores, creamos un bucle que continúe la iteración hasta que genere 365 patrones válidos
    while (count($combinacionesAceptables) < 365) {
        $miStringAleatorio = generarStringAleatorio();
        $letraAleatoria = $miStringAleatorio[mt_rand(0, 6)];
        $pangramas = generarPangramas($miStringAleatorio, $letraAleatoria, $palabras);
        // Almacena los patrones válidos en un array de $combinacionesAceptables, de otra forma, los descarta 
        if (count($pangramas) == 30) {
            $patron = '/^['.$miStringAleatorio.']*'.$letraAleatoria.'['.$miStringAleatorio.']*$/';
            $$patron = $patron;
            $combinacionesAceptables[] = $patron;
        }
    }
    return $combinacionesAceptables;
}
//////////////////////////////////////////////////////////////////

function generarPangramasValidos($patronValido, $palabras) {
    // Definir la expresión regular
    $patron = $patronValido;

    // Filtrar las palabras del array que cumplan el patrón y tengan al menos 4 caracteres
    $pangramas = array_filter($palabras, function($palabra) use ($patron) {
        return preg_match($patron, $palabra) && strlen($palabra) >= 5;
    });

    // Ordenar los pangramas alfabéticamente
    sort($pangramas);

    // Tomar los primeros 30 pangramas
    $pangramas = array_slice($pangramas, 0, 30);

    // Si no hay suficientes pangramas, mostrar un mensaje de aviso
    if (count($pangramas) < 30) {
        /*
        echo "No se han podido generar 30 pangramas con la combinación de letras proporcionada";
        echo "<br>";
        */
    }

    // Devolver el array de pangramas
    return $pangramas;
}

/////////////////////////////////////////////////////////////

// Definimos $combinacionesAceptables y $pangramas para usarlos parámetros de la función insertarPatronesYPangramas
$combinacionesAceptables = generarPatronesValidos($palabras);

////////////////////////////////////////////////////////////////

function insertarPatronesYPangramas($combinacionesAceptables,$palabras) {
    include("../other_resources/BBDD_credentials2.php");

    $conn = new mysqli($host, $usuari, $password, $database);

    // Verificar si hay errores de conexión
    if ($conn->connect_error) {
        die("La conexión a la base de datos falló: " . $conn->connect_error);
    }

    // Preparar la sentencia INSERT
    //$stmt = $conn->prepare("INSERT INTO patterns (pattern) VALUES (?)");
    $stmt = $conn->prepare("INSERT INTO patterns (pattern, created_at) VALUES (?, CURDATE())");

    // Verificar si hay errores en la preparación de la sentencia
    if (!$stmt) {
        die("La preparación de la sentencia falló: " . $conn->error);
    }

    // Recorrer cada patrón generado y ejecutar la sentencia INSERT
    foreach ($combinacionesAceptables as $combinacion) {
        // Asignar los parámetros de la sentencia preparada
        $stmt->bind_param("s", $combinacion);

        // Ejecutar la sentencia preparada
        if (!$stmt->execute()) {
            echo "Error al insertar el patrón: " . $conn->error;
        } else {
            // Obtener el id del último patrón insertado
            $pattern_id = $conn->insert_id;

            // Generar el array de pangramas para el patrón actual
            $pangramas = generarPangramasValidos($combinacion, $palabras);

            // Recorrer los pangramas y hacer inserts en la tabla valid_words
            foreach ($pangramas as $pangrama) {
                $words = explode(' ', $pangrama);
                foreach ($words as $word) {
                    $stmt_valid_words = $conn->prepare("INSERT INTO valid_words (pattern_id, word) VALUES (?, ?)");

                    if (!$stmt_valid_words) {
                        die("La preparación de la sentencia falló: " . $conn->error);
                    }
                    $stmt_valid_words->bind_param("is", $pattern_id, $word);
                    if (!$stmt_valid_words->execute()) {
                        echo "Error al insertar la palabra: " . $conn->error;
                    }
                    $stmt_valid_words->close();
                }
            }
        }
    }

    // Cerrar la sentencia preparada y la conexión a la base de datos
    $stmt->close();
    $conn->close();
}

// Llamamos a la función insertarPatronesYPangramas
insertarPatronesYPangramas($combinacionesAceptables, $palabras);

header("location: ../user_management/login.php");
?>