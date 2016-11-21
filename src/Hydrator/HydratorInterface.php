<?php

namespace CarterZenk\JsonApi\Hydrator;

use WoohooLabs\Yin\JsonApi\Hydrator\HydratorInterface as YinHydratorInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\UpdateRelationshipHydratorInterface;

interface HydratorInterface extends YinHydratorInterface, UpdateRelationshipHydratorInterface
{
}
