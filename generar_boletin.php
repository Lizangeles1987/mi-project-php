<?php
require('fpdf.php');
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Validar seguridad: Asegurar que el usuario esté logueado
if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$rol_actual = strtolower(trim($_SESSION['rol'])); // Normalizamos a minúsculas
$periodo_seleccionado = isset($_GET['periodo']) ? intval($_GET['periodo']) : 1;

// 2. Determinar dinámicamente qué estudiante se va a consultar (Control de Accesos Blindado)
if ($rol_actual === 'estudiante') {
    // Si es estudiante, solo puede ver sus propios datos por seguridad (Ignora alteraciones de URL)
    $id_alumno = intval($_SESSION['id']);
} else if ($rol_actual === 'profesor' || $rol_actual === 'docente' || $rol_actual === 'admin' || $rol_actual === 'administrador') {
    // Si es profesor o admin, se lee el alumno que pasen por la URL de manera segura
    if (isset($_GET['id_alumno'])) {
        $id_alumno = intval($_GET['id_alumno']);
    } else {
        die("Error: No se ha especificado el ID del estudiante.");
    }
} else {
    // Cualquier otro rol no autorizado se expulsa
    header("Location: login.php");
    exit();
}

// 3. Obtener datos del alumno mediante consultas preparadas PDO
$stmtUser = $pdo->prepare("SELECT nombre, grado FROM usuarios WHERE id = ? AND rol = 'estudiante'");
$stmtUser->execute([$id_alumno]);
$user_data = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    die("Error: El estudiante solicitado no existe o no es valido.");
}

$alumno_nombre = trim($user_data['nombre']);
$grado = $user_data['grado'];

// 4. Obtener calificaciones del periodo seleccionado
$query = "SELECT materia, nota, logro FROM calificaciones WHERE id_usuario = ? AND periodo = ? ORDER BY materia ASC";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_alumno, $periodo_seleccionado]);
$calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para determinar el desempeño de texto
function obtenerTextoDesempeno($nota) {
    $n = floatval($nota);
    if ($n >= 9.0) return 'Superior';
    if ($n >= 7.5) return 'Alto';
    if ($n >= 6.0) return 'Basico';
    return 'Bajo';
}

// 5. Configurar el PDF estilo institucional
class PDF extends FPDF {
    function Header() {
        // 1. Colocamos el escudo limpio dentro del margen (X=11, Y=11)
        if (file_exists('escudo_colegio.png')) {
            $this->Image('escudo_colegio.png', 11, 11, 50, 0);
        }
        
        // Título Institucional
        $this->SetFont('Arial', 'B', 13);
        $this->SetXY(48, 15);
        $this->Cell(110, 6, 'INSTITUCION EDUCATIVA AULA PRIMARIA', 0, 0, 'L');
        
        // Fecha Emisión (Esquina superior derecha)
        $this->SetFont('Arial', '', 9);
        $this->SetXY(155, 15);
        $this->Cell(45, 6, 'Fecha Emision: ' . date('d/m/Y'), 0, 1, 'R');
        
        // Resolución Oficial
        $this->SetFont('Arial', '', 9);
        $this->SetXY(48, 21);
        $this->Cell(110, 6, 'Resolucion Oficial No. 12345', 0, 0, 'L');
        
        // Periodo destacado
        global $periodo_seleccionado;
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(26, 86, 219); // Azul corporativo
        $this->SetXY(155, 21);
        $this->Cell(45, 6, 'Periodo: ' . $periodo_seleccionado . ' Periodo Academico', 0, 1, 'R');
        $this->SetTextColor(0, 0, 0);
        
        // 2. Marco perimetral institucional dibujado por encima
        $this->Rect(10, 10, 190, 260);
        
        // Establecemos el inicio del contenido escolar abajo de la cabecera
        $this->SetY(38); 
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
}

// Inicialización del reporte FPDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetFont('Arial', '', 10); 

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(12, 12, 12);

// Bloque de información del estudiante
$pdf->Ln(2); 
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(95, 5, 'ESTUDIANTE', 0, 0, 'L');
$pdf->Cell(95, 5, 'GRADO / CURSO', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(95, 7, utf8_decode($alumno_nombre), 0, 0, 'L');
$pdf->Cell(95, 7, utf8_decode($grado . ' de Primaria'), 0, 1, 'L');
$pdf->Ln(6);

// Encabezados de la Tabla
$pdf->SetFillColor(24, 30, 49); // Azul oscuro institucional
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell(45, 8, 'ASIGNATURA', 1, 0, 'L', true);
$pdf->Cell(15, 8, 'NOTA', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'DESEMPENO', 1, 0, 'C', true);
$pdf->Cell(101, 8, 'LOGRO OBTENIDO EN EL PERIODO', 1, 1, 'L', true);

// Cuerpo de la Tabla de calificaciones
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Arial', '', 9);

if (empty($calificaciones)) {
    $pdf->Cell(186, 12, 'No se encontraron calificaciones registradas en este periodo.', 1, 1, 'C');
} else {
    foreach ($calificaciones as $row) {
        $desempeno_texto = obtenerTextoDesempeno($row['nota']);
        
        // Calcular altura dinámica por si el logro es muy largo
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        // Columna de Logro calculada con MultiCell primero
        $pdf->SetXY($x + 85, $y);
        $pdf->MultiCell(101, 6, utf8_decode($row['logro']), 1, 'L');
        $endY = $pdf->GetY();
        $lineHeight = $endY - $y;
        
        // Reposicionar para pintar las columnas estáticas con la altura calculada exacta
        $pdf->SetXY($x, $y);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(45, $lineHeight, utf8_decode($row['materia']), 1, 0, 'L');
        $pdf->Cell(15, $lineHeight, number_format($row['nota'], 1), 1, 0, 'C');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(25, $lineHeight, utf8_decode($desempeno_texto), 1, 1, 'C');
        
        $pdf->SetY($endY); // Reajustar puntero al final de la fila procesada
    }
}

$pdf->Output('I', 'Boletin_Periodo_' . $periodo_seleccionado . '.pdf');
?>