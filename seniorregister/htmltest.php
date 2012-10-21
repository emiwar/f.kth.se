<?php

require_once('htmlgen.php');

$htmlg = new XhtmlGenerator(false);

$page = $htmlg->xml_doctype() . "\n" .
	$htmlg->begin_html() . "\n" .
		$htmlg->begin_head() .  "\n" .
			$htmlg->title('XhtmlGenerator test page') .  "\n" .
			$htmlg->meta_http_equiv("Content-type", "text/html; charset=utf-8") .  "\n" .
			$htmlg->meta_name("description", "PHP XhtmlGenerator class testing script, by frenchwhale") . "\n" .
			$htmlg->link_stylesheet("main.css") . "\n" .
		$htmlg->end_head() . "\n" .
		$htmlg->begin_body(array("style" => "{background-color: #f0f0ff; }")) . "\n" .
			$htmlg->begin_div('id_1', 'class_1') . "\n" .
				$htmlg->heading(2, "XhtmlGenerator test page") . $htmlg->newline() . "\n" .
				$htmlg->begin_table(array("width" => "400px")) . "\n" .
					$htmlg->begin_row() . 
						$htmlg->begin_cell() . "Cell #1" . $htmlg->end_cell() . 
						$htmlg->begin_cell() . "Cell #2" . $htmlg->end_cell() . 
					$htmlg->end_row() . "\n" .
					$htmlg->begin_row() . $htmlg->cell("Cell #3 should be wider than the two previous ones", array("colspan" => "2")) . $htmlg->end_row() . "\n" .
				$htmlg->end_table() . "\n" .
				$htmlg->begin_form("get", "htmltest.php") . "\n" .
					$htmlg->begin_div('id_2', 'class_2') . "\n" .
						"Input #1: " . $htmlg->input("text", "input1", "Value #1") . $htmlg->newline() . "\n" .
						"Input #2: " . $htmlg->input("checkbox", "input2", "1") . $htmlg->newline() . "\n" .
						$htmlg->input("submit", "", "Submit!") . $htmlg->newline() . "\n" .
					$htmlg->end_div() . "\n" .
				$htmlg->end_form() . "\n" .
				$htmlg->begin_p() . "Paragraph #1" . $htmlg->end_p() . "\n" .
				$htmlg->p("Paragraph #2") . "\n" .
				$htmlg->begin_div('id_3') . "Div #1" . $htmlg->end_div() . "\n" .
				$htmlg->div('', 'class_3', "Div #2") . "\n" .
				$htmlg->emphasis("Hey") . " there..." . $htmlg->newline() . "\n" .
				"...that was a " . $htmlg->strong("newline")  . "." . $htmlg->newline() . "\n" .
			$htmlg->end_div() . "\n" .
		$htmlg->end_body() . "\n" .
	$htmlg->end_html() . "\n";

echo $page;

?>