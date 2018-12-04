<?php

declare(strict_types = 1);

namespace Framework;

use Framework\iArraify;

/** Main class for holding user info.
 *
 */
final class Userdata implements iArraify
{
	private $u_id = -1;
	private $first_name;
	private $middle_name;
	private $last_name;
	private $display_name;
	private $email_address;
	private $permissions = 0;
	
	function __construct(array $input)
	{
		if(is_array($input))
		{
			if(array_key_exists('u_id', $input)) $this->u_id = (int)$input['u_id'];
			if(array_key_exists('first_name', $input)) $this->first_name = $input['first_name'];
			if(array_key_exists('middle_name', $input)) $this->middle_name = $input['middle_name'];
			if(array_key_exists('last_name', $input)) $this->last_name = $input['last_name'];
			if(array_key_exists('display_name', $input)) $this->display_name = $input['display_name'];
			if(array_key_exists('email_address', $input)) $this->email_address = $input['email_address'];
			if(array_key_exists('permissions', $input)) $this->permissions = (int)$input['permissions'];
		}
	}
	
	public function toArray() : array
	{
		return array(
			'u_id' => $this->u_id,
			'first_name' => $this->first_name,
			'middle_name' => $this->middle_name,
			'last_name' => $this->last_name,
			'display_name' => $this->display_name,
			'email_address' => $this->email_address,
			'permissions' => $this->permissions
			);
	}
	
	public function isValid() : bool
	{
		return ($this->u_id >= 0);
	}
	
	public function getDisplayName() : string
	{
		return $this->display_name;
	}
	
	public function getRealName() : string
	{
		return preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
	}
	
	public function getEmailAddress() : string
	{
		return $this->email_address;
	}
	
	public function getPermissions() : int
	{
		return $this->permissions;
	}
}
?>
