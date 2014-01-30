<?php
/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class ModelTest extends PHPUnit_Extensions_Database_TestCase
{
    function setUp()
    {
        \ORM\Model::$dbo = new \ORM\DBO\MySQL(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

        User::$accessible = ['id', 'email', 'first_name', 'last_name', 'role_id'];

        parent::setUp();
    }

    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            implode(DIRECTORY_SEPARATOR, [__DIR__, "fixtures", "php_orm_test.yml"])
        );
    }

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
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

    function testDynamicallyFinders()
    {
        $this->assertEquals(User::findByEmail('email1@example.com')->email, 'email1@example.com');
        $this->assertEquals(User::findByEmailAndFirstName('email1@example.com', 'John')->email, 'email1@example.com');
        $this->assertEquals(User::findByEmailAndFirstNameAndLastName('email1@example.com', 'John', 'Doe')->email, 'email1@example.com');

        $u = new User(['first_name' => 'John', 'email' => 'email10@example.com', 'role_id' => Role::first()->id]);
        $isSaved = $u->save();

        $this->assertNotEquals(false, $isSaved);

        $this->assertEquals('email1@example.com', User::findAllByFirstName('John')[0]->email);
        $this->assertEquals('email10@example.com', User::findAllByFirstName('John')[1]->email);
        $this->assertInstanceOf('User', User::findAllByFirstName('John')[0]);
        $this->assertEquals('email10@example.com', User::findAllByFirstNameAndEmail('John', 'email10@example.com')[0]->email);
    }
    /**
     * @expectedException Exception
     */
    function testDynamicallyFindersFailure()
    {
        User::findByDomain('example.com'); // throw exception
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
        $user = User::first();
        $this->assertEquals(
            [
                'id' => 'LONG',
                'email' => 'VAR_STRING',
                'first_name' => 'VAR_STRING',
                'last_name' => 'VAR_STRING',
                'role_id' => 'LONG'
            ],
            $user->schema()
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
        $u = new User([
            'role_id' => 1,
            'email' => 'email1235@example.com',
            'first_name' => 'John',
            'last_name' => 'Test',
        ]);
        $u->save();
        $this->assertCount(4, User::all());
        $users = User::where("first_name = ?", ['John']);
        $this->assertCount(2, $users);
        $users = User::where("first_name = ? OR last_name = ?", ['John', 'Bar3']);
        $this->assertCount(3, $users);
    }

}