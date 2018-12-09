<?php

use PHPUnit\Framework\TestCase;
use Framework\Permissions;

class PermissionsTest extends TestCase
{
	private $permissions;

	public function setUp()
	{
        // Remove the 'final' flag from the class definition
		uopz_flags(Framework\Session::class, null, 0);

		$session = $this->getMockBuilder(Framework\Session::class)
                     ->disableOriginalConstructor()
                     ->disableOriginalClone()
                     ->disableArgumentCloning()
                     ->disallowMockingUnknownTypes()
                     ->setMethods(['isValid','isAdmin','getPermissions','logon','logoff','__destruct'])
                     ->getMock();
        // Configure the stub.
        $session->method('isValid')
             ->willReturn(true);
        $session->method('isAdmin')
             ->willReturn(true);
		$session->method('getPermissions')
             ->willReturn((1 << 8) - 1);
		$session->method('logon')
             ->willReturn(true);
		$session->method('logoff')
             ->willReturn(true);
		$session->method('__destruct')
             ->willReturn(null);
		$this->permissions = new class($session) extends Permissions
		{
            private $session;
            public function __construct($session) { $this->session = $session; }
			protected function getSession() : Framework\Session { return $this->session; }
			public function test_checkPerms($req = 0) { return $this->checkPerms($req); }
			public function test_hasBitSet($a, $b) { return self::hasBitSet($a, $b); }
		};
	}

	/**
	 * @Covers \Framework\Permissions
	 */
	public function test_checkPerms1()
	{
		$this->assertTrue($this->permissions->test_checkPerms(Permissions::PERM_NO));
	}

	/**
	 * @Covers \Framework\Permissions
	 */
	public function test_checkPerms2()
	{
		$this->assertTrue($this->permissions->test_checkPerms(Permissions::PERM_REGISTERED_USER));
	}

	/**
	 * @Covers \Framework\Permissions
	 */
	public function test_checkPerms3()
	{
		$this->assertTrue($this->permissions->test_checkPerms(Permissions::PERM_ADMIN));
	}

	/**
	 * @Covers \Framework\Permissions
	 */
	public function test_checkPerms4()
	{
		$this->assertFalse($this->permissions->test_checkPerms(Permissions::PERM_ONLY_FRAMEWORK));
	}
}
?>
