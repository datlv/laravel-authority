<?php
use Datlv\User\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Class FactoryTest
 */
class FactoryTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * @var array
     */
    protected $users = [];

    public function setUp()
    {
        parent::setUp();
        $this->users['user'] = factory(User::class)->create();
        $this->users['admin'] = factory(User::class, 'sys.admin')->create();
        $this->users['super_admin'] = factory(User::class, 'sys.sadmin')->create();
    }

    public function testCreateUser()
    {
        $this->assertTrue($this->users['user'] instanceof User);
    }

    public function testSaveUser()
    {
        $this->seeInDatabase('users', ['username' => $this->users['user']->username]);
    }

    public function testAttachRole()
    {
        $this->seeInDatabase('role_user', [
            'user_id'    => $this->users['admin']->id,
            'role_group' => 'sys',
            'role_name'  => 'admin',
        ]);
    }
}
