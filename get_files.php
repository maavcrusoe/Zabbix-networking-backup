<?php
    $directorio = '/home/user/backup-config/';
    // Get filename and token from url
    $nombreArchivo = $_GET['archivo'];
    $token = $_GET['token'];

    // Validate token
    if ($token === 'XXXXXX') {
        // If file exists and file is in dir
        $rutaArchivo = $directorio . $nombreArchivo;
        if (file_exists($rutaArchivo) && strpos(realpath($rutaArchivo), realpath($directorio)) === 0)>
            // Set HTTP headers and type content
            header('Content-Type: text/plain'); // Cambia el tipo de contenido según el tipo de archi>
            // Read and show content
            readfile($rutaArchivo);
            exit; // Close execution
        }
    }

    // If not show error message
echo 'Access denied';
?>
