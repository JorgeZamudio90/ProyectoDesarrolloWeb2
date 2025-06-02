<?php

namespace Alexc\ProyectoAgustin\Controllers;


require_once __DIR__ . '/../../vendor/fpdf/fpdf.php';

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Core\Database;
use FPDF;
use PDO;
use Exception;
use Alexc\ProyectoAgustin\Controllers\UsuariosController;
class ReporteController extends BaseController
{
    public function get($params){
        if(!UsuariosController::autorizar()){
            return self::respuestaJson(03,'Acceso denegado. Clave API no valida',401);
        }

        if(!empty($params) && isset($params[0])){
            if($params[1] === 'inscripciones'){
                return self::generarReporteInscripciones($params[0]);
            }else{
                return self::respuestaJson(03, 'Acción no válida', 400);
            }
        }

        return self::respuestaJson(03, 'Faltan parametros validos para el reporte',400);
    }

    public static function generarReporteInscripciones($eventoId)
    {
        try{

        $db = Database::getConnection();

        $stmtData = $db->prepare("SELECT a.nombre, a.correo, a.numControl, i.fecha_registro, e.titulo, e.fecha_inicio, e.fecha_fin, u.nombre AS encargado
                                     FROM alumnos AS a
                                     INNER JOIN inscripciones AS i ON a.id = i.alumno_id
                                     INNER JOIN eventos AS e ON i.evento_id = e.id
                                     INNER JOIN encargados AS u ON e.encargado_id = u.id
                                     WHERE i.evento_id = :eventoId");
        $stmtData->execute([':eventoId' => $eventoId]);
        $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);
        if (!$data) {
            return self::respuestaJson(03, 'No se encontro el evento proporcionado',400);

        }
        // Generar PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $evento=$data[0];
        // Encabezado
        $pdf->Cell(0,10,'Evento: ' . mb_convert_encoding($evento['titulo'],'ISO-8859-1'),0,1);
        $pdf->Cell(0,10,'Fecha Inicio: ' . $evento['fecha_inicio'] . ' | Fecha Fin: ' . $evento['fecha_fin'],0,1);
        $pdf->Cell(0,10,'Encargado: ' . mb_convert_encoding($evento['encargado'],'ISO-8859-1'),0,1);
        $pdf->Ln(5);
        $pdf->Cell(0,0,'        -----------------------------------------------------------------------------------------------------------------------',0,1);
        $pdf->Ln(5);

        // Cabecera de tabla
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,10,'Nombre',1);
        $pdf->Cell(25,10,'NumControl',1);
        $pdf->Cell(50,10,'Correo',1);
        $pdf->Cell(40,10,'Fecha de Inscripción',1);
        $pdf->Cell(20,10,'Asistencia',1);
        $pdf->Ln();

        // Datos de data
        $pdf->SetFont('Arial','',10);
        foreach ($data as $alumno) {
            $pdf->Cell(50,20,mb_convert_encoding($alumno['nombre'],'ISO-8859-1'),1);
            $pdf->Cell(25,20,$alumno['numControl'],1);
            $pdf->Cell(50,20,mb_convert_encoding($alumno['correo'],'ISO-8859-1'),1);
            $pdf->Cell(40,20,$alumno['fecha_registro'],1);
            $pdf->Cell(20,20,'',1);
            $pdf->Ln();
        }

        // Descargar archivo7
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="reporte_inscripciones.pdf"');
        $pdf->Output('I');
        exit;
    }catch(Exception $e ){
        return self::respuestaJson(02, 'Error al generar el reporte: ',500,['error'=>$e->getMessage()]);
    }
}

}
