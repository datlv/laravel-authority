<?php
use Datlv\User\Seeders\UserTestData;

/**
 * Class RoleTest
 */
class RoleTest extends TestCase
{
    use UserTestData;

    public function testAttachedRoles()
    {
        $this->assertTrue($this->users['admin']->roles() === ['sys.sadmin']);
        $this->assertTrue($this->users['quantri']->roles() === ['sys.admin']);
        $this->assertTrue($this->users['user1']->roles() === []);
    }
}
