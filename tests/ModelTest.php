<?php

/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class ModelTest extends \PHPUnit_Extensions_Database_TestCase
{
    function setUp()
    {
        \ORM\Model::$dbo = new \ORM\DBO\MySQL(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

        User::$accessible = ['id', 'email', 'first_name', 'last_name', 'role_id'];

        parent::setUp();
    }

    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            implode(DIRECTORY_SEPARATOR, [__DIR__, "fixtures", "php_orm_test.yml"])
        );
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        return $this->createDefaultDBConnection(new \PDO('mysql:dbname=' . DB_NAME . ';host=' . DB_HOST . ';port=' . DB_PORT, DB_USER, DB_PASS));
    }

    function testTableName()
    {
        $this->assertEquals(User::getTable(), 'users');
        $this->assertEquals(Account::getTable(), 'accounts');
    }

    public function testModel()
    {
        $this->assertEquals(User::find(1)->email, 'email1@example.com');
        $this->assertEquals(User::first()->email, 'email1@example.com');
        $this->assertEquals(User::last()->email, 'email3@example.com');
        $this->assertEquals(User::count(), 3);
        $this->assertEquals(User::findOne(['email'], ['email1@example.com'])->email, 'email1@example.com');
        $this->assertEquals(User::findOne(['first_name', 'last_name'], ['John', 'Doe'])->email, 'email1@example.com');
        $this->assertCount(3, User::all());
    }

    public function testHasManyRelation()
    {
        $this->assertCount(1, Role::findOne(['name'], ['Admin'])->users());
    }

    public function testBelongsToRelation()
    {
        $this->assertEquals(User::find(1)->role()->name, 'Admin');
        $this->assertEquals(Account::first()->user()->first_name, User::first()->first_name);
    }

    public function testHasOneRelation()
    {
        $this->assertEquals(Account::first(), User::first()->account());
    }

    public function testHABTMRelation()
    {
        $this->assertCount(2, User::first()->messages()->get());
        User::first()->messages()->insert([['title'=>'sdf'], ['title'=>'123123']]);
        $this->assertCount(4, User::first()->messages()->get());
        $this->assertCount(1, User::first()->messages()->get(['title'],['sdf']));
        $ids = User::last()->messages()->insert([['title'=>'new title']]);
        $this->assertSame(4, User::first()->messages()->count());
        $this->assertSame(1, User::last()->messages()->count());
        $this->assertTrue(User::last()->messages()->has($ids[0]));
        $this->assertTrue(User::last()->messages()->delete($ids[0]));
        $this->assertFalse(User::last()->messages()->has($ids[0]));
        $this->assertFalse(User::last()->messages()->has(Message::find($ids[0])));

        // @todo test sync method
    }

    function testGetAttributes()
    {
        $user = User::first();
        $this->assertEquals(
            [
                'id' => 1,
                'email' => 'email1@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'role_id' => 2
            ],
            $user->attributes()
        );
    }

    function testGetSchema()
    {
        $this->assertEquals(
            [
                'id' => 'LONG',
                'email' => 'VAR_STRING',
                'first_name' => 'VAR_STRING',
                'last_name' => 'VAR_STRING',
                'role_id' => 'LONG'
            ],
            User::schema()
        );
    }

    function testAccessibleParameters()
    {
        $this->assertSame(
            ['id', 'email', 'first_name', 'last_name', 'role_id'],
            User::$accessible
        );

        $user = new User([
            'id' => 11,
            'role_id' => 1,
            'email' => 'email123@example.com',
            'first_name' => 'Test',
            'last_name' => 'Test',
        ]);
        $user->save();
        $this->assertSame(User::find(11)->first_name, 'Test');

        User::$accessible = ['id', 'email', 'last_name', 'role_id'];

        $user = new User([
            'id' => 12,
            'role_id' => 1,
            'email' => 'email123@example.com',
            'first_name' => 'Test',
            'last_name' => 'Test',
        ]);
        $user->save();
        $this->assertSame(User::find(12)->last_name, 'Test');
        $this->assertNull(User::find(12)->first_name);
    }

    function testPivotModel()
    {
        $pivot = User::first()->messages()->pivot();
        $this->assertInstanceOf('\ORM\Model', $pivot);
        $this->assertEquals($pivot::getTable(), 'messages_users');
        $this->assertCount(2, $pivot::all());

        $pivot->user_id = 2;
        $pivot->message_id = 1;
        $pivot->save();
        $this->assertCount(3, $pivot::all());
    }

    function testCustomPrimaryKey()
    {
        $option = User::find(1)->option();

        $option->name = 'testtest';
        $option->save();

        $this->assertEquals(Option::find(1)->name, 'testtest');
    }

    function testCreatePrimaryKey()
    {
        $params = [
            'role_id' => 1,
            'email' => 'email123@example.com',
            'first_name' => 'Test',
            'last_name' => 'Test',
        ];
        $user = new User();
        $user->create($params);

        $this->assertEquals($user->id, 4);

        $params['id'] = 10;
        $user = new User($params);
        $user->save();

        $this->assertEquals($user->id, 10);
        $this->assertEquals(User::find(10)->id, 10);
    }

    function testWhere()
    {
        $this->createNewUser();
        $this->assertCount(4, User::all());
        $users = User::where("first_name = ?", ['John']);
        $this->assertCount(2, $users);
        $users = User::where("first_name = ? OR last_name = ?", ['John', 'Bar3']);
        $this->assertCount(3, $users);
    }

    function testTransactions()
    {
        $this->assertCount(3, User::all());

        \ORM\Model::beginTransaction();
        $this->createNewUser();
        \ORM\Model::rollback();

        $this->assertCount(3, User::all());

        \ORM\Model::beginTransaction();
        $this->assertTrue(\ORM\Model::inTransaction());
        $this->createNewUser();
        \ORM\Model::commit();
        $this->assertCount(4, User::all());
    }

    function testValidationErrors()
    {
        $u = $this->createNewUser(['role_id' => null]);
        $this->assertSame("Role ID must be numeric", $u->getLastError());
        $this->assertTrue($u->isInvalid());
        $this->assertContains("Role ID must be numeric", $u->getErrors());
    }

    function testUpdate()
    {
        $u = $this->createNewUser(['id' => 27]);
        $this->assertSame('email1235@example.com', $u->email);
        $u->update(['email' => 'john@doe.com']);
        $this->assertSame('john@doe.com', $u->email);
        $this->assertSame('john@doe.com', User::find(27)->email);
    }

    function testCallbacks()
    {
        // @todo
    }

    function testProperties()
    {
        $properties = User::find(1)->properties();
        $this->assertSame(['id', 'email', 'first_name', 'last_name', 'role_id'], $properties);
    }

    function testFindOrInitializeBy()
    {
        $u = $this->createNewUser(['email' => 'email11111@example.com']);
        $u2 = User::findOrInitializeBy(['email'], ['email11111@example.com']);

        $this->assertSame($u->first_name, $u2->first_name);
        $this->assertSame($u->last_name, $u2->last_name);
        $this->assertSame($u->role_id, $u2->role_id);
        $this->assertSame($u->id, $u2->id);
        $this->assertTrue($u2->isPersisted());

        $u3 = User::findOrInitializeBy(['email'], ['email22222@example.com']);
        $this->assertFalse($u3->isPersisted());
    }

    function testFindOrCreateBy()
    {
        $u = $this->createNewUser(['email' => 'email11111@example.com']);
        $u2 = User::findOrCreateBy(['email'], ['email11111@example.com']);

        $this->assertSame($u->first_name, $u2->first_name);
        $this->assertSame($u->last_name, $u2->last_name);
        $this->assertSame($u->role_id, $u2->role_id);
        $this->assertSame($u->id, $u2->id);
        $this->assertTrue($u2->isPersisted());

        $u3 = User::findOrCreateBy(['email', 'role_id'], ['email22222@example.com', '1']);

        $this->assertCount(5, User::all());
        $this->assertTrue($u3->isPersisted());
    }

    function testDestroyBy()
    {
        $this->createNewUser(['email' => 'email11111@example.com']);
        $this->assertCount(4, User::all());
        User::destroyBy(['email'], ['email11111@example.com']);
        $this->assertCount(3, User::all());
        User::destroyBy(['first_name'], ['John']);
        $this->assertCount(2, User::all());
    }

    private function createNewUser($params = [])
    {
        $params = array_merge([
            'role_id' => '1',
            'email' => 'email1235@example.com',
            'first_name' => 'John',
            'last_name' => 'Test',
        ], $params);
        $u = new User($params);
        $u->save();

        return $u;
    }

}