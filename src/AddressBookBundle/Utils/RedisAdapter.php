<?php

namespace AddressBookBundle\Utils;

use Pagerfanta\Adapter\AdapterInterface;
use AddressBookBundle\Entity\Contact;

class RedisAdapter implements AdapterInterface
{
    private $members;
    private $redis;

    /**
     * Constructor.
     *
     * @param Redis $redis Redis
     * @param array $members
     */
    public function __construct(\Redis $redis, array $members)
    {
        $this->redis = $redis;
        $this->members = $members;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return count($this->members);
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $members = array_slice($this->members, $offset, $length);
        foreach($members as $member) {
            $contact = $this->redis->hGetAll($member);
            yield new Contact($contact, base64_encode($member));
        }
    }
}
