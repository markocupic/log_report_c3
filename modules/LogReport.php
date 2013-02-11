<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package log_report
 * @link    http://contao.org
 * @author Marko Cupic m.cupic@gmx.ch
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace Contao;

class LogReport extends \Frontend
{
	protected $strTemplate = 'log_report';
	protected $strPartialTemplate = 'log_report_partial';
	protected $countReports = 0;
	
	
	public function __construct()
    {
		parent::__construct();
 		//assigning the frontend template
        $this->strPartialTemplate = strlen(trim($GLOBALS['TL_CONFIG']['log_report_template'])) ? $GLOBALS['TL_CONFIG']['log_report_template'] : $this->strPartialTemplate;	
    }

	//module controller
	public function runLogReport()
	{
		if (!$GLOBALS['TL_CONFIG']['log_report_activate']) return;
		//nur zu Testzwecken
		define ('LOG_REPORT_TEST_MODE', false);
		
		$arrObservedTables = array_unique(array_merge(unserialize($GLOBALS['TL_CONFIG']['log_report_observed_tables']),explode(',',$GLOBALS['TL_CONFIG']['log_report_additional_observed_tables'])));
		if (!is_array($arrObservedTables))return;

		//removes empty value with a callback function
		$this->arrObservedTables = array_filter($arrObservedTables, array("Contao\LogReport", "isNotEmpty"));
		
		if (count($this->arrObservedTables)<1)return;
		
		$template = new \FrontendTemplate($this->strTemplate);
		$template->arrObservedTables = $this->arrObservedTables;
		$template->loadLanguageFile('default');
		$this->dateKey = date("Y_m_d");
		//if a report was allready sent today, abort here
		$objReport=$this->Database->prepare("SELECT * FROM tl_log_report WHERE date=?")->execute($this->dateKey);
		if($objReport->numRows==0 || LOG_REPORT_TEST_MODE==true)
		{
			//search for new Versions in the db
			$this->getNewVersions();
			
			//add the partialHtml to the main-template
			$template->report = $GLOBALS['LOG_REPORT'];
            unset($GLOBALS['LOG_REPORT']);
			$htmlMailContent = $template->parse();

			//send email
			if ($this->countReports<1 && true==$GLOBALS['TL_CONFIG']['log_report_send_email_when_db_changed'])
			{ 
				// When there are noch changes 
				// and $GLOBALS['TL_CONFIG']['log_report_send_email_when_db_changed'] is activated
				// no email will be sent to the recipients.
			} else {
				$this->sendEmail($htmlMailContent);
			}

			//db insert
			$set=array(
				"date" => $this->dateKey,
				"recipients" => $GLOBALS['TL_CONFIG']['log_report_recipients'],
				"report" => $htmlMailContent
			);

			//store report in tl_log_report and in tl_log
			$objInsertStmt=$this->Database->prepare("INSERT INTO tl_log_report %s")->set($set)->execute();
			if ($objInsertStmt->affectedRows)
			{
				$insertId=$objInsertStmt->insertId;
				$this->log('LogReport has been executed and an email was sent to the admin.', __CLASS__ . ' ' . __FUNCTION__ . '()', TL_GENERAL);
				$this->log(sprintf('A new version of tl_log_report ID %s has been created', $insertId), __CLASS__ . ' ' . __FUNCTION__ . '()', TL_GENERAL);
			}
		}
	}
	
	

	private function getNewVersions()
	{
		$objLog=$this->Database->prepare("SELECT * FROM tl_log WHERE log_report_date=? ORDER BY tstamp")->execute("");
		if ($objLog->numRows<1)return $return;
		
		//continue, if there are some unreported changes 
		while ($objLog->next())
		{
			//create a html-table for each new row
			$this->createPartialHtml($objLog->text, $objLog->username, $objLog->tstamp);
			//update column log_report_date in tl_log with the current date
			$set=array(
				"log_report_date" => $this->dateKey
			);
			$objUpdate=$this->Database->prepare("UPDATE tl_log %s WHERE id=?")->set($set)->execute($objLog->id);
		}
	}





