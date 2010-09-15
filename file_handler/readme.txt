First, Make sure there is a folder that is writable named "uploads" in your webroot

In your model
----------------------------------------------
		var $actsAs = array(
			'FileHandler.FileHandler' => array(
				'file_field_name', 
				'second_file_field_name'   		
			)
		);
		      
In Your View   
------------------------------------------------
	echo $this->Form->input('file_field_name', array(
   		'type'=>'file',
   		'after'=>$this->File->previewImage('file_field_name')
   	)
   ); 

Also make sure you have:  $form->create('model_name', array('enctype' => 'multipart/form-data'));   

In Your Controller 
--------------------------------------------------
 	var $helpers = array('FileHandler.File');