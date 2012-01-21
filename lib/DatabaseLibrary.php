<?php

/**
 * The Database Library handles database interaction for the application
 */
interface DatabaseLibrary
{
	public function connect();
	public function disconnect();
	public function prepare($query);
	public function query($query);
	public function fetch($type = 'object');
}