	private function createPartialHtml($str, $username, $tstamp)
	{
		$this->import('Environment');
				
		foreach ($this->arrObservedTables as $table)
		{
			$table=trim($table);
			if ($table!="")
			{
				if (false!==strpos($str, $table))
				{
				
					$arr_patterns = array(
                        //A new version of record "tl_user.id=1" has been created
						'edit' => '/(?P<table>\w+).id=(?P<id>\d+)/',
						'delete' => '/DELETE FROM (?P<table>\w+) WHERE id=(?P<id>\d+)/'
					);
					
					foreach ($arr_patterns as $logType => $pattern)
					{
						//ab php 5.2.2 named subpatterns
						preg_match($pattern, $str, $treffer);
						if (strlen($treffer["table"]) && strlen($treffer["id"]))
						{  
							$this->countReports++;
							$template = new \FrontendTemplate($this->strPartialTemplate);
							$template->table = $table;
							$template->logMessage = $str;
							$template->type = strtoupper($logType);
							$objDb=$this->Database->prepare(sprintf("SELECT * FROM %s WHERE id=?", $table))->execute($treffer["id"]);
							$arrFields=$this->Database->listFields($table);
							$fields=array();
							if ($objDb->numRows)
							{
								//create the backend-link which links directly to the contao-backend
								if ($table=="tl_content")
								{
									$backendUrl = $this->Environment->base . sprintf("contao/main.php?do=%s&table=%s&act=edit&id=%s","article", $table, $treffer["id"]);
								} 
								elseif ($table=="tl_news")
								{
									$backendUrl = $this->Environment->base . sprintf("contao/main.php?do=%s&table=%s&act=edit&id=%s",str_replace("tl_", "", $table), $table, $treffer["id"]);
								} else {
									$backendUrl = $this->Environment->base . sprintf("contao/main.php?do=%s&act=edit&id=".$treffer["id"], str_replace("tl_","",$table));
								}
								
								$fields["backendUrl"] = '<a href="' . $backendUrl . '" title="go to contao backend">' . $backendUrl . '</a>';
								foreach ($arrFields as $arrField)
								{
									//for security reasons the password will not be displayed
									if($arrField["name"] == "password" || $arrField["name"] == "PRIMARY") continue;
									$fields[$arrField["name"]] = $objDb->{$arrField["name"]};
								}
							}	
							$template->username = $username;
							$template->date = $this->parseDate('l, d. F Y, H:i', $tstamp);  
							$template->fields = $fields;
							//only the latest versions will be sent by the email
							$GLOBALS['LOG_REPORT'][$table . "_html"][$table . "_" . $treffer["id"]] = $template->parse();
						}
					}
				}
			}	
		}
	}

    public function purgeLogReportTable()
    {
        // Truncate the table
        // This method is called from the maintenance module
        $this->Database->execute("TRUNCATE TABLE tl_log_report");
    }

    public function isNotEmpty($var)
    {
        if (strlen($var)) 
    	{
    		return $var;
    	}
    }
	

	private function sendEmail($htmlMessage="")
	{
		$arr_recipients = explode (',',$GLOBALS['TL_CONFIG']['log_report_recipients']);
		if (!is_array($arr_recipients))return;
		if (!count($arr_recipients))return;
		//create the attachment-file
		$filepath = "system/html/log_report_" . time(). ".html";
		$file = new \File($filepath);
		$file->write($htmlMessage);
		$file->close();
		foreach ($arr_recipients as $recipient)
		{
			$email = new \Email();
			$email->charset='UTF-8';
			$email->priority = 'high';
			//placeholder-values values array
			$arr_search = array('http://', 'www.', 'https://');
			//replace values array
			$arr_replace = array('', '', '');
			$from = 'log.report@' . str_replace($arr_search, $arr_replace, $_SERVER['HTTP_HOST']);
			$email->from = $from;
			$email->replyTo($from);
			$email->subject = "change log contao";			
			$email->text = 'A new log report was sent to you! Please open te email with a html-compatible email-programm!';
			$email->html = $htmlMessage;
			$email->attachFile($filepath);	
			if (strlen(trim($recipient))) $email->sendTo(trim($recipient));
		}
		//delete the tmp-file
		$file->delete();
	}
}




