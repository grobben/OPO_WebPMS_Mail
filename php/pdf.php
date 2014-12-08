<?php

ob_start();

require('helpers/fpdf/fpdf.php');

/**
 * pdf class.
 *
 * @extends FPDF
 */
class pdf extends FPDF {

	/**
	 * makePdf function.
	 *
	 * @access public
	 * @param mixed $data
	 * @param mixed $location
	 * @return void
	 */
	function makePdf($data, $location) {

		// !New Document
		$pdf = new FPDF('P',  'mm', 'A4');

		// !Start Project Pages
		$pdf->AddPage();
		$pdf->SetFillColor(250, 250, 250);
		$pdf->SetDrawColor(100, 100, 100);
		$pdf->SetTextColor(0, 0, 0);

		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(190, 6, 'Projecten', 0, 1, '', false);
		$pdf->Ln();

		foreach ($data['projects'] as $project) {

			$pdf->SetFont('Arial', '', 8);

			// To new page if table doesn't fit
			$y = $pdf->GetY();
			if (($y + $this->line_height) >= 200) {
			    $pdf->AddPage();
			    $y = 0;
			}

			if ($project['deadline'] === 1) {
				$pdf->SetFillColor(255, 165, 0);
				$pdf->SetDrawColor(255, 165, 0);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->MultiCell(190, 6, 'De deadline is morgen', 1, 'C', true);
				$pdf->SetTextColor(0, 0, 0);
			}
			else if ($project['deadline'] === 2) {
				$pdf->SetFillColor(255, 165, 0);
				$pdf->SetDrawColor(255, 165, 0);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->MultiCell(190, 6, 'De deadline is vandaag', 1, 'C', true);
				$pdf->SetFillColor(255, 250, 245);
				$pdf->SetTextColor(0, 0, 0);
			}
			else if ($project['deadline'] === 3) {
				$pdf->SetFillColor(255, 29, 37);
				$pdf->SetDrawColor(255, 29, 37);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->MultiCell(190, 6, $project['days_past_deadline'] . ' dagen voorbij de deadline', 1, 'C', true);
				$pdf->SetFillColor(255, 250, 250);
				$pdf->SetTextColor(0, 0, 0);
			}

			$pdf->SetFont('Arial', 'B', 8);
			$pdf->Cell(150, 12, $project['name'], 1, 0, '', true);
			$pdf->SetFont('Arial', '', 8);
			$pdf->Cell(40, 12,'Deadline' . "\n" . date('d-m-Y', strtotime($project['end'])), 1, 1, '', true);

			if ($project['project_managers'] !== '') {
				$pdf->Cell(50, 12, $project['workers'], 1, 0, '', true);
				$pdf->Cell(50, 12, $project['project_managers'], 1, 0, '', true);
				$pdf->Cell(50, 12, $project['reference'], 1, 0, '', true);
			}
			else {
				$pdf->Cell(75, 12, $project['workers'], 1, 0, '', true);
				$pdf->Cell(75, 12, $project['reference'], 1, 0, '', true);
			}

			$pdf->Cell(40, 12, $project['progress'] . '%', 1, 1, '', true);

			foreach ($project['tasks'] as $task) {
				$pdf->Cell(20, 6, $task['worker'], 'L', 0, '', false);
				$pdf->Cell(170, 6, $task['name'], 'R', 1, '', false);
			}

			if ($project['time_spent'] !== '0 uur') {
				$pdf->MultiCell(190, 6, 'Tijd gespendeerd: ' . $project['time_spent'], 1, 'R', true);
			}
			else {
				$pdf->MultiCell(190, 6, '', 1, 'R', true);
			}

			$pdf->SetFillColor(250, 250, 250);
			$pdf->SetDrawColor(100, 100, 100);
			$pdf->SetTextColor(0, 0, 0);

	        $pdf->Ln();

        }

        // !Start Ticket Pages
        $pdf->AddPage();
		$pdf->SetFillColor(250, 250, 250);
		$pdf->SetDrawColor(100, 100, 100);
		$pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(190, 6, 'Kleine Aanpassingen', 0, 1, '', false);
		$pdf->Ln();

        foreach ($data['tickets'] as $ticket) {

			$pdf->SetFont('Arial', '', 8);

	        $pdf->Cell(20, 6, $ticket['reference'], 1, 0, '', true);
			$pdf->SetFont('Arial', 'B', 8);
	        $pdf->Cell(150, 6, $ticket['subject'], 1, 0, '', true);
			$pdf->SetFont('Arial', '', 8);
	        $pdf->Cell(20, 6, $ticket['worker'], 1, 1, 'C', true);

	        if ($ticket['text']) {
		        $pdf->SetTextColor(100, 100, 100);
		        $pdf->MultiCell(190, 6, $ticket['text'], 1, '', false);
		        $pdf->SetTextColor(0, 0, 0);
	        }

	        $pdf->Ln();

        }

		$pdf->Output('WebPMS_Update_' . date('Ymd') . '.pdf', $location);

		return $pdf;

	}		

}