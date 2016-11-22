<?php

namespace CarterZenk\Tests\JsonApi;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipParser;
use CarterZenk\JsonApi\Model\RelationshipParserInterface;
use CarterZenk\JsonApi\Transformer\Transformer;
use CarterZenk\Tests\JsonApi\Model\Comment;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\Thread;
use CarterZenk\Tests\JsonApi\Model\User;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
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

    public function testToManyRelationship()
    {
        $user = User::find(1);
        $relationships = $this->getRelationships($user);
        $relationship = $relationships['owned-contacts']($user);

        $this->assertInstanceOf(ToManyRelationship::class, $relationship);

        $links = $relationship->getLinks();

        $this->assertEquals('http://localhost', $links->getBaseUri());

        $this->assertEquals(
            '/users/1/relationships/owned-contacts',
            $links->getSelf()->getHref()
        );

        $this->assertEquals(
            '/users/1/owned-contacts',
            $links->getRelated()->getHref()
        );
    }

    public function testToOneRelationship()
    {
        $contact = Contact::find(1);
        $relationships = $this->getRelationships($contact);
        $relationship = $relationships['owner']($contact);

        $this->assertInstanceOf(ToOneRelationship::class, $relationship);

        $links = $relationship->getLinks();

        $this->assertEquals('http://localhost', $links->getBaseUri());

        $this->assertEquals(
            '/leads/1/relationships/owner',
            $links->getSelf()->getHref()
        );

        $this->assertEquals(
            '/leads/1/owner',
            $links->getRelated()->getHref()
        );
    }

    public function testMethodNotExistsExceptions()
    {
        $this->expectException(RelationshipNotExists::class);
        $this->getRelationships(new Thread());
    }

    public function testMethodReturnsWrongTypeException()
    {
        $this->expectException(RelationshipNotExists::class);
        $this->getRelationships(new Comment());
    }
}
