<?php

/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class DirtyTest extends \BaseTest
{
    function testIsChangedNewObj()
    {
        $user = new User(['email' => '123']);
        $this->assertFalse($user->isChanged());
        $user->email = 'some';
        $user->email = 'some1';
        $user->role_id = 123;
        $this->assertTrue($user->isChanged());
        $this->assertCount(2, $user->changed());
        $this->assertEquals(['email', 'role_id'], $user->changed());
        $this->assertEquals(['email' => ['some', 'some1'], 'role_id' => [123]], $user->changes());
        $this->assertEquals(['email' => 'some1', 'role_id' => 123], $user->attributes());
    }

    function testIsChangedOldObj()
    {
        $user = User::last();
        $this->assertFalse($user->isChanged());
        $user->email = 'some';
        $user->email = 'some1';
        $this->assertTrue($user->isChanged());
        $this->assertCount(1, $user->changed());
        $this->assertEquals('email', $user->changed()[0]);
        $this->assertEquals(['email' => ['some', 'some1']], $user->changes());
        $this->assertEquals('some1', $user->attributes()['email']);
    }

}