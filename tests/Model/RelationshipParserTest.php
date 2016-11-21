<?php

namespace CarterZenk\Tests\JsonApi;

use CarterZenk\JsonApi\Exceptions\RelationshipExistenceException;
use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipParser;
use CarterZenk\JsonApi\Model\RelationshipParserInterface;
use CarterZenk\JsonApi\Transformer\Transformer;
use CarterZenk\Tests\JsonApi\Model\Comment;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\OrganizationUser;
use CarterZenk\Tests\JsonApi\Model\Thread;
use CarterZenk\Tests\JsonApi\Model\User;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;

class RelationshipParserTest extends BaseTestCase
{
    private function getParser(Model $model)
    {
        return new RelationshipParser($model, 'http://localhost');
    }

    private function getRelationships(Model $model)
    {
        $transformer = new Transformer();
        $parser = $this->getParser($model);
        return $parser->getRelationships($transformer);
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists(RelationshipParser::class));
    }

    public function testImplementsInterface()
    {
        $parser = $this->getParser(Contact::find(1));
        $this->assertInstanceOf(RelationshipParserInterface::class, $parser);
    }

    public function testGetRelationshipsReturnedArray()
    {
        $relationships = $this->getRelationships(User::find(1));
        $this->assertEquals(3, count($relationships));
        $this->assertArrayHasKey('owned-contacts', $relationships);
        $this->assertArrayHasKey('assigned-contacts', $relationships);
        $this->assertArrayHasKey('organizations', $relationships);
        $this->assertInstanceOf(\Closure::class, $relationships['owned-contacts']);
        $this->assertInstanceOf(\Closure::class, $relationships['assigned-contacts']);
        $this->assertInstanceOf(\Closure::class, $relationships['organizations']);
    }

    public function testRelationshipClosureReturnsRelationship()
    {
        $user = User::find(1);
        $relationships = $this->getRelationships($user);
        $relationship = $relationships['organizations']($user);

        $this->assertInstanceOf(ToManyRelationship::class, $relationship);

        $contact = Contact::find(1);
        $relationships = $this->getRelationships($contact);
        $relationship = $relationships['owner']($contact);

        $this->assertInstanceOf(ToOneRelationship::class, $relationship);
    }

    public function testMethodNotExistsExceptions()
    {
        $this->expectException(RelationshipExistenceException::class);
        $this->getRelationships(new Thread());
    }

    public function testMethodReturnsWrongTypeException()
    {
        $this->expectException(RelationshipExistenceException::class);
        $this->getRelationships(new Comment());
    }
}
