<?php

require_once('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule;

use \FluidXml\FluidXml;
use \FluidXml\FluidNamespace;
$name = '';

//read excel
$inputFileName = 'excel_dir/excel_file.xlsx';
$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
$spreadsheet = $reader->load($inputFileName);

//filtering KC only
$spreadsheet->getActiveSheet()->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());
$autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();

$autoFilter->getColumn('G')
        ->setFilterType(Column::AUTOFILTER_FILTERTYPE_CUSTOMFILTER)
        ->createRule()
        ->setRule(
            Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
            'KC'
        )
        ->setRuleType(Rule::AUTOFILTER_RULETYPE_CUSTOMFILTER);


$autoFilter->showHideRows();
$worksheet = $spreadsheet->getActiveSheet();

foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
    if ($spreadsheet->getActiveSheet()->getRowDimension($row->getRowIndex())->getVisible()) {
	    
        $col_a = $spreadsheet->getActiveSheet()->getCell('A' . $row->getRowIndex())->getValue();
        $col_b = $spreadsheet->getActiveSheet()->getCell('B' . $row->getRowIndex())->getValue();
        $col_c = $spreadsheet->getActiveSheet()->getCell('C' . $row->getRowIndex())->getValue();
        $col_d = $spreadsheet->getActiveSheet()->getCell('D' . $row->getRowIndex())->getValue();
        $col_e = $spreadsheet->getActiveSheet()->getCell('E' . $row->getRowIndex())->getValue();
        $col_f = $spreadsheet->getActiveSheet()->getCell('F' . $row->getRowIndex())->getValue();
        $col_g = $spreadsheet->getActiveSheet()->getCell('G' . $row->getRowIndex())->getValue();
        $col_h = $spreadsheet->getActiveSheet()->getCell('H' . $row->getRowIndex())->getCalculatedValue();

        $result[] = array($col_a, $col_b, $col_c, $col_d, $col_e, $col_f, $col_g, $col_h);
    }

    unset($result[0]);
}

