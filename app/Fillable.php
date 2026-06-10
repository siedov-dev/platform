<?php

namespace App;

use Illuminate\Database\Eloquent\MassAssignmentException;
/**
 *
 */
class Fillable
{

	function __construct($config = [])
	{
		foreach ($config as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			} else {
				throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
			}
		}

		$this->init();
	}

	public function init()
	{

	}
}
