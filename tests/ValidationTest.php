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
        $object = new stdClass();
        $object->role_id = 10;

        $validator = new \ORM\Validator\Exclusion($object, 'role_id', ['in' => [10, 11]], 'Role appeared in invalid list');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Role appeared in invalid list', $validator->getMessage());
    }

    function testFormat()
    {
        $object = new stdClass();
        $object->email = 'someinvalidemail';

        $validator = new \ORM\Validator\Format($object, 'email', ['with' => '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/i'], 'Email should be valid');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Email should be valid', $validator->getMessage());
    }

    function testInclusion()
    {
        $object = new stdClass();
        $object->role_id = 12;

        $validator = new \ORM\Validator\Inclusion($object, 'role_id', ['in' => range(10, 11)], 'Role does not appeared in valid list');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Role does not appeared in valid list', $validator->getMessage());
    }

    function testLength()
    {
        $object = new stdClass();
        $object->title = 'qw';

        $validator = new \ORM\Validator\Length($object, 'title', ['in' => [3, 5]], 'Title should have length between 3 and 5');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Title should have length between 3 and 5', $validator->getMessage());

        $validator = new \ORM\Validator\Length($object, 'title', ['minimum' => 3]);
        $this->assertFalse($validator->validate());
        $this->assertEquals('is too short', $validator->getMessage());

        $object->title = 'qwerty';

        $validator = new \ORM\Validator\Length($object, 'title', ['maximum' => 3]);
        $this->assertFalse($validator->validate());
        $this->assertEquals('is too long', $validator->getMessage());

        $validator = new \ORM\Validator\Length($object, 'title', ['is' => 5]);
        $this->assertFalse($validator->validate());
        $this->assertEquals('is the wrong length', $validator->getMessage());

        // success tests
        $object->title = 'qwee';
        $validator = new \ORM\Validator\Length($object, 'title', ['in' => [3, 5]]);
        $this->assertTrue($validator->validate());

        $object->title = 'qwe';
        $validator = new \ORM\Validator\Length($object, 'title', ['minimum' => 3]);
        $this->assertTrue($validator->validate());

        $validator = new \ORM\Validator\Length($object, 'title', ['maximum' => 3]);
        $this->assertTrue($validator->validate());

        $object->title = 'qwerty';
        $validator = new \ORM\Validator\Length($object, 'title', ['is' => 6]);
        $this->assertTrue($validator->validate());

    }

    function testNumericality()
    {
        $object = new stdClass();
        $object->role_id = 'someinvalidstring';

        $validator = new \ORM\Validator\Numericality($object, 'role_id', [], 'Role is not numeric');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Role is not numeric', $validator->getMessage());
    }

    function testPresence()
    {
        $object = new stdClass();
        $object->title = null;

        $validator = new \ORM\Validator\Presence($object, 'title');
        $this->assertFalse($validator->validate());
        $this->assertEquals('can\'t be blank', $validator->getMessage());

        $object->title = 'somestring';
        $validator = new \ORM\Validator\Presence($object, 'title', [], 'cannot be empty');
        $this->assertTrue($validator->validate());
        $this->assertEquals('cannot be empty', $validator->getMessage());
    }

    function testUniqueness()
    {
        $message = new Message(['title' => 'some title']);
        $message->validateWith(new \ORM\Validator\Uniqueness($message, 'title'));
        $this->assertTrue($message->isValid());
        $message->save();
        $this->assertTrue($message->isValid());

        $message = new Message(['title' => 'some title']);
        $validator = new \ORM\Validator\Uniqueness($message, 'title');
        $message->validateWith($validator);
        $this->assertFalse($message->isValid());
        $this->assertEquals('is not unique', $validator->getMessage());

        $message = new Message(['title' => 'some title1']);
        $message->validateWith(new \ORM\Validator\Uniqueness($message, 'title'));
        $this->assertTrue($message->isValid());

    }

    function testCustom()
    {
        $message = new Message(['title' => 'some title']);
        $message->validateWith(new \ORM\Validator\Custom($message, 'title', ['closure' => function($value) {
            return strlen($value) > 12;
            }]));
        $this->assertTrue($message->isInvalid());
    }

    function testValidates()
    {
        $message = new Message(['title' => '123456789012345678901']);
        $this->assertTrue($message->isInvalid());
    }

}