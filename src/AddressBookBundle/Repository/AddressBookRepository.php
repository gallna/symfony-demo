<?php

namespace AddressBookBundle\Repository;

use AddressBookBundle\Entity\Contact;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use AddressBookBundle\Utils\RedisAdapter;
use Redis;

class AddressBookRepository
{
    private $redis;

    /**
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param string $username
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function get($username, $page = 1)
    {
        $paginator = new Pagerfanta(
            new RedisAdapter($this->redis, $this->redis->sMembers($username))
        );
        $paginator->setMaxPerPage(Contact::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @param string $slug
     *
     * @return Contact
     */
    public function find($username, $slug)
    {
        $key = base64_decode($slug);
        if ($this->redis->sContains($username, $key)) {
            $contact = $this->redis->hGetAll($key);
            return new Contact($contact, $slug);
        }
    }

    /**
     * @param string $username
     *
     * @return Generator
     */
    public function getAll($username)
    {
        $members = $this->redis->sMembers($username);
        foreach($members as $member) {
            $contact = $this->redis->hGetAll($member);
            yield new Contact($contact, base64_encode($member));
        }
    }

    /**
     * @param Contact $contact
     *
     * @return void
     */
    public function add(Contact $contact)
    {
        $multi = $this->redis->multi();
        $multi->sAdd(
            $contact->getUsername(),
            $key = $this->contactKey($contact)
        );
        $contact->setSlug(base64_encode($key));
        foreach ($contact as $hashKey => $hashValue) {
            $multi->hSet($key, $hashKey, $hashValue);
        }
        $multi->exec();
    }

    /**
     * @param string $slug
     * @param Contact $contact
     *
     * @return void
     */
    public function update($slug, Contact $contact)
    {
        $this->redis->sRem($contact->getUsername(), base64_decode($slug));
        $this->add($contact);
    }

    /**
     * @param Contact $contact
     *
     * @return void
     */
    public function remove(Contact $contact)
    {
        $this->redis->hDel($key = $this->contactKey($contact));
        $this->redis->sRem($username, $key);
        $contact->setSlug(null);
    }

    /**
     * @param string $username
     * @param string $query The search query as input by the user
     * @param int    $limit    The maximum number of results returned
     *
     * @return Generator
     */
    public function findBySearchQuery($username, $query, $limit = Contact::NUM_ITEMS)
    {
        if (!($search = $this->sanitizeSearchQuery($query))) {
            return [];
        }
        $it = null;
        $this->redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
        while($members = $this->redis->sScan($username, $it, "*$search*")) {
            foreach($members as $key => $member) {
                $contact = $this->redis->hGetAll($member);
                yield new Contact($contact, base64_encode($member));
            }
        }
    }

    private function contactKey(Contact $contact)
    {
        return join(':', array_intersect_key((array)$contact, array_flip(["username", "name", "email", "phone"])));
    }

    private function sanitizeSearchQuery($query)
    {
        return preg_replace('/[^[:alnum:] ]/', '', trim(preg_replace('/[[:space:]]+/', ' ', $query)));
    }
}
