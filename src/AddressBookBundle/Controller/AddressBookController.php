<?php

namespace AddressBookBundle\Controller;

use AddressBookBundle\Entity\Contact;
use AddressBookBundle\Form\ContactType;
use AddressBookBundle\Repository\AddressBookRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used to manage address-book contents in the public part of the site.
 *
 * @Route("/address-book")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class AddressBookController extends Controller
{
    /**
     * Lists all Contact entities.
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_post_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     * @Route("/", defaults={"page": "1", "_format"="html"}, name="address_book_list")
     * @Route("/page/{page}", defaults={"_format"="html"}, requirements={"page": "[1-9]\d*"}, name="address_book_paginated")
     * @Method("GET")
     */
    public function indexAction($page, AddressBookRepository $repository)
    {
        $contacts = $repository->get($this->getUser()->getUsername(), $page);
        return $this->render('address-book/list.html.twig', ['posts' => $contacts]);
    }

    /**
     * @Route("/item/{slug}", name="address_book_item")
     * @Method("GET")
     */
    public function showAction($slug, AddressBookRepository $repository)
    {
        if (!($contact = $repository->find($this->getUser()->getUsername(), $slug))) {
            throw new NotFoundHttpException("Slug not found or does not belong to this user");
        }
        return $this->render('address-book/contact.html.twig', ['post' => $contact]);
    }

    /**
     * @Route("/search", name="address_book_search")
     * @Method("GET")
     *
     * @return Response|JsonResponse
     */
    public function searchAction(Request $request, AddressBookRepository $repository)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render('address-book/search.html.twig');
        }

        $query = $request->query->get('q', '');
        $contacts = $repository->findBySearchQuery($this->getUser()->getUsername(), $query);

        $results = [];
        foreach ($contacts as $contact) {
            $results[] = [
                'title' => htmlspecialchars($contact->getName()),
                'summary' => htmlspecialchars($contact->getEmail() . ", " . $contact->getPhone()),
                'url' => $this->generateUrl('address_book_item', ['slug' => $contact->getSlug()]),
            ];
        }

        return $this->json($results);
    }
}
