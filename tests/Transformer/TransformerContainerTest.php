<?php

namespace CarterZenk\Tests\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Paginator;
use CarterZenk\JsonApi\Transformer\Container;
use CarterZenk\JsonApi\Transformer\ResourceTransformer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\User;
use Illuminate\Database\Eloquent\Collection;

class TransformerContainerTest extends BaseTestCase
{
    public function testGetTransformerByModel()
    {
        $container = $this->getContainer();
        $contact = $this->getContact();
        $transformer = $container->getTransformer($contact);

        $this->checkTransformer($transformer, $contact, 'lead');
    }

    public function testGetTransformerByToOneRelationship()
    {
        $container = $this->getContainer();
        $owner = $this->getContact()->owner;
        $transformer = $container->getTransformer($owner);

        $this->checkTransformer($transformer, $owner, 'user');

    }

    public function testGetTransformerByToManyRelationship()
    {
        $container = $this->getContainer();
        $ownedContacts = $this->getUser()->ownedContacts;
        $transformer = $container->getTransformer($ownedContacts);

        $this->checkTransformer($transformer, $ownedContacts, 'lead');
    }

    public function testGetTransformerByPaginator()
    {
        $container = $this->getContainer();
        $ownedContacts = $this->getUser()->ownedContacts;
        $paginator = $this->getPaginator($ownedContacts);
        $transformer = $container->getTransformer($paginator);

        $this->checkTransformer($transformer, $paginator, 'lead');
    }

    private function getContainer()
    {
        return new Container('http://localhost');
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
