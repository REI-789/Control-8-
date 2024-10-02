<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambia esto
$password = "Tupasword"; // Cambia esto
$dbname = "organizacion";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'registrar_proyecto':
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $presupuesto = $_POST['presupuesto'];
                $fecha_inicio = $_POST['fecha_inicio'];
                $fecha_fin = $_POST['fecha_fin'];

                $sql = "INSERT INTO PROYECTO (nombre, descripcion, presupuesto, fecha_inicio, fecha_fin) VALUES ('$nombre', '$descripcion', '$presupuesto', '$fecha_inicio', '$fecha_fin')";
                $conn->query($sql);
                break;

            case 'registrar_donante':
                $nombre = $_POST['nombre'];
                $email = $_POST['email'];
                $direccion = $_POST['direccion'];
                $telefono = $_POST['telefono'];

                $sql = "INSERT INTO DONANTE (nombre, email, direccion, telefono) VALUES ('$nombre', '$email', '$direccion', '$telefono')";
                $conn->query($sql);
                break;

            case 'registrar_donacion':
                $monto = $_POST['monto'];
                $fecha = $_POST['fecha'];
                $id_proyecto = $_POST['id_proyecto'];
                $id_donante = $_POST['id_donante'];

                $sql = "INSERT INTO DONACION (monto, fecha, id_proyecto, id_donante) VALUES ('$monto', '$fecha', '$id_proyecto', '$id_donante')";
                $conn->query($sql);
                break;
        }
    }
}

// Consultar proyectos con más de dos donaciones
$sql_proyectos = "SELECT p.nombre, COUNT(d.id_donacion) AS num_donaciones, SUM(d.monto) AS total_recaudado
                  FROM PROYECTO p
                  LEFT JOIN DONACION d ON p.id_proyecto = d.id_proyecto
                  GROUP BY p.id_proyecto
                  HAVING num_donaciones > 2";

$result_proyectos = $conn->query($sql_proyectos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Organización sin Fines de Lucro</title>
    <script>
        function validarProyecto() {
            const nombre = document.forms["proyectoForm"]["nombre"].value;
            if (nombre == "") {
                alert("El nombre del proyecto es requerido");
                return false;
            }
        }
    </script>
</head>
<body>
    <h1>Gestión de Proyectos y Donaciones</h1>

    <!-- Formulario para registrar proyecto -->
    <h2>Registrar Proyecto</h2>
    <form name="proyectoForm" action="" method="post" onsubmit="return validarProyecto()">
        <input type="hidden" name="accion" value="registrar_proyecto">
        Nombre: <input type="text" name="nombre" required><br>
        <br>
        Descripción: <textarea name="descripcion"></textarea><br>
        <br>
        Presupuesto: <input type="number" step="0.01" name="presupuesto"><br>
        <br>
        Fecha de Inicio: <input type="date" name="fecha_inicio"><br>
        <br>
        Fecha de Fin: <input type="date" name="fecha_fin"><br>
        <br>
        <input type="submit" value="Registrar">
    </form>

    <!-- Formulario para registrar donante -->
    <h2>Registrar Donante</h2>
    <form action="" method="post">
        <input type="hidden" name="accion" value="registrar_donante">
        Nombre: <input type="text" name="nombre" required><br>
        <br>
        Email: <input type="email" name="email" required><br>
        <br>
        Dirección: <input type="text" name="direccion"><br>
        <br>
        Teléfono: <input type="text" name="telefono"><br>
        <br>
        <input type="submit" value="Registrar">
    </form>

    <!-- Formulario para registrar donación -->
    <h2>Registrar Donación</h2>
    <form action="" method="post">
        <input type="hidden" name="accion" value="registrar_donacion">
        Monto: <input type="number" step="0.01" name="monto" required><br>
        <br>
        Fecha: <input type="date" name="fecha" required><br>
        <br>
        Proyecto ID: <input type="int" name="id_proyecto" required><br>
        <br>
        Donante ID: <input type="number" name="id_donante" required><br>
        <br>
        <input type="submit" value="Registrar">
    </form>

    <!-- Mostrar proyectos con más de 2 donaciones -->
    <h2>Proyectos con más de 2 donaciones:</h2>
    <?php if ($result_proyectos->num_rows > 0): ?>
        <ul>
        <?php while($row = $result_proyectos->fetch_assoc()): ?>
            <li>Proyecto: <?php echo $row["nombre"]; ?> - Donaciones: <?php echo $row["num_donaciones"]; ?> - Total Recaudado: <?php echo $row["total_recaudado"]; ?></li>
        <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No hay proyectos con más de 2 donaciones.</p>
    <?php endif; ?>

    <!-- Nueva sección: Donaciones Recientes -->
    <h2>Donaciones Recientes</h2>
    <table>
        <tr>
            <th>Donante</th>
            <th>Monto</th>
            <th>Fecha</th>
        </tr>
        <?php
        $sql_donaciones = "SELECT d.nombre, don.monto, don.fecha FROM DONACION don JOIN DONANTE d ON don.id_donante = d.id_donante ORDER BY don.fecha DESC";
        $result_donaciones = $conn->query($sql_donaciones);
        while($row = $result_donaciones->fetch_assoc()) {
            echo "<tr><td>{$row['nombre']}</td><td>{$row['monto']}</td><td>{$row['fecha']}</td></tr>";
        }
        ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>