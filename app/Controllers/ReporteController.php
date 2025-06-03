<?php

namespace Alexc\ProyectoAgustin\Controllers;
use PHPMailer\PHPMailer\PHPMailer;
require_once __DIR__ . '/../../vendor/fpdf/fpdf.php';

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Core\Database;
use FPDF;
use PDO;
use Exception;
use Alexc\ProyectoAgustin\Controllers\UsuariosController;
use Alexc\ProyectoAgustin\Models\Evento;
use Alexc\ProyectoAgustin\Models\Encargado;
class ReporteController extends BaseController
{
    public function get($params){
        if(!UsuariosController::autorizar()){
            return self::respuestaJson(03,'Acceso denegado. Clave API no valida',401);
        }

        if(!empty($params) && isset($params[0])){
            if(isset($params[1]) && $params[1] === 'inscripciones'){
                return self::generarReporteInscripciones($params[0]);
            }else{
                return self::respuestaJson(03, 'Acción no válida', 400);
            }
        }

        return self::respuestaJson(03, 'Faltan parametros validos para el reporte',400);
    }

    public static function generarReporteInscripciones($eventoId)
    {
        try {
            $db = Database::getConnection();

            $stmtData = $db->prepare("
                SELECT 
                    a.nombre, a.correo, a.numControl, 
                    i.fecha_registro, 
                    e.titulo, e.fecha_inicio, e.fecha_fin, 
                    u.nombre AS encargado
                FROM alumnos AS a
                INNER JOIN inscripciones AS i ON a.id = i.alumno_id
                INNER JOIN eventos AS e ON i.evento_id = e.id
                INNER JOIN encargados AS u ON e.encargado_id = u.id
                WHERE i.evento_id = :eventoId
            ");
            $stmtData->execute([':eventoId' => $eventoId]);
            $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            if (!$data) {
                return self::respuestaJson(03, 'No se encontró el evento proporcionado', 400);
            }

            $evento = $data[0];
            $slug = self::generarNombreArchivo($evento['titulo']); // "Titulo.pdf"
            $rutaArchivo = __DIR__ . '/../../storage/ReporteInscripciones_' . $slug;

            // Generar PDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Evento: ' . mb_convert_encoding($evento['titulo'], 'ISO-8859-1'), 0, 1);
            $pdf->Cell(0, 10, 'Fecha Inicio: ' . $evento['fecha_inicio'] . ' | Fecha Fin: ' . $evento['fecha_fin'], 0, 1);
            $pdf->Cell(0, 10, 'Encargado: ' . mb_convert_encoding($evento['encargado'], 'ISO-8859-1'), 0, 1);
            $pdf->Ln(5);
            $pdf->Cell(0, 0, str_repeat('-', 119), 0, 1);
            $pdf->Ln(5);

            // Cabecera de tabla
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(50, 10, 'Nombre', 1);
            $pdf->Cell(25, 10, 'NumControl', 1);
            $pdf->Cell(50, 10, 'Correo', 1);
            $pdf->Cell(40, 10, 'Fecha de Inscripción', 1);
            $pdf->Cell(20, 10, 'Asistencia', 1);
            $pdf->Ln();

            // Datos de alumnos
            $pdf->SetFont('Arial', '', 10);
            foreach ($data as $alumno) {
                $pdf->Cell(50, 20, mb_convert_encoding($alumno['nombre'], 'ISO-8859-1'), 1);
                $pdf->Cell(25, 20, $alumno['numControl'], 1);
                $pdf->Cell(50, 20, mb_convert_encoding($alumno['correo'], 'ISO-8859-1'), 1);
                $pdf->Cell(40, 20, $alumno['fecha_registro'], 1);
                $pdf->Cell(20, 20, '', 1);
                $pdf->Ln();
            }

            $pdf->Output('F', $rutaArchivo);

            // Enviar PDF al navegador
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $slug . '"');
            readfile($rutaArchivo);
            exit;
        } catch (Exception $e) {
            return self::respuestaJson(02, 'Error al generar el reporte', 500, ['error' => $e->getMessage()]);
        }
    }

    public function post($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válida', 401);
        }

        if (isset($params[0], $params[1])) {
            if ($params[1] === 'subir') {
                return $this->subirArchivoFTP($params[0]);
            } elseif ($params[1] === 'enviar') {
                return $this->enviarCorreoReporte($params[0]);
            }
        }

        return self::respuestaJson(03, 'Faltan parámetros válidos. Este método solo permite "subir" para FTP y "enviar" para correo', 400);
    }

    public function subirArchivoFTP($eventoId)
    {
        $ftp_host = "127.0.0.1";
        $ftp_user = "apiUser";
        $ftp_pass = "apiuser123";

        $evento = Evento::obtenerPorId($eventoId);
        if (!$evento) {
            return self::respuestaJson(03, 'Evento no encontrado', 404);
        }

        $slug = self::generarNombreArchivo($evento['titulo']); // "Titulo.pdf"
        $nombreRemoto = 'ReporteInscripciones_' . $slug;
        $archivoLocal = __DIR__ . '/../../storage/ReporteInscripciones_' . $slug;
        if(!file_exists($archivoLocal)){
            return self::respuestaJson(2,"El archivo local no existen trata de generarlo usando la funciona GET",404);
        }

        // Conectar FTP
        $ftp_conn = ftp_connect($ftp_host);
        if (!$ftp_conn) {
            return self::respuestaJson(2, "No se pudo conectar al servidor FTP", 401);
        }

        $login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
        if (!$login) {
            ftp_close($ftp_conn);
            return self::respuestaJson(3, "Error al iniciar sesión en el servidor FTP", 401);
        }

        // Subir archivo
        if (ftp_put($ftp_conn, $nombreRemoto, $archivoLocal, FTP_BINARY)) {
            ftp_close($ftp_conn);
            return self::respuestaJson(1, "Archivo almacenado correctamente en el servidor FTP");
        } else {
            ftp_close($ftp_conn);
            return self::respuestaJson(2, "Error al subir el archivo al servidor FTP", 500);
        }
    }

    public function enviarCorreoReporte($eventoId)
    {
        $mail = new PHPMailer(true);
        $evento = Evento::obtenerEncargadoEventoId($eventoId);
        if (!$evento) {
            return self::respuestaJson(03, 'Evento no encontrado', 404);
        }
        try {
            $slug = self::generarNombreArchivo($evento['titulo']); 
            $rutaAdjunto = __DIR__ . '/../../storage/ReporteInscripciones_' . $slug;

            if(!file_exists($rutaAdjunto)){
                return self::respuestaJson(2,"El archivo local no existen trata de generarlo usando la funciona GET",404);
            }

            $mail->setFrom('jesuscda123@gmail.com', 'Nombre');
            $mail->addAddress('l21390336@chetumal.tecnm.mx', 'Destinatario');
            $mail->Subject = 'Reporte de Inscripciones del evento ' . $evento['titulo'];
            $mail->Body    = 'Adjunto el reporte solicitado.';
            $mail->addAttachment($rutaAdjunto);

            $mail->send();
            return self::respuestaJson(1, "Correo enviado correctamente al encargado");
        } catch (Exception $e) {
            return self::respuestaJson(2, "No se pudo enviar el correo. Error: {$mail->ErrorInfo}", 500);
        }
    }

    public static function generarNombreArchivo($titulo)
    {
        return preg_replace('/[^A-Za-z0-9-_]/', '_', $titulo) . '.pdf';
    }




}
