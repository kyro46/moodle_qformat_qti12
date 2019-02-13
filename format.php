<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Code for importing IMS QTI 1.2 questions into Moodle.
 *
 * @package   qformat_qti12
 * @author    Christoph Jobst <cjobst@wifa.uni-leipzig.de>
 * @copyright 2019, University Leipzig
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class qformat_qti12 extends qformat_based_on_xml {
	/** @var string path to the temporary directory. */
	public $tempdir = '';
	
	public function provide_import() {
		return true;
	}
	
	public function mime_type() {
		return mimeinfo('type', '.zip');
	}
	
	public function get_filecontent($path) {
		$fullpath = $this->tempdir . '/' . $path;
		if (is_file($fullpath) && is_readable($fullpath)) {
			return file_get_contents($fullpath);
		}
		return false;
	}
	
	public function readdata($filename) {
		error_log("readdata reached");
		
		//using Blackboard-ZIP-Import as reference
		$uniquecode = time();
		$this->tempdir = make_temp_directory('qti12_import/' . $uniquecode);
		$basename = basename("$filename", ".zip");
		
		if (is_readable($filename)) {
			if (!copy($filename, $this->tempdir . '/' . $basename . '.zip')) {
				$this->error(get_string('cannotcopybackup', 'question'));
				fulldelete($this->tempdir);
				return false;
			}
			$packer = get_file_packer('application/zip');
			if ($packer->extract_to_pathname($this->tempdir . '/' . $basename . '.zip', $this->tempdir)) {
				//$dom = new DomDocument();
				
				//the x_x_qti_x.xml contains questions, the x_x_qpl_x.xml general information about the pool
				//if (!$dom->load($this->tempdir . '/' . $basename . '/' . str_replace('qpl','qti', $basename) . '.xml')) {
				if (!$questestinterop = simplexml_load_file($this->tempdir . '/' . $basename . '/' . str_replace('qpl','qti', $basename) . '.xml')) {
					$this->error(get_string('errorreadxml', 'qformat_qti12'));
					fulldelete($this->tempdir);
					return false;
				}
				//$xpath = new DOMXPath($dom);
			} else {
				$this->error(get_string('cannotunzip', 'question'));
				fulldelete($this->temp_dir);
			}
		} else {
			$this->error(get_string('cannotreaduploadfile', 'error'));
			fulldelete($this->tempdir);
		}

		return $questestinterop;

	}

	private function get_question_type($strqtype) {
		$mdlquestiontype = '';
		switch ($strqtype) {
			case 'SINGLE CHOICE QUESTION':
				$mdlquestiontype = 'multichoice';
				break;
			default :
				$mdlquestiontype = '';
				break;
		}
		
		return $mdlquestiontype;
	}
	
	public function import_headers($item) {
		error_log('import_headers reached');
		
		// Initalise question object.
		$question = $this->defaultquestion();
		
		$qtext = trim(clean_param($item->presentation->flow->material->mattext, PARAM_TEXT));
		$qname = (string) $item['title'];
		
		if (strlen($qname) == 0) {
			$qname = $qtext;
		}
		
		if (strlen($qtext) == 0) {
			$qtext = $qname;
		}
		
		$question->name = $qname;
		$question->questiontext = $qtext;
		$question->questiontextformat = FORMAT_HTML;
		$question->generalfeedback = '';
		$question->generalfeedbackformat = FORMAT_HTML;
		$question->feedbackformat = FORMAT_HTML;
				
		// Get the content type for this question.
		$contenttype = clean_param($item->presentation->flow->material->mattext['texttype'], PARAM_TEXT);
		
		switch ($contenttype) {
			case 'text/plain':
				$question->questiontextformat = 2; // Plain_text.
				break;
			case 'text/html':
				$question->questiontextformat = 1; // HTML.
				break;
			default:
				echo get_string('contenttypenotset', 'qformat_qti12');
				$question->questiontextformat = 1; // HTML.
		}

		return $question;
	}
	public function import_multichoice($item) {
		// Common question headers.
		$question = $this->import_headers($item);
		
		// Header parts particular to multichoice.
		$question->qtype = 'multichoice';
		$question->answernumbering = 'abc';
		$question->single = 1;
		$question->shuffleanswers = (isset($item->presentation->flow->responde_lid->render_choice['shuffle']) && $item->presentation->flow->responde_lid->render_choice['shuffle'] == 'Yes') ? 1 : 0;
				
		foreach ($item->presentation->flow->response_lid->render_choice->response_label as $answer) {
			$question->answer[] = $this->text_field($answer->material->mattext);
		}
		
		$question->defaultmark = 0;
		foreach ($item->resprocessing->respcondition as $respcondition) {
			if ($respcondition->setvar['action'] == 'Add' && $question->defaultmark < $respcondition->setvar)
				$question->defaultmark = (string) $respcondition->setvar;
		}
		
		foreach ($item->resprocessing->respcondition as $respcondition) {
			if (isset($respcondition->setvar) && $respcondition->setvar == $question->defaultmark) {
				$question->fraction[] =  1; //$respcondition->setvar / $question->defaultmark;
			} else
				//Moodle only supports specific values for fractions so we cannot do this:
				//$question->fraction[] = $respcondition->setvar / $question->defaultmark;
				$question->fraction[] = 0; //Moodle only supports specific values for fractions so we have to skip this
		}

		foreach ($item->itemfeedback as $feedback) {
			$matches = null;
			preg_match_all("/.*?(\d+)$/", $feedback['ident'], $matches);
			if (count($matches)) {
				$question->feedback[] = $this->text_field($feedback->flow_mat->material->mattext);
			} else {
				$question->feedback[] = $this->text_field($feedback->flow_mat->material->mattext);
			}
		}
		
		return $question;
	}
	
	/**
	 * @return array (of objects) question objects.
	 */
	public function readquestions($questestinterop) {		
		$questions = array();

		foreach ($questestinterop as $item) {
			$questiontype = $this->get_question_type($item->itemmetadata->qtimetadata->qtimetadatafield[1]->fieldentry);
			error_log($questiontype);
			
			if (empty($questiontype)) {
				continue;
			}
			
			$question = null;
			
			switch ($questiontype) {
				case 'multichoice':
					$question = $this->import_multichoice($item);
					break;
				default:
					$qtstr = clean_param($questiontype, PARAM_TEXT);
					$this->error(get_string('unknownquestiontype', 'qformat_qml', $qtstr));
					break;
			}
			
			$questions[] = $question;
			
		}
		return $questions;
	}
	
	/**
	 * Clean the temporary directory if a zip file was imported
	 * @return bool success
	 */
	public function importpostprocess() {
		if ($this->tempdir != '') {
			fulldelete($this->tempdir);
		}
		return true;
	}
}