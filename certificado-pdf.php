<?php
require_once 'fpdf/fpdf.php';
require_once 'config.php';
checkAuth('aluno');


$stmt = $pdo->prepare("
    SELECT 
        u.nome, c.nome as curso_nome, c.carga_horaria_necessaria,
        COALESCE(SUM(CASE WHEN p.status = 'concluido' THEN p.horas_contabilizadas ELSE 0 END), 0) as horas_totais
    FROM usuarios u
    JOIN cursos c ON u.curso_id = c.id
    LEFT JOIN participacoes p ON u.id = p.aluno_id
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->execute([$_SESSION['user_id']]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if($aluno['horas_totais'] < $aluno['carga_horaria_necessaria']) {
    header("Location: dashboard_aluno.php");
    exit;
}




$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// borda interna e externa
$pdf->Image('img\fundo-certificado.png', 0, 0, 297, 210);
$pdf->SetLineWidth(1);
$pdf->Rect(10, 10, 277, 190, 'D'); 
$pdf->SetLineWidth(0.5);
$pdf->Rect(15, 15, 267, 180, 'D');

// cabeçalho
$pdf->Ln(30);
$pdf->SetFont('Arial', 'B', 30);
// converte UTF-8 para o formato aceito pelo FPDF (ISO-8859-1)
$pdf->Cell(0, 20, iconv('UTF-8', 'windows-1252', 'CERTIFICADO DE CONCLUSÃO'), 0, 1, 'C');

// body do certificado
$pdf->Ln(15);
$pdf->SetFont('Arial', '', 18);
$pdf->Cell(0, 10, 'Certificamos que', 0, 1, 'C');

// Nome do Aluno
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 25);
$pdf->Cell(0, 15, iconv('UTF-8', 'windows-1252', strtoupper($aluno['nome'])), 0, 1, 'C');

// Texto do Curso
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 18);
$pdf->MultiCell(0, 10, iconv('UTF-8', 'windows-1252', 'completou com êxito o programa de extensão acadêmica do curso'), 0, 'C');

// Nome do Curso
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 22);
$pdf->MultiCell(0, 10, iconv('UTF-8', 'windows-1252', $aluno['curso_nome']), 0, 'C');

// Carga Horária
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Carga Horária Cumprida: ' . $aluno['horas_totais'] . ' horas'), 0, 1, 'C');

// Rodapé e Data
$pdf->SetY(160);
$pdf->SetFont('Arial', '', 12);
$data_hoje = date('d') . ' de ' . date('F') . ' de ' . date('Y');
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Emitido em ' . $data_hoje), 0, 1, 'C');

$pdf->SetY(175);
$pdf->SetFont('Arial', 'I', 10);
$texto_footer = "Certificado válido conforme normas de extensão acadêmica.\nDocumento gerado pelo Sistema de Extensão Acadêmica.";
$pdf->MultiCell(0, 5, iconv('UTF-8', 'windows-1252', $texto_footer), 0, 'C');

// saida do PDF
$pdf->Output('I', 'Certificado.pdf'); 

?>
