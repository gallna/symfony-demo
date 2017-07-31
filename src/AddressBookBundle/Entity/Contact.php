<?php

namespace AddressBookBundle\Entity;

class Contact extends \ArrayObject
{
    /**
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     */
    const NUM_ITEMS = 10;

    /**
     * @var string
     */
    private $slug;

    public function __construct($data = [], $slug = null)
    {
        if (!empty($slug)) {
            $this->setSlug($slug);
        }
        parent::__construct($data);
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getUsername()
    {
        return $this->offsetExists("username") ? $this->offsetGet("username") : '';
    }

    public function setUsername($name)
    {
        $this->offsetSet("username", $name);
    }

    public function getName()
    {
        return $this->offsetExists("name") ? $this->offsetGet("name") : '';
    }
    public function setName($name)
    {
        $this->offsetSet("name", $name);
    }

    public function getEmail()
    {
        return $this->offsetExists("email") ? $this->offsetGet("email") : '';
    }

    public function setEmail($email)
    {
        $this->offsetSet("email", $email);
    }

    public function getPhone()
    {
        return $this->offsetExists("phone") ? $this->offsetGet("phone") : '';
    }

    public function setPhone($phone)
    {
        $this->offsetSet("phone", $phone);
    }
}
