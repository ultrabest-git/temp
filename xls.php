<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

writeToLog($_POST,"POST");
if (CModule::IncludeModule("nkhost.phpexcel"))
{
	global $PHPEXCELPATH;      
	// Подключаем класс для работы с excel
	require_once($PHPEXCELPATH.'/PHPExcel.php');
	// Подключаем класс для вывода данных в формате excel
	require_once($PHPEXCELPATH.'/PHPExcel/Writer/Excel5.php');		 
	// Создаем объект класса PHPExcel
	$xls = new PHPExcel();	
	//if($list>0)$xls->createSheet($list);

	foreach($_POST['params'] as $list=>$course)
	{	
		if($list>0)$xls->createSheet($list);
		// Устанавливаем индекс активного листа
		$xls->setActiveSheetIndex($list);
		// Получаем активный лист
		$sheet = $xls->getActiveSheet();
		// Подписываем лист
		if(strlen(course_name($course))>31) $sheet->setTitle("Отчет-".($list+1));
		else $sheet->setTitle(course_name($course));
		 
		// Вставляем текст в ячейку A1
			$bg = array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => '003E7E')
				),
				'font' => array(
						'color'     => array('rgb' => 'FFFFFF')              
					)
			);
			$bg_red = array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => 'ff0000')
				),
				'font' => array(
						'color'     => array('rgb' => 'FFFFFF')              
					)				
			);	
			$bg_green = array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => '008000')
				),
				'font' => array(
						'color'     => array('rgb' => 'FFFFFF')              
					)				
			);	
			$border = array(
				'borders'=>array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => '000000')
					)
				)
			);	
			$border1 = array(
				'borders'=>array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => 'ffffff')
					)
				)
			);				
			$sheet->setCellValue("A1", course_name($course));
			$sheet->getStyle('A1')->applyFromArray($bg);
			 
			// Объединяем ячейки
			$sheet->mergeCells('A1:G1');
			// Выравнивание текста
			$sheet->getStyle('A1')->getAlignment()->setHorizontal(
			PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


			//Заголовки таблицы
			$sheet->setCellValue("A2", "Название");
			$sheet->setCellValue("F2", "Кол-во, в %");
			$sheet->setCellValue("G2", "Сдан(Да/Нет)");
			$sheet->getStyle('A2')->applyFromArray($bg);
			$sheet->getStyle('F2')->applyFromArray($bg);
			$sheet->getStyle('G2')->applyFromArray($bg);	
			
			// Объединяем ячейки
			$sheet->mergeCells('A2:E2');
			// Выравнивание текста
			$sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle('F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	 
			$sheet->getStyle('G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	 
			
			//Общая сводка 
			$sheet->setCellValue("A3", "Общий показатель");
			$sheet->setCellValue("F3", $_POST['data'][$course]['RESULT']);
			$sheet->setCellValue("G3", $_POST['data'][$course]['COMPLETED']);

			$sheet->getStyle('A3')->applyFromArray($bg);
			$sheet->getStyle('F3')->applyFromArray($bg);
			$sheet->getStyle('G3')->applyFromArray($bg);			
			// Объединяем ячейки
			$sheet->mergeCells('A3:E3');
			// Выравнивание текста
			$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$sheet->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);		
			$sheet->getStyle('G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle("A1:G3")->applyFromArray($border1);			
			//сама таблица 
			$i=4;
			$sheet->getColumnDimensionByColumn('A')->setAutoSize(true);
			$sheet->getColumnDimensionByColumn('B')->setAutoSize(true);	
			$sheet->getColumnDimensionByColumn('C')->setAutoSize(true);
			$sheet->getColumnDimensionByColumn('D')->setWidth(600);
			$sheet->getColumnDimensionByColumn('E')->setWidth(600);								
			$sheet->getColumnDimensionByColumn('F')->setWidth(100);
			$sheet->getColumnDimensionByColumn('G')->setWidth(100);	
			$mass1=array_sort($_POST['data'][$course]['LEVEL1'],'GRPD');			
			foreach($mass1 as $level1)
			{		
				if($level1['COMPLETED']=="Да")
				{
									
					$sheet->getStyle('G'.$i)->applyFromArray($bg_green);			
				}	
				else		
				{
					$sheet->getStyle('G'.$i)->applyFromArray($bg_red);										
				}
				// Объединяем ячейки
				$sheet->mergeCells('A'.$i.':E'.$i);
				$sheet->setCellValue('A' . $i, $level1['GRPD']." ".$level1['FIO']);
				$sheet->setCellValue('F' . $i, $level1['RESULT']);
				$sheet->setCellValue('G' . $i, $level1['COMPLETED']);
				$sheet->getStyle('F'.$i.':G'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);								
				$sheet->getRowDimension($i)->setOutlineLevel(1);
				$sheet->getRowDimension($i)->setVisible(true);
				$sheet->getRowDimension($i)->setCollapsed(false);
				$i++;	
				foreach($level1['CFO'] as $level2)
				{
					// Объединяем ячейки
					$sheet->mergeCells('B'.$i.':E'.$i);					
					if($level2['COMPLETED']=="Да")
						{
									
							$sheet->getStyle('G'.$i)->applyFromArray($bg_green);			
						}	
						else		
						{
							$sheet->getStyle('G'.$i)->applyFromArray($bg_red);										
						}				
					$sheet->setCellValue('B' . $i, $level2['NAME']);
					$sheet->setCellValue('F' . $i, $level2['RESULT']);
					$sheet->setCellValue('G' . $i, $level2['COMPLETED']);	
					$sheet->getStyle('F'.$i.':G'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);											
					$sheet->getRowDimension($i)->setOutlineLevel(2);
					$sheet->getRowDimension($i)->setVisible(false);
					$sheet->getRowDimension($i)->setCollapsed(true);
					$i++;
						foreach($level2['PSK'] as $level3)
					{
						// Объединяем ячейки
						$sheet->mergeCells('C'.$i.':E'.$i);						
						if($level3['COMPLETED']=="Да")
						{
									
							$sheet->getStyle('G'.$i)->applyFromArray($bg_green);			
						}	
						else		
						{
							$sheet->getStyle('G'.$i)->applyFromArray($bg_red);										
						}						
						$sheet->setCellValue('C' . $i, $level3['NAME']);
						$sheet->setCellValue('F' . $i, $level3['RESULT']);
						$sheet->setCellValue('G' . $i, $level3['COMPLETED']);
						$sheet->getStyle('F'.$i.':G'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);													
						$sheet->getRowDimension($i)->setOutlineLevel(3);
						$sheet->getRowDimension($i)->setVisible(false);
						$sheet->getRowDimension($i)->setCollapsed(true);
						$i++;
						foreach($level3['USERS'] as $level4)
						{
							if($level4['COMPLETED']=="Да")
							{
									
								$sheet->getStyle('G'.$i)->applyFromArray($bg_green);			
							}	
							else		
							{
								$sheet->getStyle('G'.$i)->applyFromArray($bg_red);										
							}							
							// Объединяем ячейки
							$sheet->mergeCells('D'.$i.':E'.$i);								
							$sheet->setCellValue('D' . $i, $level4['NAME']);
							$sheet->setCellValue('F' . $i, $level4['RESULT']);
							$sheet->setCellValue('G' . $i, $level4['COMPLETED']);			
							$sheet->getStyle('F'.$i.':G'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);																						
							$sheet->getRowDimension($i)->setOutlineLevel(4);
							$sheet->getRowDimension($i)->setVisible(false);							
							$sheet->getRowDimension($i)->setCollapsed(true);
							$i++;
							foreach($level4['TEST'] as $level5)
							{	
								if($level5['COMPLETED']=="Да")
								{
									
									$sheet->getStyle('G'.$i)->applyFromArray($bg_green);			
								}	
								else		
								{
									$sheet->getStyle('G'.$i)->applyFromArray($bg_red);										
								}									
								$sheet->setCellValue('E' . $i, $level5['NAME']);
								$sheet->setCellValue('F' . $i, $level5['RESULT']);	
								$sheet->setCellValue('G' . $i, $level5['COMPLETED']);
								$sheet->getStyle('F'.$i.':G'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);							
								$sheet->getRowDimension($i)->setOutlineLevel(5);
								$sheet->getRowDimension($i)->setVisible(false);								
								$sheet->getRowDimension($i)->setCollapsed(true);
								$i++;
							}				
						}			
					}
					
				}		
			}
			$sheet->getStyle("A4:G".($i-1))->applyFromArray($border);				
			
	}	
		 // Выводим HTTP-заголовки
		 header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
		 header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
		 header ( "Cache-Control: no-cache, must-revalidate" );
		 header ( "Pragma: no-cache" );
		 header ( "Content-type: application/vnd.ms-excel" );
		 header ( "Content-Disposition: attachment; filename=otchet.xls" );
		 $path=$_SERVER["DOCUMENT_ROOT"]. "/upload/xls/otchet.xls";
		 echo json_encode("/upload/xls/otchet.xls");
		// Выводим содержимое файла
		 $objWriter = new PHPExcel_Writer_Excel5($xls);
		//echo $objWriter->save('php://output');
		 $objWriter->save($path);	
		header("Content-Length: ".filesize($path));
    // output file content
	//	readfile($path);
    // delete temporary file
		//unlink($path);		 

}
	
	
	
	
function writeToLog($data, $title = '')
{
        if (!extractlog)
                return false;

        $log = "\n------------------------\n";
        $log .= date("Y.m.d G:i:s")."\n";
        $log .= (strlen($title) > 0 ? $title : 'DEBUG')."\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";

        file_put_contents(__DIR__."/".extractlog, $log, FILE_APPEND);

        return true;
}


function name_cfo($id)
{
	if(CModule::IncludeModule("learning"))
	{ 
								$rsSection = \Bitrix\Iblock\SectionTable::getList(array(
									'filter' => array(
										'IBLOCK_ID' => 75,
										'DEPTH_LEVEL' => 1,
										'ID' => $id
									), 
									'select' =>  array('ID','NAME'),
								));
								while ($arSection=$rsSection->fetch()) 
								{
									return $arSection['NAME'];
								}
	}	
}
