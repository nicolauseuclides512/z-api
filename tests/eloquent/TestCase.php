<?php

class TestCase extends PHPUnit\Framework\TestCase
{
	/**
	 * Creates the application.
	 *
	 * @return \Laravel\Lumen\Application
	 */
	public function createApplication()
	{
		return require __DIR__.'/../../bootstrap/app.php';
	}

	public function isValidationPass($some_model, $field)
	{
		#if errors = empty 
		if (empty($some_model['errors']))
		{
			return true;
		}

		#get error_message from validation
		$error_messages			= $some_model['errors']->messages();
		if (empty($error_messages[$field]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
