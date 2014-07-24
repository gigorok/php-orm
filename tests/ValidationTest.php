<?php

/**
 * php-orm
 *
 * @author Igor Gonchar <gigorok@gmail.com>
 * @copyright 2014 Igor Gonchar
 */

class ValidationTest extends \BaseTest
{
    function testExclusion()
    {
        $user = new User();
        $user->role_id = 10;

        $validator = new \ORM\Validator\Exclusion('role_id', ['in' => [10, 11]], 'Role appeared in invalid list');
        $this->assertFalse($validator->validate($user));
        $this->assertEquals('Role appeared in invalid list', $validator->getMessage());
    }

    function testFormat()
    {
        $message = new Message(['title' => 'someinvalidemail']);

        $validator = new \ORM\Validator\Format('title', ['with' => '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/i'], 'Email should be valid');
        $this->assertFalse($validator->validate($message));
        $this->assertEquals('Email should be valid', $validator->getMessage());
    }

    function testInclusion()
    {
        $user = new User();
        $user->role_id = 12;

        $validator = new \ORM\Validator\Inclusion('role_id', ['in' => range(10, 11)], 'Role does not appeared in valid list');
        $this->assertFalse($validator->validate($user));
        $this->assertEquals('Role does not appeared in valid list', $validator->getMessage());
    }

    function testLength()
    {
        $message = new Message(['title' => 'qw']);

        $validator = new \ORM\Validator\Length('title', ['in' => [3, 5]], 'Title should have length between 3 and 5');
        $this->assertFalse($validator->validate($message));
        $this->assertEquals('Title should have length between 3 and 5', $validator->getMessage());

        $validator = new \ORM\Validator\Length('title', ['minimum' => 3]);
        $this->assertFalse($validator->validate($message));
        $this->assertEquals('is too short', $validator->getMessage());

        $message->title = 'qwerty';

        $validator = new \ORM\Validator\Length('title', ['maximum' => 3]);
        $this->assertFalse($validator->validate($message));
        $this->assertEquals('is too long', $validator->getMessage());

        $validator = new \ORM\Validator\Length('title', ['is' => 5]);
        $this->assertFalse($validator->validate($message));
        $this->assertEquals('is the wrong length', $validator->getMessage());

        // success tests
        $message->title = 'qwee';
        $validator = new \ORM\Validator\Length('title', ['in' => [3, 5]]);
        $this->assertTrue($validator->validate($message));

        $message->title = 'qwe';
        $validator = new \ORM\Validator\Length('title', ['minimum' => 3]);
        $this->assertTrue($validator->validate($message));

        $validator = new \ORM\Validator\Length('title', ['maximum' => 3]);
        $this->assertTrue($validator->validate($message));

        $message->title = 'qwerty';
        $validator = new \ORM\Validator\Length('title', ['is' => 6]);
        $this->assertTrue($validator->validate($message));

    }

    function testNumericality()
    {
        $user = new User(['role_id' => 'someinvalidstring']);

        $validator = new \ORM\Validator\Numericality('role_id', [], 'Role is not numeric');
        $this->assertFalse($validator->validate($user));
        $this->assertEquals('Role is not numeric', $validator->getMessage());
    }

    function testPresence()
    {
        $message = new Message(['title' => null]);

        $validator = new \ORM\Validator\Presence('title');
        $this->assertFalse($validator->validate($message));
        $this->assertEquals('can\'t be blank', $validator->getMessage());

        $message->title = 'somestring';
        $validator = new \ORM\Validator\Presence('title', [], 'cannot be empty');
        $this->assertTrue($validator->validate($message));
        $this->assertEquals('cannot be empty', $validator->getMessage());
    }

    function testUniqueness()
    {
        $message = new Message(['title' => 'some title']);
        $message->validateWith(new \ORM\Validator\Uniqueness('title'));
        $this->assertTrue($message->isValid());
        $message->save();
        $this->assertTrue($message->isValid());

        $message = new Message(['title' => 'some title']);
        $validator = new \ORM\Validator\Uniqueness('title');
        $message->validateWith($validator);
        $this->assertFalse($message->isValid());
        $this->assertEquals('is not unique', $validator->getMessage());

        $message = new Message(['title' => 'some title1']);
        $message->validateWith(new \ORM\Validator\Uniqueness('title'));
        $this->assertTrue($message->isValid());

    }

    function testCustom()
    {
        $message = new Message(['title' => 'some title']);
        $message->validateWith(new \ORM\Validator\Custom('title', ['closure' => function($record) {
            return strlen($record->title) > 12;
            }]));
        $this->assertTrue($message->isInvalid());
    }

    function testValidates()
    {
        $message = new Message(['title' => '123456789012345678901']);
        $this->assertTrue($message->isInvalid());
    }

}