//generate XML code
	$options = [ 
		'root'       => 'workflow-definition',    // The root node of the document.
	  	'version'    => '1.0'];

	$xml = new FluidXml(null, $options);

	$xml->query('//workflow-definition')
	          ->setAttribute('xmlns', 'urn:liferay.com:liferay-workflow_6.2.0')
	          ->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance')
	          ->setAttribute('xsi:schemaLocation','urn:liferay.com:liferay-workflow_6.2.0 http://www.liferay.com/dtd/liferay-workflow-definition_6_2_0.xsd');

	//add nodes
	$notif2 = array('notification-type' => 'user-notification');
	$transition2 = array('transition' => array('name' => 'reject', 'target' => 'update', 'default' => 'false'));

	$component = [
					'name' => $name,
					'description' => 'KC assign to SME and then approve article or document.',
					'version' => 2,
					'state' => 
						[
							'name' => 'created',
							'metadata' => '<![CDATA[{"xy":[36,51]}]]>',
							'initial' => 'true',
							'transitions' => 
							[
								'transition' => 
								[
									'name' => 'review',
									'target' => 'review'
								]
							]
						],
					'task' => 
						[
							'name' => 'update',
							'metadata' => '<![CDATA[{"transitions":{"resubmit":{"bendpoints":[[303,140]]}},"xy":[328,199]}]]>',
							'actions' => 
								[
									'action' => 
										[
											'name' => 'reject',
											'script' => '<![CDATA[import com.liferay.portal.kernel.workflow.WorkflowStatusManagerUtil;		import com.liferay.portal.kernel.workflow.WorkflowConstants;
											WorkflowStatusManagerUtil.updateStatus(WorkflowConstants.getLabelStatus("denied"), workflowContext);
											WorkflowStatusManagerUtil.updateStatus(WorkflowConstants.getLabelStatus("pending"), workflowContext);
											]]>',
											'script-language' => 'groovy',
											'execution-type' => 'onAssignment'
										],
									'notification' => 
										[
											'name' => 'Creator Modification Notification',
											'template' => 'Your submission was rejected by ${userName}, please modify and resubmit.',
											'template-language' => 'freemarker',
											'notification-type' => 'email',
											$notif2,
											'execution-type' => 'onAssignment'
										]
								],
							'assignments' => 
								[
									'user'
								],
							'transitions' => 
								[
									'transition' => 
										[
											'name' => 'resubmit',
											'target' => 'review'
										]
								]


						],

				]; 


	$xml->add($component);

	$notification = array('notification' => 
						[
							'name' => 'Review Completion Notification',
							'template' => '${userName} mengirimkan ${entryType} untuk diulas',
							'template-language' => 'freemarker',
							'notification-type' => 'email',
							$notif2,
							'recipients' => 
								[
									'user'
								],
							'execution-type' => 'onExit'
						]
					);

	$component2 = 	[
						'task' => 
							[
								'name' => 'review',
								'metadata' => '<![CDATA[{"xy":[168,36]}]]>',
								'actions' => 
									[
										'notification' => 
											[
												'name' => 'Review Notification',
												'template' => '${userName} mengirimkan ${entryType} untuk diulas',
												'template-language' => 'freemarker',
												'notification-type' => 'email',
												$notif2,
												'execution-type' => 'onAssignment'
											],
										$notification
									],
								'assignments' => 
									[
										'roles'
									],
								'transitions' => 
									[
										'transition' => 
											[
												'name' => 'approve',
												'target' => 'kro-publish',
											],
										$transition2
									]
							]
					];

	$xml->add($component2);

	$component3 = 	[
						'task' => 
							[
								'name' => 'kro-publish',
								'metadata' => '<![CDATA[{"xy":[340,270]}]]>',
								'actions' => 
									[
										'notification' => 
											[
												'name' => 'Publish Notification',
												'template' => '${userName} mengirimkan ${entryType} untuk diterbitkan',
												'template-language' => 'freemarker',
												$notif2,
												'notification-type' => 'email',
												$notif2,
												'execution-type' => 'onAssignment'
											],
									],
								'assignments' => 
									[
										'roles' => 
											[
												'role' => 
													[
														'role-type' => 'regular',
														'name' => 'KRO'
													]
											]
									],
								'transitions' => 
									[
										'transition' => 
											[
												'name' => 'published',
												'target' => 'approved'
											]
									]
							],
						'state' => 
							[
								'name' => 'approved',
								'metadata' => '<![CDATA[{"xy":[380,51]}]]>',
								'actions' => 
									[
										'action' => 
											[
												'name' => 'approve',
												'script' => '<![CDATA[
							import com.liferay.portal.kernel.workflow.WorkflowStatusManagerUtil;
							import com.liferay.portal.kernel.workflow.WorkflowConstants;
							WorkflowStatusManagerUtil.updateStatus(WorkflowConstants.getLabelStatus("approved"), workflowContext);
						]]>',
												'script-language' => 'groovy',
												'execution-type' => 'onEntry'
											]
									]
							]
					];

	$xml->add($component3);

	

	$parent = $xml->query('//task/assignments/roles');
	$count_xml = $parent->size() - 1;


	//fill roles and name
	foreach($result as $r){

		$el_notification = new DOMElement('role');
		$role_parent = $parent[0]->appendChild($el_notification);
		$role = new DOMElement('role-type','regular');
		$role_name = new DOMElement('name',htmlspecialchars($r[7]));

		$role_parent->appendChild($role);
		$role_parent->appendChild($role_name);

	}

//generate XML file
	foreach($result as $r){
		$name = $xml->query('//name');
		$name[0]->nodeValue = 'Workflow KM '.htmlspecialchars($r[1]);
		
		$file_name = './xml_dir/Workflow KM '.$r[1].'.xml';
		$myfile = fopen($file_name, "w") or die("Unable to open file!");
		fwrite($myfile, $xml);
		fclose($myfile);
	}
	
