<?php

namespace CarterZenk\Tests\JsonApi\Transformer;

use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\JsonApi\Model\Paginator;
use CarterZenk\JsonApi\Transformer\Container;
use CarterZenk\JsonApi\Transformer\LinksFactory;
use CarterZenk\JsonApi\Transformer\ResourceTransformer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\User;
use Illuminate\Database\Eloquent\Collection;
use Slim\Http\Uri;

class TransformerContainerTest extends BaseTestCase
{
    public function testGetTransformerByModel()
    {
        $container = $this->getContainer();
        $contact = $this->getContact();
        $transformer = $container->get($contact);

        $this->checkTransformer($transformer, $contact, 'contact');
    }

    public function testGetTransformerByToOneRelationship()
    {
        $container = $this->getContainer();
        $owner = $this->getContact()->owner;
        $transformer = $container->get($owner);

        $this->checkTransformer($transformer, $owner, 'user');

    }

    public function testGetTransformerByToManyRelationship()
    {
        $container = $this->getContainer();
        $ownedContacts = $this->getUser()->ownedContacts;
        $transformer = $container->get($ownedContacts);

        $this->checkTransformer($transformer, $ownedContacts, 'contact');
    }

    public function testGetTransformerByPaginator()
    {
        $container = $this->getContainer();
        $ownedContacts = $this->getUser()->ownedContacts;
        $paginator = $this->getPaginator($ownedContacts);
        $transformer = $container->get($paginator);

        $this->checkTransformer($transformer, $paginator, 'contact');
    }

    public function testGetTransformerWithInvalidObjectThrowsException()
    {
        $container = $this->getContainer();
        $this->expectException(InvalidDomainObjectException::class);
        $container->get([]);
    }

    public function testGetAttributesWithArrayReturnsArray()
    {
        $container = $this->getContainer();
        $contact = $this->getContact();
        $transformer = $container->get($contact);

        $this->assertEquals([], $transformer->getAttributes([]));
    }

    private function getContainer()
    {
        $uri = new Uri('http', 'localhost', 8000);
        $linksFactory = new LinksFactory($uri);
        return new Container($linksFactory);
    }

    private function getPaginator(Collection $collection)
    {
        $pageNumber = 1;
        $pageSize = 20;

        return new Paginator(
            $collection->forPage($pageNumber, $pageSize),
            $collection->count(),
            $pageNumber,
            $pageSize
        );
    }

    private function getContact()
    {
        return Contact::find(1);
    }

    private function getUser()
    {
        return User::find(1);
    }

    private function checkTransformer($transformer, $domainObject, $type)
    {
        $this->assertInstanceOf(ResourceTransformer::class, $transformer);
        $this->assertEquals($type, $transformer->getType($domainObject));
    }
}
