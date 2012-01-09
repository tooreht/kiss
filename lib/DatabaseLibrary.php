<?php

/**
 * The Database Library handles database interaction for the application
 */
abstract class DatabaseLibrary
{
	abstract protected function connect();
	abstract protected function disconnect();
	abstract protected function prepeare($query);
	abstract protected function query();
	abstract protected function fetch($type = 'object');
}
