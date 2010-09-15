<?php 
/**
 *	This helper is used in conjunction with the FileHandler behavior in models/behaviors/file_handler.php.
 *	FileHelper provides markup to preview and remove uploaded files.
 *
 *	USAGE: In your model
 *	----------------------------------------------
 *		var $actsAs = array(
 *			'FileHandler.FileHandler' => array(
 *				'file_field_name', 
 *				'second_file_field_name'
 *			)
 *		);      
 *
 *	USAGE: In Your View   
 *	------------------------------------------------
 *	echo $this->Form->input('file_field_name', array(
 *   		'type'=>'file',
 *   		'after'=>$this->File->previewImage('file_field_name')
 *   	)
 *   );
 *
 * 	Also make sure you have:  $form->create('model_name', rray('enctype' => 'multipart/form-data'));   
 *
 *	USAGE: In Your Controller 
 *  --------------------------------------------------
 * 	var $helpers = array('FileHandler.File');
 *
 *	
 * 	@author		Jason Gillespie
 *	@version	1.2
 *	@date		04/18/2010
 */

class FileHelper extends AppHelper
{
	var $helpers = array('Html','Form');
	
	function previewImage ($element, $options = array()) {
		$model = $this->params['models'][0];
	   
		if (isset($this->data[$model][$element]) AND $this->data[$model][$element] != '') {

				$src = '/'.$this->data[$model][$element];
				
				if (isset($options['width'])) {
					$width = $options['width'];
				}
				else {
					$width = 'auto';
				}
				if (isset($options['height'])) {
					$height = $options['height'];
				}
				else {
					$height = 'auto';
				}
				$markup = '<br /><br />';
				$markup .= '<a href="'.$src.'" /><img style="width: '.$width.';height: '.$height.';"src="'.$src.'" /></a>';
				$markup .= $this->Form->input($model.'.'.$element.'.remove', array('type'=>'checkbox', 'label'=>'Delete '.$this->data[$model][$element].'?', 'value'=>''));

				return $this->output($markup);
		}
	}
}

?>