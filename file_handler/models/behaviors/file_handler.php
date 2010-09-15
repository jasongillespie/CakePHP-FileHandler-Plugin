<?php
/**
 * FileHandler saves and deletes files specified in fields defined in the $actsAs property;
 * FileHandler also inserts and deletes the filename (with it's path) into a database table.
 *	
 * @package FileHandler Behavior
 * @author Jason Gillespie
 * @date May 3, 2010
 * @version 1.2 - There are currently no Configurable Options or Validation rules in this version
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
 * 	Also make sure you have:  $form->create('model_name', array('enctype' => 'multipart/form-data'));   
 *
 *	USAGE: In Your Controller 
 *  --------------------------------------------------
 * 	var $helpers = array('FileHandler.File');
 *	
 **/

App::import('Core', array('File', 'Folder'));

class FileHandlerBehavior extends ModelBehavior {
	
	private $model;

	function setup (&$model, $settings) {
		
		if (!isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = array(
				// Put optional settings here
				// 'option1_key' => 'option1_default_value',
			);
		}
	
		$this->settings[$model->alias] = array_merge($this->settings[$model->alias], (array)$settings);	
		$this->model =& $model;
	}
	
	/**
	 * Update the database and files passed in the data array before save.
	 * @author Jason Gillespie
	 **/
	function beforeSave () { 

		if($this->_updateFiles()) {  			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Set flag to remove files, then update files before the record is deleted
	 * @author Jason Gillespie
	 **/
	function beforeDelete () {
		
		foreach ($this->settings[$this->model->alias] as $field) {
			$this->model->data[$this->model->alias][$field]['remove'] = 1;
		}
		
		if($this->_updateFiles()) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Update the database and files passed in the data array before save.
	 * @author Jason Gillespie
	 **/
	function _updateFiles () {

		foreach ($this->settings[$this->model->alias] as $field) {
			
			if (!isset($this->model->data[$this->model->alias][$field])) {
				$this->model->validationErrors['The field '.$field.' in your $actsAs array does not exist in your view.'];
				return false;
			}
			
			// Remove File 
			if (isset($this->model->data[$this->model->alias][$field]['remove']) AND $this->model->data[$this->model->alias][$field]['remove']) {
				if (!$this->_removeFile($field)) {
					return false;
				}
			}
			
			// Save File 
			else if ($this->model->data[$this->model->alias][$field]['size'] != 0) {   
				
				if (!$this->_saveFile($field)) {
					return false;
				}
			}
			
			// Ignore 
			else {
				unset ($this->model->data[$this->model->alias][$field]);
			}
		}
		
		return true;
	}
	
	/**
	 * Accepts a field, creates a directory for the field, and saves the file into the directory.
	 * We also mutate the data property in the passed model by replacing the file array with 
	 * the file path.
	 *
	 * @param $field - The fieldname that contains the file to save.
	 * @return bool - TRUE if the file can be saved into the appropriate directory.
	 * @author Jason Gillespie
	 **/
	function _saveFile ($field) {
 		
		$model_human_name = Inflector::underscore($this->model->alias);
		$file_dir = "uploads/$model_human_name/$field/";
		$file_data = $this->model->data[$this->model->alias][$field];         

		// Get file data from tmp
		$file = new File($file_data['tmp_name']);
		$tmp_file_data = $file->read();
		$file->close();
	
		// Create new directory
		if (!is_dir($file_dir)) {
			$folder = new Folder();
			if (!$folder->create($file_dir)) {
				$this->model->validationErrors['The folder '.$file_dir.' could not be created.'];
				return false;
			}				
		}					
		
		// If file exists, make unique
		$index = 0;
	 	do {
			if($index == 0) {
				$file_path = $file_dir.$file_data['name'];
			} 
			else {
				$file_path = $file_dir.$index.'_'.$file_data['name'];
			}
			$index++;
		} while (file_exists($file_path));
		
		// Save new file
		$file = new File($file_path, true);
		$file->write($tmp_file_data);
		$file->close();
		
		$this->model->data[$this->model->alias][$field] = $file_path;

		return true;
		
	}
	
	/**
	 * Accepts a field and deletes the file from both the filesystem and the database
	 *
	 * @param $field - The fieldname that contains the file to delete.
	 * @return bool - TRUE if the file can be removed.
	 * @author Jason Gillespie
	 **/
	function _removeFile ($field) {
		
		$model_name = $this->model->alias;
		$id = $this->model->id;
		$record = $this->model->find('first', array('conditions'=>array($model_name.'.id'=>$id)));
		$file_path = $record[$model_name][$field];

		if (file_exists($file_path)) {
			$file = new File($file_path);
			$file->delete();			
		}
		
		$this->model->data[$model_name][$field] = null;
		
		return true;
	}
}
?>