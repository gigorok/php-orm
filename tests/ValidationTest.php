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
        $validator = new \ORM\Validator\Exclusion('role_id', 10, ['in' => [10, 11]], 'Role appeared in invalid list');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Role appeared in invalid list', $validator->getMessage());
    }

    function testFormat()
    {
        $validator = new \ORM\Validator\Format('email', 'someinvalidemail', ['with' => '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/i'], 'Email should be valid');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Email should be valid', $validator->getMessage());
    }

    function testInclusion()
    {
        $validator = new \ORM\Validator\Inclusion('role_id', 12, ['in' => range(10, 11)], 'Role does not appeared in valid list');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Role does not appeared in valid list', $validator->getMessage());
    }

    function testLength()
    {
        $validator = new \ORM\Validator\Length('title', 'qw', ['in' => [3, 5]], 'Title should have length between 3 and 5');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Title should have length between 3 and 5', $validator->getMessage());

        $validator = new \ORM\Validator\Length('title', 'qw', ['minimum' => 3]);
        $this->assertFalse($validator->validate());
        $this->assertEquals('is too short', $validator->getMessage());

        $validator = new \ORM\Validator\Length('title', 'qwerty', ['maximum' => 3]);
        $this->assertFalse($validator->validate());
        $this->assertEquals('is too long', $validator->getMessage());

        $validator = new \ORM\Validator\Length('title', 'qwerty', ['is' => 5]);
        $this->assertFalse($validator->validate());
        $this->assertEquals('is the wrong length', $validator->getMessage());

        // success tests
        $validator = new \ORM\Validator\Length('title', 'qwee', ['in' => [3, 5]]);
        $this->assertTrue($validator->validate());

        $validator = new \ORM\Validator\Length('title', 'qwe', ['minimum' => 3]);
        $this->assertTrue($validator->validate());

        $validator = new \ORM\Validator\Length('title', 'qwe', ['maximum' => 3]);
        $this->assertTrue($validator->validate());

        $validator = new \ORM\Validator\Length('title', 'qwerty', ['is' => 6]);
        $this->assertTrue($validator->validate());

    }

    function testNumericality()
    {
        $validator = new \ORM\Validator\Numericality('role_id', 'someinvalidstring', [], 'Role is not numeric');
        $this->assertFalse($validator->validate());
        $this->assertEquals('Role is not numeric', $validator->getMessage());
    }

    function testPresence()
    {
        $validator = new \ORM\Validator\Presence('title', null);
        $this->assertFalse($validator->validate());
        $this->assertEquals('can\'t be blank', $validator->getMessage());

        $validator = new \ORM\Validator\Presence('title', 'somestring', [], 'cannot be empty');
        $this->assertTrue($validator->validate());
        $this->assertEquals('cannot be empty', $validator->getMessage());
    }

    function testUniqueness()
    {
        $message = new Message(['title' => 'some title']);
        $message->validateWith($this->uniquenessMessageValidator($message));
        $message->save();
        $this->assertTrue($message->isValid());

        $message = new Message(['title' => 'some title']);
        $validator = $this->uniquenessMessageValidator($message);
        $message->validateWith($validator);
        $this->assertFalse($message->isValid());
        $this->assertEquals('is not unique', $validator->getMessage());

        $message = new Message(['title' => 'some title1']);
        $message->validateWith($this->uniquenessMessageValidator($message));
        $this->assertTrue($message->isValid());
    }

    private function uniquenessMessageValidator($message)
    {
        return new \ORM\Validator\Uniqueness('title', $message->title, ['class_name' => 'Message', 'object' => $message]);
    }

}